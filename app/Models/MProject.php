<?php

namespace App\Models;

use App\Models\Concerns\GeneratesIntegerIds;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

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
        'intSkillset_ID',
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
        'bitIsAdHoc',
        'txtAdHocCategory',
        'txtPriority',
        'txtSpecialGoal',
        'txtApprovalStatus',
        'intSupervisor_ID',
        'intApprovedBy_ID',
        'dtmApprovedAt',
        'txtApprovalNotes',
    ];

    protected $casts = [
        'floatWeight' => 'float',
        'floatPlan' => 'float',
        'floatActual' => 'float',
        'intScore' => 'integer',
        'intSkillset_ID' => 'integer',
        'intSupervisor_ID' => 'integer',
        'intApprovedBy_ID' => 'integer',
        'bitHasSubProject' => 'boolean',
        'bitActive' => 'boolean',
        'bitIsAdHoc' => 'boolean',
        'dtmProjectStartDate' => 'datetime',
        'dtmProjectEndDate' => 'datetime',
        'dtmApprovedAt' => 'datetime',
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

    public function skillset(): BelongsTo
    {
        return $this->belongsTo(MSkillset::class, 'intSkillset_ID', 'intSkillset_ID');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(MUser::class, 'intUser_ID', 'intUser_ID');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(MUser::class, 'intSupervisor_ID', 'intUser_ID');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(MUser::class, 'intApprovedBy_ID', 'intUser_ID');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(TrProjectAssignment::class, 'intProject_ID', 'intProject_ID');
    }

    public function directAssignments(): HasMany
    {
        return $this->hasMany(TrProjectAssignment::class, 'intProject_ID', 'intProject_ID')
            ->whereNull('intSubProject_ID');
    }

    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(MUser::class, 'trProjectAssignment', 'intProject_ID', 'intUser_ID');
    }

    /**
     * Get all unique users assigned to this project (directly or via sub-projects).
     */
    public function allAssignedUsers()
    {
        if ($this->relationLoaded('assignments') && $this->assignments->every(fn($a) => $a->relationLoaded('user'))) {
            return $this->assignments->pluck('user')->filter()->unique('intUser_ID')->values();
        }

        return MUser::whereIn('intUser_ID', function ($q) {
            $q->select('intUser_ID')
                ->from('trProjectAssignment')
                ->where('intProject_ID', $this->intProject_ID);
        })->get();
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

    public function scopeForUser(Builder $query, $userId): Builder
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('intUser_ID', $userId)
                ->orWhereExists(function ($sub) use ($userId) {
                    $sub->select(DB::raw(1))
                        ->from('trProjectAssignment')
                        ->whereColumn('trProjectAssignment.intProject_ID', 'mProject.intProject_ID')
                        ->where('trProjectAssignment.intUser_ID', $userId);
                });
        });
    }

    public function isAdHoc(): bool
    {
        return (bool) $this->bitIsAdHoc || (int) $this->intProjectType_ID === 5;
    }

    public function scopeAdHoc(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('bitIsAdHoc', true)
                ->orWhere('intProjectType_ID', 5);
        });
    }

    public function scopeStandardProjects(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where(function ($sq) {
                $sq->whereNull('bitIsAdHoc')->orWhere('bitIsAdHoc', false);
            })->where('intProjectType_ID', '!=', 5);
        });
    }

    public function isPendingApproval(): bool
    {
        return $this->txtApprovalStatus === 'Pending Approval';
    }

    public function isApproved(): bool
    {
        return empty($this->txtApprovalStatus) || $this->txtApprovalStatus === 'Approved';
    }

    public function isRejected(): bool
    {
        return $this->txtApprovalStatus === 'Rejected';
    }

    public function scopePendingApproval(Builder $query): Builder
    {
        return $query->where('txtApprovalStatus', 'Pending Approval');
    }
}

