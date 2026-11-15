<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $manifest_key
 * @property string $record_type
 * @property int $record_id
 */
#[Fillable(['manifest_key', 'record_type', 'record_id'])]
class CatalogExampleBinding extends Model
{
    protected $primaryKey = 'manifest_key';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['record_id' => 'integer'];
    }
}
