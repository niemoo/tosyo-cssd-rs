<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrayItem extends Model
{
    protected $fillable = [
        'tray_id',
        'instrument_item_id',
        'notes',
    ];

    // Relationships
    public function tray()
    {
        return $this->belongsTo(Tray::class);
    }

    public function instrumentItem()
    {
        return $this->belongsTo(InstrumentItem::class, 'instrument_item_id');
    }
}