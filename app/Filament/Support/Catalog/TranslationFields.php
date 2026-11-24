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
                ->afterStateUpdated(function (?string $state, ?Model $record, Set $set) use ($attributes): void {
                    $original = self::original($record, $state, $attributes);
                    $set('original', $original);
                    $set('edited', []);
                    $set('remove', false);
                    foreach ($attributes as $attribute) {
                        $set($attribute, $original[$attribute] ?? null);
                        $set('suppress_'.$attribute, false);
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
        $values = [];
        $remove = (bool) ($input['remove'] ?? false);
        $before = $input['original'] ?? [];
        foreach ($attributes as $attribute) {
            if ($attribute !== 'display_name' && ($input['suppress_'.$attribute] ?? false)) {
                $values[$attribute] = null;

                continue;
            }
            if (! ($input['edited'][$attribute] ?? false) || ! array_key_exists($attribute, $input)) {
                continue;
            }
            $value = $input[$attribute];
            $trimmed = is_string($value) ? trim($value) : $value;
            $normalized = $trimmed === '' ? null : $trimmed;
            if (array_key_exists($attribute, $before) && ($before[$attribute] === $value || $before[$attribute] === $normalized)) {
                continue;
            }
            $values[$attribute] = $value;
        }
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

    /** @return array<string, mixed> */
    public static function savedState(Model $record, string $locale): array
    {
        $attributes = TranslationPatch::attributes($record);
        $original = self::original($record, $locale, $attributes);
        $state = ['locale' => $locale, 'original' => $original, 'edited' => [], 'remove' => false];
        foreach ($attributes as $attribute) {
            $state[$attribute] = $original[$attribute] ?? null;
            $state['suppress_'.$attribute] = false;
        }

        return $state;
    }
}
