<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('digital_twin_scenarios', function (Blueprint $table) {
            $table->id();

            $table->foreignId('shipment_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name', 120);
            $table->string('engine_version', 40);

            $table->json('input_snapshot');
            $table->json('baseline_snapshot');
            $table->json('result_snapshot');
            $table->json('comparison_snapshot')->nullable();

            $table->unsignedTinyInteger('evidence_coverage')
                ->default(0);

            $table->boolean('is_preferred')
                ->default(false);

            $table->timestamps();

            $table->index([
                'shipment_id',
                'created_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'digital_twin_scenarios'
        );
    }
};
