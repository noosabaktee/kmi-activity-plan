<?php

namespace App\Models;

use App\Models\Concerns\GeneratesIntegerIds;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrProjectAssignment extends Model
{
    use GeneratesIntegerIds;

    protected $table = 'trProjectAssignment';

    protected $primaryKey = 'intProjectAssignment_ID';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'intProjectAssignment_ID',
        'intProject_ID',
        'intSubProject_ID',
        'intUser_ID',
        'txtInsertedBy',
        'dtmInserted',
    ];

    protected $casts = [
        'intProjectAssignment_ID' => 'integer',
        'intProject_ID' => 'integer',
        'intSubProject_ID' => 'integer',
        'intUser_ID' => 'integer',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(MUser::class, 'intUser_ID', 'intUser_ID');
    }
}
