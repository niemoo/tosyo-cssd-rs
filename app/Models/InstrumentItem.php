<?php

namespace App\Models;

use App\Traits\HasAuditColumns;
use App\Traits\HasQrCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstrumentItem extends Model
{
    use SoftDeletes, HasAuditColumns, HasQrCode;

    protected $fillable = [
        'hospital_id',
        'instrument_id',
        'serial_number',
        'code',
        'barcode',
        'rfid_tag',
        'condition',
        'total_cycles',
        'current_tray_id',
        'purchased_at',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active'    => 'boolean',
            'total_cycles' => 'integer',
            'purchased_at' => 'date',
            'deleted_at'   => 'datetime',
        ];
    }

    // Relationships
    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    public function instrument()
    {
        return $this->belongsTo(Instrument::class);
    }

    public function currentTray()
    {
        return $this->belongsTo(Tray::class, 'current_tray_id');
    }

    // Helpers
    public function isNearingLifespan(): bool
    {
        $lifespan = $this->instrument->lifespan_cycles;
        if (!$lifespan) return false;
        return $this->total_cycles >= ($lifespan * 0.9);
    }

    public function getRemainingCyclesAttribute(): ?int
    {
        $lifespan = $this->instrument->lifespan_cycles;
        if (!$lifespan) return null;
        return max(0, $lifespan - $this->total_cycles);
    }
}