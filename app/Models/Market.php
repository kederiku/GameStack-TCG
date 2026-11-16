<?php

namespace App\Models;

use App\Support\Localization\HasContentTranslations;
use Illuminate\Database\Eloquent\Attributes\Fillable;

/**
 * @property string $key
 * @property string $name
 */
#[Fillable(['key', 'name', 'display_name'])]
class Market extends CatalogRecord
{
    use HasContentTranslations;

    /** @var list<string> */
    public array $translatable = ['display_name'];
}
