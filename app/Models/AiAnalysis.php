<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiAnalysis extends Model
{
    protected $fillable = [
        'shipment_id',
        'risk_level',
        'waste_probability',
        'sustainability_score',
        'recommendations',
    ];
    public function shipment()
{
    return $this->belongsTo(Shipment::class);
}
}
