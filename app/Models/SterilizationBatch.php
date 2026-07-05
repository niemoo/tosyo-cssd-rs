<?php

namespace App\Models;

use App\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SterilizationBatch extends Model
{
    use SoftDeletes, HasAuditColumns;

    const STATUS_PENDING     = 'PENDING';
    const STATUS_IN_PROGRESS = 'IN_PROGRESS';
    const STATUS_COMPLETED   = 'COMPLETED';
    const STATUS_FAILED      = 'FAILED';

    const STATUSES = [
        self::STATUS_PENDING     => ['label' => 'Menunggu',       'color' => 'gray'],
        self::STATUS_IN_PROGRESS => ['label' => 'Berjalan',       'color' => 'blue'],
        self::STATUS_COMPLETED   => ['label' => 'Selesai',        'color' => 'green'],
        self::STATUS_FAILED      => ['label' => 'Gagal',          'color' => 'red'],
    ];

    protected $fillable = [
        'hospital_id',
        'sterilizer_id',
        'batch_number',
        'status',
        'temperature',
        'pressure',
        'duration_minutes',
        'operator_id',
        'started_at',
        'completed_at',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'temperature'      => 'decimal:2',
            'pressure'         => 'decimal:2',
            'duration_minutes' => 'integer',
            'started_at'       => 'datetime',
            'completed_at'     => 'datetime',
            'deleted_at'       => 'datetime',
        ];
    }

    // Relationships
    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    public function sterilizer()
    {
        return $this->belongsTo(Sterilizer::class);
    }

    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function items()
    {
        return $this->hasMany(SterilizationBatchItem::class, 'batch_id');
    }

    public function trays()
    {
        return $this->belongsToMany(Tray::class, 'sterilization_batch_items', 'batch_id', 'tray_id')
                    ->withPivot('result', 'failure_notes')
                    ->withTimestamps();
    }

    public function consumableUsages()
    {
        return $this->morphMany(ConsumableUsage::class, 'usageable');
    }

    // Helpers
    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status]['label'] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUSES[$this->status]['color'] ?? 'gray';
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function hasFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }
}