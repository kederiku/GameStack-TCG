<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;

/**
 * @property string $key
 * @property string $name
 */
#[Fillable(['key', 'name'])]
class Market extends CatalogRecord {}
