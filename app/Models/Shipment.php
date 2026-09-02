<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    public const ACTIVE_STATUSES = [
        'Harvested',
        'Packed',
        'In Transit',
    ];

    protected $fillable = [
        'harvest_id',
        'origin',
        'destination',
        'distance_km',
        'duration_hours',
        'carbon_emission',
        'route_score',
        'status',
        'delivered_at',
        'completion_snapshot',
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
            'delivered_at' => 'datetime',
            'completion_snapshot' => 'array',
            'recorded_temperature_c' => 'float',
            'recorded_relative_humidity_percent' => 'float',
            'recorded_moisture_percent' => 'float',
            'condition_recorded_at' => 'datetime',
        ];
    }

    public function scopeOperationallyActive(Builder $query): Builder
    {
        return $query->whereIn('status', self::ACTIVE_STATUSES);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'Delivered');
    }

    public function isOperationallyActive(): bool
    {
        return in_array($this->status, self::ACTIVE_STATUSES, true);
    }

    public function isDelivered(): bool
    {
        return $this->status === 'Delivered';
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
