<?php

namespace App\Models;

use App\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConsumableMovement extends Model
{
    use SoftDeletes, HasAuditColumns;

    const TYPE_IN  = 'IN';
    const TYPE_OUT = 'OUT';

    protected $fillable = [
        'hospital_id',
        'consumable_id',
        'type',
        'quantity',
        'reference_type',
        'reference_id',
        'notes',
        'handled_by',
        'moved_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity'   => 'integer',
            'moved_at'   => 'datetime',
            'deleted_at' => 'datetime',
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

    public function handler()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function reference()
    {
        return $this->morphTo(__FUNCTION__, 'reference_type', 'reference_id');
    }
}