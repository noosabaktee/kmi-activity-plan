<?php

namespace App\Models;

use App\Models\Concerns\GeneratesIntegerIds;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MDepartment extends Model
{
    use GeneratesIntegerIds;

    protected $table = 'mDepartment';
    protected $primaryKey = 'intDepartment_ID';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'intDepartment_ID',
        'txtDepartmentCode',
        'txtDepartmentName',
        'txtDescription',
        'bitActive',
        'txtInsertedBy',
        'dtmInserted',
        'txtUpdatedBy',
        'dtmUpdated',
    ];

    protected function casts(): array
    {
        return [
            'intDepartment_ID' => 'integer',
            'bitActive' => 'boolean',
            'dtmInserted' => 'datetime',
            'dtmUpdated' => 'datetime',
        ];
    }

    public function subDepartments(): HasMany
    {
        return $this->hasMany(MSubDepartment::class, 'intDepartment_ID', 'intDepartment_ID');
    }

    public function users(): HasMany
    {
        return $this->hasMany(MUser::class, 'intDepartment_ID', 'intDepartment_ID');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(MProject::class, 'intDepartment_ID', 'intDepartment_ID');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('bitActive', true);
    }
}
