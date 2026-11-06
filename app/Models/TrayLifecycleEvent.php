<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrayLifecycleEvent extends Model
{
    // Jenis peristiwa + label & warna untuk timeline di UI.
    const TYPES = [
        'ASSEMBLE_START' => ['label' => 'Mulai Perakitan',           'color' => 'blue'],
        'PACKAGE' => ['label' => 'Dikemas & Siap Sterilisasi', 'color' => 'amber'],
        'LOAD' => ['label' => 'Dimuat ke Batch',            'color' => 'purple'],
        'STERILIZE_PASS' => ['label' => 'Sterilisasi Lulus',          'color' => 'green'],
        'STERILIZE_FAIL' => ['label' => 'Sterilisasi Gagal',          'color' => 'red'],
        'ISSUE' => ['label' => 'Didistribusikan ke Unit',    'color' => 'teal'],
        'RETURN' => ['label' => 'Dikembalikan (Baik)',        'color' => 'gray'],
        'RETURN_DAMAGED' => ['label' => 'Dikembalikan (Bermasalah)',  'color' => 'red'],
        'EXPIRE' => ['label' => 'Kedaluwarsa',                'color' => 'red'],
        'REPROCESS' => ['label' => 'Diproses Ulang',             'color' => 'blue'],
        'RETIRE' => ['label' => 'Dipensiunkan',               'color' => 'gray'],
    ];

    protected $fillable = [
        'hospital_id',
        'tray_id',
        'event_type',
        'from_status',
        'to_status',
        'reference_type',
        'reference_id',
        'context',
        'performed_by',
        'performed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'context' => 'array',
            'performed_at' => 'datetime',
        ];
    }

    // Relationships
    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    public function tray()
    {
        return $this->belongsTo(Tray::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    // Helpers
    public function getLabelAttribute(): string
    {
        return self::TYPES[$this->event_type]['label'] ?? $this->event_type;
    }

    public function getColorAttribute(): string
    {
        return self::TYPES[$this->event_type]['color'] ?? 'gray';
    }
}
