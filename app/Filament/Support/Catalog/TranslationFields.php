<?php

namespace App\Filament\Support\Catalog;

use App\Actions\Catalog\SaveTranslations;
use App\Models\Catalog;
use App\Models\ExpansionEdition;
use App\Models\ExpansionRelease;
use App\Models\Language;
use App\Support\Catalog\CatalogRules;
use App\Support\Localization\ContentLanguageUsage;
use App\Support\Localization\TranslationPatch;
use Closure;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

final class TranslationFields
{
    /**
     * @param  class-string<Model>  $modelClass
     * @return list<Component>
     */
    public static function schema(string $modelClass): array
    {
        $attributes = TranslationPatch::attributes(new $modelClass);
        $fields = [
            Select::make('locale')->label(__('admin.translation.language'))->live()->searchable()
                ->options(fn (): array => Language::query()->get()->mapWithKeys(fn (Language $language): array => [
                    CatalogRules::languageCode($language->code) => CatalogLabels::language($language),
                ])->sort()->all())
                ->afterStateUpdated(function (?string $state, ?string $old, ?Model $record, Get $get, Set $set) use ($attributes): void {
                    if ($old !== null && ($get('remove') || self::changes($attributes, $get->array('')) !== [])) {
                        $set('locale', $old);
                        $set('pending_switch', ['locale' => $state]);
                        $set('switch_decision', null);

                        return;
                    }

                    $set('', self::state($record, $state, $attributes));
                }),
            Hidden::make('pending_switch')->dehydrated(false),
            ToggleButtons::make('switch_decision')->label(fn (Get $get): string => __('admin.translation.switch_question', [
                'language' => $get('pending_switch.locale') ?? __('admin.translation.no_language'),
            ]))
                ->options(['keep' => __('admin.translation.keep'), 'discard' => __('admin.translation.discard')])
                ->inline()->live()->dehydrated(false)->visible(fn (Get $get): bool => filled($get('pending_switch')))
                ->afterStateUpdated(function (?string $state, ?Model $record, Get $get, Set $set) use ($attributes): void {
                    if ($state === 'discard' && is_array($pending = $get('pending_switch'))) {
                        $set('', self::state($record, $pending['locale'], $attributes));
                    } else {
                        $set('pending_switch', null);
                        $set('switch_decision', null);
                    }
                }),
            Hidden::make('original'),
            Hidden::make('edited'),
        ];
        foreach ($attributes as $attribute) {
            $input = $attribute === 'description' ? Textarea::make($attribute)->rows(4) : TextInput::make($attribute)->maxLength(255);
            $fields[] = $input->label(__('admin.translation.'.$attribute))
                ->visible(fn (Get $get): bool => filled($get('locale')) && ! $get('remove'))
                ->disabled(fn (Get $get): bool => (bool) $get('suppress_'.$attribute))
                ->afterStateUpdated(fn (Set $set) => $set('edited.'.$attribute, true));
            if ($attribute !== 'display_name') {
                $fields[] = Toggle::make('suppress_'.$attribute)->label(__('admin.translation.suppress_'.$attribute))->live()
                    ->visible(fn (Get $get): bool => filled($get('locale')) && ! $get('remove')
                        && ! array_key_exists($attribute, $get('original') ?? []));
            }
        }
        $fields[] = Toggle::make('remove')->label(__('admin.translation.remove'))->live()
            ->visible(fn (Get $get): bool => filled($get('locale')) && filled($get('original')));

        return [Section::make(__('admin.translation.heading'))->description(__('admin.translation.help'))
            ->statePath('translation')->schema($fields)->columnSpanFull()];
    }

    /** @param list<string> $attributes
     * @return array<string, ?string>
     */
    private static function original(?Model $record, ?string $locale, array $attributes): array
    {
        $original = [];
        if ($locale !== null && $record !== null && method_exists($record, 'getTranslations')) {
            foreach ($attributes as $attribute) {
                $translations = $record->getTranslations($attribute);
                if (array_key_exists($locale, $translations)) {
                    $original[$attribute] = $translations[$locale];
                }
            }
        }

        return $original;
    }

    /** @template T of Model
     * @param  T  $record
     * @param  array<string, mixed>  $data
     * @param  Closure(T, array<string, mixed>): T  $save
     * @return T
     */
    public static function save(Model $record, array $data, Closure $save): Model
    {
        $input = $data['translation'] ?? [];
        $attributes = TranslationPatch::attributes($record);
        unset($data['translation']);
        $data = array_diff_key($data, array_flip($attributes));
        $locale = $input['locale'] ?? null;
        $remove = (bool) ($input['remove'] ?? false);
        $values = self::changes($attributes, $input);
        if ($locale === null || ($values === [] && ! $remove)) {
            return $save($record, $data);
        }

        return $record->getConnection()->transaction(function () use ($record, $data, $save, $locale, $values, $remove): Model {
            // Lock every language needed by the two writes in their shared order before either entity.
            $languageId = match (true) {
                $record instanceof Catalog => $data['default_language_id'] ?? $record->default_language_id,
                $record instanceof ExpansionRelease => ExpansionEdition::query()->whereKey($data['edition_id'] ?? $record->edition_id)->value('language_id'),
                default => null,
            };
            $codes = [$locale];
            if ($languageId !== null && ($code = Language::query()->whereKey($languageId)->value('code')) !== null) {
                $codes[] = $code;
            }
            ContentLanguageUsage::lock($codes, $record->getConnection());
            $saved = $save($record, $data);
            try {
                return app(SaveTranslations::class)->handle($saved, $locale, $values, $remove);
            } catch (ValidationException $exception) {
                $messages = [];
                foreach ($exception->errors() as $field => $errors) {
                    $messages['translation.'.$field] = $errors;
                }

                throw ValidationException::withMessages($messages);
            }
        });
    }

    /**
     * @param  list<string>  $attributes
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    private static function changes(array $attributes, array $input): array
    {
        $values = [];
        $before = $input['original'] ?? [];
        foreach ($attributes as $attribute) {
            if ($attribute !== 'display_name' && ($input['suppress_'.$attribute] ?? false)) {
                $values[$attribute] = null;

                continue;
            }
            if (! array_key_exists($attribute, $input)) {
                continue;
            }
            $value = $input[$attribute];
            $trimmed = is_string($value) ? trim($value) : $value;
            $normalized = $trimmed === '' ? null : $trimmed;
            $original = $before[$attribute] ?? null;
            if ($original === $value || $original === $normalized) {
                continue;
            }
            // Preserve historical padding or empty strings through Filament's form normalization.
            if (! ($input['edited'][$attribute] ?? false) && is_string($original) && trim($original) === ($normalized ?? '')) {
                continue;
            }
            $values[$attribute] = $value;
        }

        return $values;
    }

    /** @return array<string, mixed> */
    public static function savedState(Model $record, string $locale): array
    {
        return self::state($record, $locale, TranslationPatch::attributes($record));
    }

    /**
     * @param  list<string>  $attributes
     * @return array<string, mixed>
     */
    private static function state(?Model $record, ?string $locale, array $attributes): array
    {
        $original = self::original($record, $locale, $attributes);
        $state = ['locale' => $locale, 'original' => $original, 'edited' => [], 'remove' => false, 'pending_switch' => null, 'switch_decision' => null];
        foreach ($attributes as $attribute) {
            $state[$attribute] = $original[$attribute] ?? null;
            $state['suppress_'.$attribute] = false;
        }

        return $state;
    }
}
