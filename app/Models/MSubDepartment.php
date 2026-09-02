<?php

namespace App\Models;

use App\Models\Concerns\GeneratesIntegerIds;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MSubDepartment extends Model
{
    use GeneratesIntegerIds;

    protected $table = 'mSubDepartment';
    protected $primaryKey = 'intSubDepartment_ID';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'intSubDepartment_ID',
        'intDepartment_ID',
        'txtSubDepartmentCode',
        'txtSubDepartmentName',
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
            'intSubDepartment_ID' => 'integer',
            'intDepartment_ID' => 'integer',
            'bitActive' => 'boolean',
            'dtmInserted' => 'datetime',
            'dtmUpdated' => 'datetime',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(MDepartment::class, 'intDepartment_ID', 'intDepartment_ID');
    }

    public function users(): HasMany
    {
        return $this->hasMany(MUser::class, 'intSubDepartment_ID', 'intSubDepartment_ID');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(MProject::class, 'intSubDepartment_ID', 'intSubDepartment_ID');
    }

    public function supervisors(): BelongsToMany
    {
        return $this->belongsToMany(
            MUser::class,
            'trSupervisorSubDept',
            'intSubDepartment_ID',
            'intUser_ID'
        );
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('bitActive', true);
    }
}
