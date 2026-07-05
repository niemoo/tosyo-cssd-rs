<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SterilizationBatchItem extends Model
{
    const RESULT_PENDING = 'PENDING';
    const RESULT_PASSED  = 'PASSED';
    const RESULT_FAILED  = 'FAILED';

    protected $fillable = [
        'batch_id',
        'tray_id',
        'result',
        'failure_notes',
    ];

    // Relationships
    public function batch()
    {
        return $this->belongsTo(SterilizationBatch::class, 'batch_id');
    }

    public function tray()
    {
        return $this->belongsTo(Tray::class);
    }
}