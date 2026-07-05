<?php

namespace App\Models;

use App\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tray extends Model
{
    use SoftDeletes, HasAuditColumns;

    const STATUS_ASSEMBLING         = 'ASSEMBLING';
    const STATUS_READY              = 'READY';
    const STATUS_IN_STERILIZATION   = 'IN_STERILIZATION';
    const STATUS_STERILE            = 'STERILE';
    const STATUS_IN_USE             = 'IN_USE';
    const STATUS_RETURNED           = 'RETURNED';
    const STATUS_NEEDS_REPROCESSING = 'NEEDS_REPROCESSING';

    const STATUSES = [
        self::STATUS_ASSEMBLING         => ['label' => 'Dirakit',           'color' => 'blue'],
        self::STATUS_READY              => ['label' => 'Siap Sterilisasi',   'color' => 'amber'],
        self::STATUS_IN_STERILIZATION   => ['label' => 'Dalam Sterilisasi',  'color' => 'purple'],
        self::STATUS_STERILE            => ['label' => 'Steril',             'color' => 'green'],
        self::STATUS_IN_USE             => ['label' => 'Sedang Digunakan',   'color' => 'teal'],
        self::STATUS_RETURNED           => ['label' => 'Dikembalikan',       'color' => 'gray'],
        self::STATUS_NEEDS_REPROCESSING => ['label' => 'Perlu Diproses Ulang', 'color' => 'red'],
    ];

    protected $fillable = [
        'hospital_id',
        'template_id',
        'code',
        'name',
        'status',
        'current_rack_id',
        'assembled_by',
        'assembled_at',
        'notes',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active'   => 'boolean',
            'assembled_at' => 'datetime',
            'deleted_at'  => 'datetime',
        ];
    }

    // Relationships
    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    public function template()
    {
        return $this->belongsTo(TrayTemplate::class, 'template_id');
    }

    public function currentRack()
    {
        return $this->belongsTo(StorageRack::class, 'current_rack_id');
    }

    public function assembler()
    {
        return $this->belongsTo(User::class, 'assembled_by');
    }

    public function items()
    {
        return $this->hasMany(TrayItem::class);
    }

    public function sterilizationBatchItems()
    {
        return $this->hasMany(SterilizationBatchItem::class);
    }

    public function consumableUsages()
    {
        return $this->morphMany(ConsumableUsage::class, 'usageable');
    }

    public function returns()
    {
        return $this->hasMany(TrayReturn::class);
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

    public function isSterile(): bool
    {
        return $this->status === self::STATUS_STERILE;
    }

    public function canBeSterilized(): bool
    {
        return $this->status === self::STATUS_READY;
    }
}