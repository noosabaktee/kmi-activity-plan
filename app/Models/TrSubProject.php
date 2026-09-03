<?php

namespace App\Models;

use App\Models\Concerns\GeneratesIntegerIds;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrSubProject extends Model
{
    use GeneratesIntegerIds;

    protected $table = 'trSubProject';

    protected $primaryKey = 'intSubProject_ID';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'intSubProject_ID',
        'intProject_ID',
        'txtSubProjectName',
        'txtDeliverable',
        'txtTargetSkalaGrade',
        'intScore',
        'txtAchievement',
        'floatWeight',
        'floatProgress',
        'dtmStartDate',
        'dtmEndDate',
        'txtStatus',
        'txtInsertedBy',
        'dtmInserted',
    ];

    protected $casts = [
        'floatWeight' => 'float',
        'floatProgress' => 'float',
        'intScore' => 'integer',
        'dtmStartDate' => 'datetime',
        'dtmEndDate' => 'datetime',
        'dtmInserted' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(MProject::class, 'intProject_ID', 'intProject_ID');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(TrProjectAssignment::class, 'intSubProject_ID', 'intSubProject_ID');
    }

    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(MUser::class, 'trProjectAssignment', 'intSubProject_ID', 'intUser_ID');
    }

    public function stages(): HasMany
    {
        return $this->hasMany(TrProjectStage::class, 'intSubProject_ID', 'intSubProject_ID')
            ->orderBy('intProjectStageNumber');
    }

    public function dailyTasks(): HasMany
    {
        return $this->hasMany(TrDailyTask::class, 'intSubProject_ID', 'intSubProject_ID');
    }

    public function dailyPlanActivities(): HasMany
    {
        return $this->hasMany(TrDailyPlanActivity::class, 'intSubProject_ID', 'intSubProject_ID');
    }

    /**
     * Recalculate and update the sub project's progress based on its stages.
     */
    public function recalculateProgress(): void
    {
        $stages = $this->stages()->get();
        if ($stages->isNotEmpty()) {
            $planSum = $stages->sum('floatProjectStagePlan');
            $actualSum = $stages->sum('floatProjectStageActual');
            $this->floatProgress = $planSum > 0 ? round(($actualSum / $planSum) * 100, 2) : 0;
            $this->save();
        }
    }
}
