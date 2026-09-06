<?php

namespace App\Models;

use App\Models\Concerns\GeneratesIntegerIds;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrDailyTask extends Model
{
    use GeneratesIntegerIds;

    protected $table = 'trDailyTask';
    protected $primaryKey = 'intDailyTask_ID';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'intDailyTask_ID',
        'intUser_ID',
        'intDepartment_ID',
        'intSubDepartment_ID',
        'intProjectType_ID',
        'intProject_ID',
        'intSubProject_ID',
        'intProjectStage_ID',
        'dtmTaskDate',
        'txtActivityDescription',
        'txtDeliverableOutput',
        'floatDurationHours',
        'floatProgressPercent',
        'txtTaskStatus',
        'txtNotes',
        'txtAttachmentPath',
        'txtAttachmentName',
        'txtAttachmentType',
        'txtInsertedBy',
        'dtmInserted',
    ];

    protected static function booted(): void
    {
        static::creating(function (TrDailyTask $task) {
            if (empty($task->dtmInserted)) {
                $task->dtmInserted = now();
            }
            if (empty($task->intDepartment_ID) && ! empty($task->intUser_ID)) {
                $user = MUser::find($task->intUser_ID);
                $task->intDepartment_ID = $user?->intDepartment_ID ?: 1;
                $task->intSubDepartment_ID = $user?->intSubDepartment_ID;
            }
            if (empty($task->intProjectType_ID) && ! empty($task->intProject_ID)) {
                $project = MProject::find($task->intProject_ID);
                $task->intProjectType_ID = $project?->intProjectType_ID;
            }
        });
    }

    protected function casts(): array
    {
        return [
            'intDailyTask_ID' => 'integer',
            'intUser_ID' => 'integer',
            'intDepartment_ID' => 'integer',
            'intSubDepartment_ID' => 'integer',
            'intProjectType_ID' => 'integer',
            'intProject_ID' => 'integer',
            'intSubProject_ID' => 'integer',
            'intProjectStage_ID' => 'integer',
            'dtmTaskDate' => 'date',
            'floatDurationHours' => 'float',
            'floatProgressPercent' => 'float',
            'dtmInserted' => 'datetime',
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

    public function subDepartment(): BelongsTo
    {
        return $this->belongsTo(MSubDepartment::class, 'intSubDepartment_ID', 'intSubDepartment_ID');
    }

    public function projectType(): BelongsTo
    {
        return $this->belongsTo(MProjectType::class, 'intProjectType_ID', 'intProjectType_ID');
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
