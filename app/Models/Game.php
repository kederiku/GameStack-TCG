<?php

namespace App\Models;

use App\Observers\GameObserver;
use Carbon\CarbonInterface;
use Database\Factories\GameFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $image
 * @property int|null $start_year
 * @property int|null $end_year
 * @property string|null $publisher
 * @property string|null $description
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 */
#[Fillable(['name', 'slug', 'start_year', 'end_year', 'publisher', 'description'])]
#[ObservedBy([GameObserver::class])]
class Game extends Model
{
    /** @use HasFactory<GameFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'start_year' => 'integer',
            'end_year' => 'integer',
        ];
    }

    public function delete(): ?bool
    {
        if (! $this->exists) {
            return parent::delete();
        }

        return $this->getConnection()->transaction(function (): ?bool {
            $current = $this->newQuery()->lockForUpdate()->find($this->id);

            if ($current === null) {
                return null;
            }

            // A concurrent save may have replaced the image since this instance loaded.
            $this->setRawAttributes($current->getAttributes(), sync: true);

            return parent::delete();
        });
    }

    protected static function booted(): void
    {
        static::saving(function (Game $game): void {
            $game->name = trim($game->name);

            if (blank($game->slug)) {
                $game->slug = Str::slug($game->name);
            }
        });
    }
}
