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
        'intProject_ID',
        'intSubProject_ID',
        'dtmTaskDate',
        'txtActivityDescription',
        'txtDeliverableOutput',
        'floatDurationHours',
        'floatProgressPercent',
        'txtTaskStatus',
        'txtNotes',
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
        });
    }

    protected function casts(): array
    {
        return [
            'intDailyTask_ID' => 'integer',
            'intUser_ID' => 'integer',
            'intDepartment_ID' => 'integer',
            'intSubDepartment_ID' => 'integer',
            'intProject_ID' => 'integer',
            'intSubProject_ID' => 'integer',
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

    public function project(): BelongsTo
    {
        return $this->belongsTo(MProject::class, 'intProject_ID', 'intProject_ID');
    }

    public function subProject(): BelongsTo
    {
        return $this->belongsTo(TrSubProject::class, 'intSubProject_ID', 'intSubProject_ID');
    }
}
