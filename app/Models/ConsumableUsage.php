<?php

namespace App\Models;

use App\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;

class ConsumableUsage extends Model
{
    use HasAuditColumns;

    protected $fillable = [
        'hospital_id',
        'consumable_id',
        'usageable_type',
        'usageable_id',
        'quantity',
        'notes',
        'used_by',
        'used_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'used_at'  => 'datetime',
        ];
    }

    // Relationships
    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    public function consumable()
    {
        return $this->belongsTo(Consumable::class);
    }

    public function usageable()
    {
        return $this->morphTo();
    }

    public function usedBy()
    {
        return $this->belongsTo(User::class, 'used_by');
    }
}