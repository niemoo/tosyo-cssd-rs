<?php

namespace App\Models;

use App\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DistributionRequest extends Model
{
    use SoftDeletes, HasAuditColumns;

    const STATUS_DRAFT            = 'DRAFT';
    const STATUS_PENDING_APPROVAL = 'PENDING_APPROVAL';
    const STATUS_APPROVED         = 'APPROVED';
    const STATUS_REJECTED         = 'REJECTED';
    const STATUS_IN_PROCESS       = 'IN_PROCESS';
    const STATUS_FULFILLED        = 'FULFILLED';
    const STATUS_CLOSED           = 'CLOSED';

    const STATUSES = [
        self::STATUS_DRAFT            => ['label' => 'Draft',              'color' => 'gray'],
        self::STATUS_PENDING_APPROVAL => ['label' => 'Menunggu Approval',  'color' => 'amber'],
        self::STATUS_APPROVED         => ['label' => 'Disetujui',          'color' => 'blue'],
        self::STATUS_REJECTED         => ['label' => 'Ditolak',            'color' => 'red'],
        self::STATUS_IN_PROCESS       => ['label' => 'Diproses',           'color' => 'purple'],
        self::STATUS_FULFILLED        => ['label' => 'Terpenuhi',          'color' => 'green'],
        self::STATUS_CLOSED           => ['label' => 'Selesai',            'color' => 'teal'],
    ];

    protected $fillable = [
        'hospital_id',
        'unit_id',
        'request_number',
        'status',
        'requested_by',
        'approved_by',
        'fulfilled_by',
        'requested_at',
        'approved_at',
        'fulfilled_at',
        'notes',
        'rejection_notes',
        'revision_notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'requested_at' => 'datetime',
            'approved_at'  => 'datetime',
            'fulfilled_at' => 'datetime',
            'deleted_at'   => 'datetime',
        ];
    }

    // Relationships
    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function fulfiller()
    {
        return $this->belongsTo(User::class, 'fulfilled_by');
    }

    public function items()
    {
        return $this->hasMany(DistributionRequestItem::class, 'request_id');
    }

    public function trayReturns()
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

    public function canBeApproved(): bool
    {
        return $this->status === self::STATUS_PENDING_APPROVAL;
    }

    public function canBeRevised(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function canBeFulfilled(): bool
    {
        return in_array($this->status, [self::STATUS_APPROVED, self::STATUS_IN_PROCESS]);
    }
}