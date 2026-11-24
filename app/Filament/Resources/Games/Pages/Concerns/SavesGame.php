<?php

namespace App\Filament\Resources\Games\Pages\Concerns;

use App\Actions\Games\SaveGame;
use App\Filament\Support\Catalog\TranslationFields;
use App\Models\Game;
use Illuminate\Validation\ValidationException;

trait SavesGame
{
    /** @param array<string, mixed> $data */
    protected function saveGame(Game $game, array $data): Game
    {
        try {
            return TranslationFields::save($game, $data, app(SaveGame::class)->handle(...));
        } catch (ValidationException $exception) {
            $messages = [];
            foreach ($exception->errors() as $field => $errors) {
                $messages['data.'.$field] = $errors;
            }

            $this->dispatch('form-validation-error', livewireId: $this->getId());

            throw ValidationException::withMessages($messages);
        }
    }
}
