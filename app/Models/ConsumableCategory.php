<?php

namespace App\Models;

use App\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConsumableCategory extends Model
{
    use SoftDeletes, HasAuditColumns;

    protected $fillable = [
        'hospital_id',
        'name',
        'code',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    // Relationships
    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    public function consumables()
    {
        return $this->hasMany(Consumable::class, 'category_id');
    }
}