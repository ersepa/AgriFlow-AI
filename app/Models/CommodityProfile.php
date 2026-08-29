<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommodityProfile extends Model
{
    protected $fillable = [
        'name',
        'local_name',
        'slug',
        'category',
        'profile_context',
        'storage_life_min_days',
        'storage_life_max_days',
        'optimal_temp_min',
        'optimal_temp_max',
        'optimal_humidity_min',
        'optimal_humidity_max',
        'chilling_threshold_c',
        'q10_factor',
        'perishability_level',
        'temperature_control_recommended',
        'aliases',
        'notes',
        'source_name',
        'source_url',
    ];

    protected function casts(): array
    {
        return [
            'storage_life_min_days' => 'integer',
            'storage_life_max_days' => 'integer',
            'optimal_temp_min' => 'float',
            'optimal_temp_max' => 'float',
            'optimal_humidity_min' => 'float',
            'optimal_humidity_max' => 'float',
            'chilling_threshold_c' => 'float',
            'q10_factor' => 'float',
            'temperature_control_recommended' => 'boolean',
            'aliases' => 'array',
        ];
    }
}
