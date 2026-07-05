<?php

namespace App\Models;

use App\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hospital extends Model
{
    use SoftDeletes, HasAuditColumns;

    protected $fillable = [
        'name',
        'code',
        'address',
        'phone',
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
    public function hospitalUsers()
    {
        return $this->hasMany(HospitalUser::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'hospital_users')
                    ->withPivot('joined_at', 'is_active')
                    ->withTimestamps();
    }

    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    public function instrumentCategories()
    {
        return $this->hasMany(InstrumentCategory::class);
    }

    public function instruments()
    {
        return $this->hasMany(Instrument::class);
    }

    public function instrumentItems()
    {
        return $this->hasMany(InstrumentItem::class);
    }

    public function trayTemplates()
    {
        return $this->hasMany(TrayTemplate::class);
    }

    public function trays()
    {
        return $this->hasMany(Tray::class);
    }

    public function sterilizers()
    {
        return $this->hasMany(Sterilizer::class);
    }

    public function storageRacks()
    {
        return $this->hasMany(StorageRack::class);
    }

    public function consumableCategories()
    {
        return $this->hasMany(ConsumableCategory::class);
    }

    public function consumables()
    {
        return $this->hasMany(Consumable::class);
    }

    public function consumableStocks()
    {
        return $this->hasMany(ConsumableStock::class);
    }

    public function consumableMovements()
    {
        return $this->hasMany(ConsumableMovement::class);
    }
}