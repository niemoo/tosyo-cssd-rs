<?php

namespace App\Models;

use App\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StorageRack extends Model
{
    use SoftDeletes, HasAuditColumns;

    protected $fillable = [
        'hospital_id',
        'name',
        'code',
        'location_desc',
        'capacity',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
            'capacity'   => 'integer',
            'deleted_at' => 'datetime',
        ];
    }

    // Relationships
    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }
}