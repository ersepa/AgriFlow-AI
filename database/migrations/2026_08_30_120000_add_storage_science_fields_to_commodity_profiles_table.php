<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commodity_profiles', function (Blueprint $table) {
            $table->string('commodity_class')
                ->default('fresh_produce')
                ->after('category');

            $table->string('quality_model_type')
                ->default('shelf_life_quality')
                ->after('commodity_class');

            $table->decimal(
                'safe_moisture_short_term_max_percent',
                5,
                2
            )->nullable();

            $table->decimal(
                'safe_moisture_long_term_max_percent',
                5,
                2
            )->nullable();

            $table->decimal(
                'safe_relative_humidity_max_percent',
                5,
                2
            )->nullable();

            $table->unsignedSmallInteger(
                'reference_storage_max_months'
            )->nullable();

            $table->json('source_references')->nullable();

            $table->text(
                'storage_science_note'
            )->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('commodity_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'commodity_class',
                'quality_model_type',
                'safe_moisture_short_term_max_percent',
                'safe_moisture_long_term_max_percent',
                'safe_relative_humidity_max_percent',
                'reference_storage_max_months',
                'source_references',
                'storage_science_note',
            ]);
        });
    }
};
