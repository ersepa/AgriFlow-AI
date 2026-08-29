<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commodity_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('local_name')->nullable();
            $table->string('slug')->unique();
            $table->string('category');
            $table->string('profile_context')->nullable();

            $table->unsignedSmallInteger('storage_life_min_days')->nullable();
            $table->unsignedSmallInteger('storage_life_max_days')->nullable();

            $table->decimal('optimal_temp_min', 5, 2)->nullable();
            $table->decimal('optimal_temp_max', 5, 2)->nullable();
            $table->decimal('optimal_humidity_min', 5, 2)->nullable();
            $table->decimal('optimal_humidity_max', 5, 2)->nullable();

            // Temperature below which chilling injury can become a concern.
            // Null means we are not defining a chilling threshold for this MVP profile.
            $table->decimal('chilling_threshold_c', 5, 2)->nullable();

            // Reserved for Step 3 deterioration modeling. We keep it nullable so
            // we do not pretend to have commodity-specific Q10 values yet.
            $table->decimal('q10_factor', 4, 2)->nullable();

            $table->string('perishability_level');
            $table->boolean('temperature_control_recommended')->default(false);
            $table->json('aliases')->nullable();

            $table->text('notes')->nullable();
            $table->string('source_name');
            $table->text('source_url');

            $table->timestamps();

            $table->index(['category', 'perishability_level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commodity_profiles');
    }
};
