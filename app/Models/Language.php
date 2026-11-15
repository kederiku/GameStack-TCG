<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;

/**
 * @property string $code
 * @property string $name
 */
#[Fillable(['code', 'name'])]
class Language extends CatalogRecord {}
