<?php

namespace App\Models;

use App\Models\Concerns\GeneratesIntegerIds;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrWaLog extends Model
{
    use GeneratesIntegerIds;

    protected $table = 'trWaLog';

    protected $primaryKey = 'intWaLog_ID';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'intWaLog_ID',
        'intWaSchedule_ID',
        'intUser_ID',
        'txtRecipientPhone',
        'txtRecipientName',
        'txtMessage',
        'txtStatus',
        'txtApiResponse',
        'dtmSentAt',
        'txtInsertedBy',
        'dtmInserted',
    ];

    protected $casts = [
        'dtmSentAt' => 'datetime',
        'dtmInserted' => 'datetime',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(MWaSchedule::class, 'intWaSchedule_ID', 'intWaSchedule_ID');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(MUser::class, 'intUser_ID', 'intUser_ID');
    }
}
