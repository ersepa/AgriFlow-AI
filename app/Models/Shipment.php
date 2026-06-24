<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
protected $fillable = [
    'harvest_id',
    'origin',
    'destination',
    'distance_km',
    'duration_hours',
    'carbon_emission',
    'route_score',
    'status',
    'origin_lat',
    'origin_lng',
    'destination_lat',
    'destination_lng',
    'route_geometry',
];

    public function harvest()
    {
        return $this->belongsTo(Harvest::class);
    }
    public function aiAnalyses()
{
    return $this->hasMany(AiAnalysis::class);
}
}