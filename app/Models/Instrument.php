<?php

namespace App\Models;

use App\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Instrument extends Model
{
    use SoftDeletes, HasAuditColumns;

    protected $fillable = [
        'hospital_id',
        'category_id',
        'name',
        'code',
        'brand',
        'material',
        'lifespan_cycles',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active'       => 'boolean',
            'lifespan_cycles' => 'integer',
            'deleted_at'      => 'datetime',
        ];
    }

    // Relationships
    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    public function category()
    {
        return $this->belongsTo(InstrumentCategory::class, 'category_id');
    }

    public function items()
    {
        return $this->hasMany(InstrumentItem::class);
    }

    public function trayTemplateItems()
    {
        return $this->hasMany(TrayTemplateItem::class);
    }

    // Helpers
    public function getAvailableItemsCountAttribute(): int
    {
        return $this->items()
                    ->where('condition', 'GOOD')
                    ->whereNull('current_tray_id')
                    ->where('is_active', true)
                    ->count();
    }
}