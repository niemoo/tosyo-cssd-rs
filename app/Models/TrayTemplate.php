<?php

namespace App\Models;

use App\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrayTemplate extends Model
{
    use SoftDeletes, HasAuditColumns;

    protected $fillable = [
        'hospital_id',
        'name',
        'code',
        'description',
        'is_lockable',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'is_lockable' => 'boolean',
            'is_active'   => 'boolean',
            'deleted_at'  => 'datetime',
        ];
    }

    // Relationships
    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    public function templateItems()
    {
        return $this->hasMany(TrayTemplateItem::class, 'template_id');
    }

    public function trays()
    {
        return $this->hasMany(Tray::class, 'template_id');
    }

    // Helpers
    public function getTotalInstrumentsAttribute(): int
    {
        return $this->templateItems->sum('quantity');
    }
}