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
        'recorded_temperature_c',
        'recorded_relative_humidity_percent',
        'recorded_moisture_percent',
        'condition_source',
        'condition_recorded_at',
        'origin_lat',
        'origin_lng',
        'destination_lat',
        'destination_lng',
        'route_geometry',
    ];

    protected function casts(): array
    {
        return [
            'recorded_temperature_c' => 'float',
            'recorded_relative_humidity_percent' => 'float',
            'recorded_moisture_percent' => 'float',
            'condition_recorded_at' => 'datetime',
        ];
    }

    public function harvest()
    {
        return $this->belongsTo(Harvest::class);
    }

    public function aiAnalyses()
    {
        return $this->hasMany(AiAnalysis::class);
    }

    public function decisionResult()
    {
        return $this->hasOne(DecisionResult::class);
    }
}
