<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Harvest extends Model
{
    protected $fillable = [
        'user_id',
        'commodity',
        'weight',
        'location',
        'harvest_date',
        'expiry_date',
    ];
    public function shipments()
{
    return $this->hasMany(Shipment::class);
}
}