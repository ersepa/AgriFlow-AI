<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->decimal('recorded_temperature_c', 5, 2)
                ->nullable()
                ->after('status');

            $table->decimal('recorded_relative_humidity_percent', 5, 2)
                ->nullable()
                ->after('recorded_temperature_c');

            $table->decimal('recorded_moisture_percent', 5, 2)
                ->nullable()
                ->after('recorded_relative_humidity_percent');

            $table->string('condition_source', 50)
                ->nullable()
                ->after('recorded_moisture_percent');

            $table->timestamp('condition_recorded_at')
                ->nullable()
                ->after('condition_source');
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn([
                'recorded_temperature_c',
                'recorded_relative_humidity_percent',
                'recorded_moisture_percent',
                'condition_source',
                'condition_recorded_at',
            ]);
        });
    }
};
