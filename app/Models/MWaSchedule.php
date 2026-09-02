<?php

namespace App\Models;

use App\Models\Concerns\GeneratesIntegerIds;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

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
        'txtCronExpression',
        'txtScheduledTime',
        'txtMessageTemplate',
        'txtTargetRole',
        'bitIsActive',
        'dtmLastExecuted',
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
            'bitIsActive' => 'boolean',
            'bitActive' => 'boolean',
            'dtmLastExecuted' => 'datetime',
            'dtmInserted' => 'datetime',
            'dtmUpdated' => 'datetime',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('bitActive', true);
    }
}
