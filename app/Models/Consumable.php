<?php

namespace App\Models;

use App\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Consumable extends Model
{
    use SoftDeletes, HasAuditColumns;

    protected $fillable = [
        'hospital_id',
        'category_id',
        'name',
        'code',
        'unit',
        'minimum_stock',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active'     => 'boolean',
            'minimum_stock' => 'integer',
            'deleted_at'    => 'datetime',
        ];
    }

    // Relationships
    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    public function category()
    {
        return $this->belongsTo(ConsumableCategory::class, 'category_id');
    }

    public function stock()
    {
        return $this->hasOne(ConsumableStock::class);
    }

    public function movements()
    {
        return $this->hasMany(ConsumableMovement::class);
    }

    // Helpers
    public function getCurrentStockAttribute(): int
    {
        return $this->stock?->quantity ?? 0;
    }

    public function isLowStock(): bool
    {
        return $this->current_stock <= $this->minimum_stock;
    }
}