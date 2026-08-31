<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DigitalTwinScenario extends Model
{
    protected $fillable = [
        'shipment_id',
        'name',
        'engine_version',
        'input_snapshot',
        'baseline_snapshot',
        'result_snapshot',
        'comparison_snapshot',
        'evidence_coverage',
        'is_preferred',
    ];

    protected function casts(): array
    {
        return [
            'input_snapshot' => 'array',
            'baseline_snapshot' => 'array',
            'result_snapshot' => 'array',
            'comparison_snapshot' => 'array',
            'evidence_coverage' => 'integer',
            'is_preferred' => 'boolean',
        ];
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }
}
