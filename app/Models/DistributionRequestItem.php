<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DistributionRequestItem extends Model
{
    protected $fillable = [
        'request_id',
        'template_id',
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

    // Satu item bisa dipenuhi beberapa tray (sesuai quantity).
    public function trays()
    {
        return $this->belongsToMany(Tray::class, 'distribution_request_item_trays', 'item_id', 'tray_id')
            ->withPivot('issued_by', 'issued_at')
            ->withTimestamps();
    }

    // Helpers
    public function assignedCount(): int
    {
        return $this->trays()->count();
    }

    public function remaining(): int
    {
        return max(0, $this->quantity - $this->assignedCount());
    }

    public function isFullyAssigned(): bool
    {
        return $this->remaining() === 0;
    }
}
