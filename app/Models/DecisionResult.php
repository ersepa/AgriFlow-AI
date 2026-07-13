<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DecisionResult extends Model
{
    protected $fillable = [
        'shipment_id',
        'risk_score',
        'priority_score',
        'carbon_score',
        'sustainability_score',
        'recommended_action',
        'ai_explanation',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }
}