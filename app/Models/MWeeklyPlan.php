<?php

namespace App\Models;

use App\Models\Concerns\GeneratesIntegerIds;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MWeeklyPlan extends Model
{
    use GeneratesIntegerIds;

    protected $table = 'mWeeklyPlan';
    protected $primaryKey = 'intWeeklyPlan_ID';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'intWeeklyPlan_ID',
        'intUser_ID',
        'intDepartment_ID',
        'txtWeekTitle',
        'dtmWeekStartDate',
        'dtmWeekEndDate',
        'txtTargetGoals',
        'txtStatus',
        'bitActive',
        'txtInsertedBy',
        'dtmInserted',
        'txtUpdatedBy',
        'dtmUpdated',
    ];

    protected static function booted(): void
    {
        static::creating(function (MWeeklyPlan $plan) {
            if (empty($plan->intDepartment_ID) && ! empty($plan->intUser_ID)) {
                $user = MUser::find($plan->intUser_ID);
                $plan->intDepartment_ID = $user?->intDepartment_ID ?: 1;
            }
        });
    }

    protected function casts(): array
    {
        return [
            'intWeeklyPlan_ID' => 'integer',
            'intUser_ID' => 'integer',
            'intDepartment_ID' => 'integer',
            'dtmWeekStartDate' => 'date',
            'dtmWeekEndDate' => 'date',
            'bitActive' => 'boolean',
            'dtmInserted' => 'datetime',
            'dtmUpdated' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(MUser::class, 'intUser_ID', 'intUser_ID');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(MDepartment::class, 'intDepartment_ID', 'intDepartment_ID');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(TrDailyPlanActivity::class, 'intWeeklyPlan_ID', 'intWeeklyPlan_ID');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('bitActive', true);
    }
}
