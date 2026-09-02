<?php

namespace App\Models;

use App\Models\Concerns\GeneratesIntegerIds;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrSupervisorSubDept extends Model
{
    use GeneratesIntegerIds;

    protected $table = 'trSupervisorSubDept';

    protected $primaryKey = 'intSupervisorSubDept_ID';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'intSupervisorSubDept_ID',
        'intUser_ID',
        'intSubDepartment_ID',
        'txtInsertedBy',
        'dtmInserted',
    ];

    protected $casts = [
        'dtmInserted' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(MUser::class, 'intUser_ID', 'intUser_ID');
    }

    public function subDepartment(): BelongsTo
    {
        return $this->belongsTo(MSubDepartment::class, 'intSubDepartment_ID', 'intSubDepartment_ID');
    }
}
