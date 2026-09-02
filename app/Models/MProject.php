<?php

namespace App\Models;

use App\Models\Concerns\GeneratesIntegerIds;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MProject extends Model
{
    use GeneratesIntegerIds;

    protected $table = 'mProject';

    protected $primaryKey = 'intProject_ID';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'intProject_ID',
        'intDepartment_ID',
        'intSubDepartment_ID',
        'intProjectType_ID',
        'intUser_ID',
        'txtProjectCode',
        'txtProjectName',
        'txtKpiLevel',
        'txtDeliverable',
        'txtTargetSkalaGrade',
        'intScore',
        'txtAchievement',
        'floatWeight',
        'bitHasSubProject',
        'txtDescription',
        'dtmProjectStartDate',
        'dtmProjectEndDate',
        'floatPlan',
        'floatActual',
        'txtStatus',
        'txtInsertedBy',
        'dtmInserted',
        'txtUpdatedBy',
        'dtmUpdated',
        'bitActive',
    ];

    protected $casts = [
        'floatWeight' => 'float',
        'floatPlan' => 'float',
        'floatActual' => 'float',
        'intScore' => 'integer',
        'bitHasSubProject' => 'boolean',
        'bitActive' => 'boolean',
        'dtmProjectStartDate' => 'datetime',
        'dtmProjectEndDate' => 'datetime',
        'dtmInserted' => 'datetime',
        'dtmUpdated' => 'datetime',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(MDepartment::class, 'intDepartment_ID', 'intDepartment_ID');
    }

    public function subDepartment(): BelongsTo
    {
        return $this->belongsTo(MSubDepartment::class, 'intSubDepartment_ID', 'intSubDepartment_ID');
    }

    public function projectType(): BelongsTo
    {
        return $this->belongsTo(MProjectType::class, 'intProjectType_ID', 'intProjectType_ID');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(MUser::class, 'intUser_ID', 'intUser_ID');
    }

    public function subProjects(): HasMany
    {
        return $this->hasMany(TrSubProject::class, 'intProject_ID', 'intProject_ID');
    }

    public function stages(): HasMany
    {
        return $this->hasMany(TrProjectStage::class, 'intProject_ID', 'intProject_ID')
            ->orderBy('intProjectStageNumber');
    }

    public function directStages(): HasMany
    {
        return $this->hasMany(TrProjectStage::class, 'intProject_ID', 'intProject_ID')
            ->whereNull('intSubProject_ID')
            ->orderBy('intProjectStageNumber');
    }

    public function dailyTasks(): HasMany
    {
        return $this->hasMany(TrDailyTask::class, 'intProject_ID', 'intProject_ID');
    }

    public function dailyPlanActivities(): HasMany
    {
        return $this->hasMany(TrDailyPlanActivity::class, 'intProject_ID', 'intProject_ID');
    }

    /**
     * Recalculate and update the overall progress and exposure for this project.
     */
    public function recalculateProgress(): void
    {
        if ($this->bitHasSubProject) {
            $subProjects = $this->subProjects()->with('stages')->get();
            if ($subProjects->isNotEmpty()) {
                $totalWeight = $subProjects->sum('floatWeight');
                $weightedActual = 0;
                $weightedScore = 0;
                $scoreCount = 0;

                foreach ($subProjects as $sub) {
                    $w = $totalWeight > 0 ? ($sub->floatWeight / $totalWeight) : (1 / $subProjects->count());
                    $weightedActual += ($sub->floatProgress * $w);
                    if ($sub->intScore) {
                        $weightedScore += ($sub->intScore * $w);
                        $scoreCount++;
                    }
                }

                $this->floatActual = round($weightedActual, 2);
                if ($scoreCount > 0) {
                    $this->intScore = (int) round($weightedScore);
                }
                $this->save();
            }
        } else {
            $stages = $this->directStages()->get();
            if ($stages->isNotEmpty()) {
                $planSum = $stages->sum('floatProjectStagePlan');
                $actualSum = $stages->sum('floatProjectStageActual');
                $this->floatActual = $planSum > 0 ? round(($actualSum / $planSum) * 100, 2) : 0;
                $this->save();
            }
        }
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('bitActive', true);
    }
}
