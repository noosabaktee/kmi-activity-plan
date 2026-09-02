<?php

namespace App\Models;

use App\Models\Concerns\GeneratesIntegerIds;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MWaSchedule extends Model
{
    use GeneratesIntegerIds;

    protected $table = 'mWaSchedule';
    protected $primaryKey = 'intWaSchedule_ID';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'intWaSchedule_ID',
        'txtScheduleTitle',
        'txtCronDay',
        'txtCronExpression',
        'txtScheduledTime',
        'txtMessageTemplate',
        'txtFooterText',
        'txtTargetType',
        'intDepartment_ID',
        'intSubDepartment_ID',
        'intUser_ID',
        'txtTargetRole',
        'dtmLastSentAt',
        'bitActive',
        'txtInsertedBy',
        'dtmInserted',
        'txtUpdatedBy',
        'dtmUpdated',
    ];

    protected function casts(): array
    {
        return [
            'intWaSchedule_ID' => 'integer',
            'intDepartment_ID' => 'integer',
            'intSubDepartment_ID' => 'integer',
            'intUser_ID' => 'integer',
            'bitActive' => 'boolean',
            'dtmLastSentAt' => 'datetime',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(MUser::class, 'intUser_ID', 'intUser_ID');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('bitActive', true);
    }
}
