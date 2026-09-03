<?php

namespace App\Models;

use App\Models\Concerns\GeneratesIntegerIds;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MSkillset extends Model
{
    use GeneratesIntegerIds;

    protected $table = 'mSkillset';
    protected $primaryKey = 'intSkillset_ID';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'intSkillset_ID',
        'txtSkillsetName',
        'txtDescription',
        'txtBadgeColor',
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
            'intSkillset_ID' => 'integer',
            'bitActive' => 'boolean',
            'dtmInserted' => 'datetime',
            'dtmUpdated' => 'datetime',
        ];
    }

    public function projects(): HasMany
    {
        return $this->hasMany(MProject::class, 'intSkillset_ID', 'intSkillset_ID');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('bitActive', true);
    }
}
