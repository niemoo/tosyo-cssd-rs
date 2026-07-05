<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DistributionRequestItem extends Model
{
    protected $fillable = [
        'request_id',
        'template_id',
        'tray_id',
        'quantity',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    // Relationships
    public function request()
    {
        return $this->belongsTo(DistributionRequest::class, 'request_id');
    }

    public function template()
    {
        return $this->belongsTo(TrayTemplate::class, 'template_id');
    }

    public function tray()
    {
        return $this->belongsTo(Tray::class);
    }
}