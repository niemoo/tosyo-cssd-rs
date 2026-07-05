<?php

namespace App\Models;

use App\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrayTemplateItem extends Model
{
    use SoftDeletes, HasAuditColumns;

    protected $fillable = [
        'template_id',
        'instrument_id',
        'quantity',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity'   => 'integer',
            'is_active'  => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    // Relationships
    public function template()
    {
        return $this->belongsTo(TrayTemplate::class, 'template_id');
    }

    public function instrument()
    {
        return $this->belongsTo(Instrument::class);
    }
}