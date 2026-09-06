<?php

namespace App\Models;

use App\Models\Concerns\GeneratesIntegerIds;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrDailyPlanActivity extends Model
{
    use GeneratesIntegerIds;

    protected $table = 'trDailyPlanActivity';
    protected $primaryKey = 'intDailyPlanActivity_ID';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'intDailyPlanActivity_ID',
        'intWeeklyPlan_ID',
        'intUser_ID',
        'intProject_ID',
        'intSubProject_ID',
        'intProjectStage_ID',
        'txtDayName',
        'dtmActivityDate',
        'txtActivityName',
        'txtStartTime',
        'txtEndTime',
        'floatDuration',
        'txtLocationType',
        'txtRemarks',
        'bitIsCompleted',
        'txtInsertedBy',
        'dtmInserted',
    ];

    protected static function booted(): void
    {
        static::creating(function (TrDailyPlanActivity $act) {
            if (empty($act->intUser_ID)) {
                if (! empty($act->intWeeklyPlan_ID)) {
                    $plan = MWeeklyPlan::find($act->intWeeklyPlan_ID);
                    $act->intUser_ID = $plan?->intUser_ID ?: (session('auth_user_id') ?: 1);
                } else {
                    $act->intUser_ID = session('auth_user_id') ?: 1;
                }
            }
            if (empty($act->dtmInserted)) {
                $act->dtmInserted = now();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'intDailyPlanActivity_ID' => 'integer',
            'intWeeklyPlan_ID' => 'integer',
            'intUser_ID' => 'integer',
            'intProject_ID' => 'integer',
            'intSubProject_ID' => 'integer',
            'intProjectStage_ID' => 'integer',
            'dtmActivityDate' => 'date',
            'floatDuration' => 'float',
            'bitIsCompleted' => 'boolean',
            'dtmInserted' => 'datetime',
        ];
    }

    public function weeklyPlan(): BelongsTo
    {
        return $this->belongsTo(MWeeklyPlan::class, 'intWeeklyPlan_ID', 'intWeeklyPlan_ID');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(MUser::class, 'intUser_ID', 'intUser_ID');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(MProject::class, 'intProject_ID', 'intProject_ID');
    }

    public function subProject(): BelongsTo
    {
        return $this->belongsTo(TrSubProject::class, 'intSubProject_ID', 'intSubProject_ID');
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(TrProjectStage::class, 'intProjectStage_ID', 'intProjectStage_ID');
    }
}
