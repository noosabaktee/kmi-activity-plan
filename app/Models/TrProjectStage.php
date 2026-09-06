<?php

namespace App\Models;

use App\Models\Concerns\GeneratesIntegerIds;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrProjectStage extends Model
{
    use GeneratesIntegerIds;

    protected $table = 'trProjectStage';

    protected $primaryKey = 'intProjectStage_ID';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'intProjectStage_ID',
        'intProject_ID',
        'intSubProject_ID',
        'intProjectStageNumber',
        'txtProjectStageStep',
        'dtmProjectStageStartDate',
        'dtmProjectStageEndDate',
        'floatProjectStagePlan',
        'floatProjectStageActual',
        'txtInsertedBy',
        'dtmInserted',
    ];

    protected $casts = [
        'intProjectStageNumber' => 'integer',
        'floatProjectStagePlan' => 'float',
        'floatProjectStageActual' => 'float',
        'dtmProjectStageStartDate' => 'datetime',
        'dtmProjectStageEndDate' => 'datetime',
        'dtmInserted' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(MProject::class, 'intProject_ID', 'intProject_ID');
    }

    public function subProject(): BelongsTo
    {
        return $this->belongsTo(TrSubProject::class, 'intSubProject_ID', 'intSubProject_ID');
    }

    public function dailyPlanActivities(): HasMany
    {
        return $this->hasMany(TrDailyPlanActivity::class, 'intProjectStage_ID', 'intProjectStage_ID');
    }
}
