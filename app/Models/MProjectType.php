<?php

namespace App\Models;

use App\Models\Concerns\GeneratesIntegerIds;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MProjectType extends Model
{
    use GeneratesIntegerIds;

    protected $table = 'mProjectType';
    protected $primaryKey = 'intProjectType_ID';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'intProjectType_ID',
        'txtProjectTypeCode',
        'txtProjectTypeName',
        'txtDescription',
        'floatDefaultWeight',
        'txtColor',
        'txtIcon',
        'bitActive',
        'txtInsertedBy',
        'dtmInserted',
        'txtUpdatedBy',
        'dtmUpdated',
    ];

    protected function casts(): array
    {
        return [
            'intProjectType_ID' => 'integer',
            'floatDefaultWeight' => 'float',
            'bitActive' => 'boolean',
            'dtmInserted' => 'datetime',
            'dtmUpdated' => 'datetime',
        ];
    }

    public function projects(): HasMany
    {
        return $this->hasMany(MProject::class, 'intProjectType_ID', 'intProjectType_ID');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('bitActive', true);
    }
}
