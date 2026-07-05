<?php

namespace App\Models;

use App\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sterilizer extends Model
{
    use SoftDeletes, HasAuditColumns;

    protected $fillable = [
        'hospital_id',
        'name',
        'code',
        'type',
        'capacity',
        'serial_number',
        'last_maintenance_at',
        'next_maintenance_at',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active'           => 'boolean',
            'capacity'            => 'integer',
            'last_maintenance_at' => 'date',
            'next_maintenance_at' => 'date',
            'deleted_at'          => 'datetime',
        ];
    }

    // Relationships
    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    // Helpers
    public function isMaintenanceDue(): bool
    {
        if (!$this->next_maintenance_at) return false;
        return $this->next_maintenance_at->isPast()
            || $this->next_maintenance_at->isToday()
            || $this->next_maintenance_at->diffInDays(now()) <= 7;
    }
}