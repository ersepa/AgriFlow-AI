<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DigitalTwinComparisonSet extends Model
{
    protected $fillable = [
        'shipment_id',
        'name',
        'engine_version',
        'baseline_snapshot',
        'scenarios_snapshot',
        'comparison_snapshot',
        'preferred_option',
        'evidence_coverage',
    ];

    protected function casts(): array
    {
        return [
            'baseline_snapshot' => 'array',
            'scenarios_snapshot' => 'array',
            'comparison_snapshot' => 'array',
            'evidence_coverage' => 'integer',
        ];
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }
}
