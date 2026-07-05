<?php

namespace App\Models;

use App\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;

class TrayReturn extends Model
{
    use HasAuditColumns;

    const CONDITION_GOOD       = 'GOOD';
    const CONDITION_DAMAGED    = 'DAMAGED';
    const CONDITION_INCOMPLETE = 'INCOMPLETE';

    const CONDITIONS = [
        self::CONDITION_GOOD       => ['label' => 'Baik',       'color' => 'green'],
        self::CONDITION_DAMAGED    => ['label' => 'Rusak',      'color' => 'red'],
        self::CONDITION_INCOMPLETE => ['label' => 'Tidak Lengkap', 'color' => 'amber'],
    ];

    protected $fillable = [
        'hospital_id',
        'distribution_request_id',
        'tray_id',
        'received_by',
        'condition',
        'missing_items',
        'notes',
        'returned_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'returned_at' => 'datetime',
        ];
    }

    // Relationships
    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    public function distributionRequest()
    {
        return $this->belongsTo(DistributionRequest::class, 'distribution_request_id');
    }

    public function tray()
    {
        return $this->belongsTo(Tray::class);
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    // Helpers
    public function getConditionLabelAttribute(): string
    {
        return self::CONDITIONS[$this->condition]['label'] ?? $this->condition;
    }

    public function getConditionColorAttribute(): string
    {
        return self::CONDITIONS[$this->condition]['color'] ?? 'gray';
    }
}