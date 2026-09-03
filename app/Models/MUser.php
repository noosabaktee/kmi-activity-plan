<?php

namespace App\Models;

use App\Models\Concerns\GeneratesIntegerIds;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MUser extends Model
{
    use GeneratesIntegerIds;

    protected $table = 'mUser';
    protected $primaryKey = 'intUser_ID';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'intUser_ID',
        'intDepartment_ID',
        'intSubDepartment_ID',
        'txtEmployeeName',
        'txtEmployeeCode',
        'txtEmail',
        'txtPassword',
        'txtPhone',
        'txtRole',
        'txtPosition',
        'txtAvatarPath',
        'bitActive',
        'txtInsertedBy',
        'dtmInserted',
        'txtUpdatedBy',
        'dtmUpdated',
    ];

    protected $hidden = [
        'txtPassword',
    ];

    protected function casts(): array
    {
        return [
            'intUser_ID' => 'integer',
            'intDepartment_ID' => 'integer',
            'intSubDepartment_ID' => 'integer',
            'bitActive' => 'boolean',
            'dtmInserted' => 'datetime',
            'dtmUpdated' => 'datetime',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(MDepartment::class, 'intDepartment_ID', 'intDepartment_ID');
    }

    public function subDepartment(): BelongsTo
    {
        return $this->belongsTo(MSubDepartment::class, 'intSubDepartment_ID', 'intSubDepartment_ID');
    }

    public function supervisedSubDepartments(): BelongsToMany
    {
        return $this->belongsToMany(
            MSubDepartment::class,
            'trSupervisorSubDept',
            'intUser_ID',
            'intSubDepartment_ID'
        );
    }

    public function projects(): HasMany
    {
        return $this->hasMany(MProject::class, 'intUser_ID', 'intUser_ID')
            ->where('bitActive', true);
    }

    public function projectAssignments(): HasMany
    {
        return $this->hasMany(TrProjectAssignment::class, 'intUser_ID', 'intUser_ID');
    }

    public function assignedProjects(): BelongsToMany
    {
        return $this->belongsToMany(MProject::class, 'trProjectAssignment', 'intUser_ID', 'intProject_ID')
            ->distinct();
    }

    public function assignedSubProjects(): BelongsToMany
    {
        return $this->belongsToMany(TrSubProject::class, 'trProjectAssignment', 'intUser_ID', 'intSubProject_ID')
            ->whereNotNull('trProjectAssignment.intSubProject_ID')
            ->distinct();
    }

    public function dailyTasks(): HasMany
    {
        return $this->hasMany(TrDailyTask::class, 'intUser_ID', 'intUser_ID');
    }

    public function weeklyPlans(): HasMany
    {
        return $this->hasMany(MWeeklyPlan::class, 'intUser_ID', 'intUser_ID')
            ->where('bitActive', true);
    }

    public function dailyPlanActivities(): HasMany
    {
        return $this->hasMany(TrDailyPlanActivity::class, 'intUser_ID', 'intUser_ID');
    }

    public function isHead(): bool
    {
        return $this->txtRole === 'Head';
    }

    public function isSupervisor(): bool
    {
        return $this->txtRole === 'Supervisor';
    }

    public function isEmployee(): bool
    {
        return $this->txtRole === 'Employee';
    }

    public function isSuperadmin(): bool
    {
        return $this->txtRole === 'Superadmin';
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('bitActive', true);
    }
}
