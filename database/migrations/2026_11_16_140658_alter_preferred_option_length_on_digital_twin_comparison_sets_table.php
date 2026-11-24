<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'digital_twin_comparison_sets',
            function (Blueprint $table) {
                $table->string('preferred_option', 120)
                    ->nullable()
                    ->change();
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'digital_twin_comparison_sets',
            function (Blueprint $table) {
                $table->string('preferred_option', 80)
                    ->nullable()
                    ->change();
            }
        );
    }
};