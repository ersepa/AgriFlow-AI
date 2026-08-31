<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'digital_twin_comparison_sets',
            function (Blueprint $table) {
                $table->id();

                $table->foreignId('shipment_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->string('name', 120);
                $table->string('engine_version', 50);

                $table->json('baseline_snapshot');
                $table->json('scenarios_snapshot');
                $table->json('comparison_snapshot');

                $table->string(
                    'preferred_option',
                    80
                )->nullable();

                $table->unsignedTinyInteger(
                    'evidence_coverage'
                )->default(0);

                $table->timestamps();

                $table->index([
                    'shipment_id',
                    'created_at',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'digital_twin_comparison_sets'
        );
    }
};
