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
                $table->index(
                    'created_at',
                    'dt_comparison_sets_created_at_index'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'digital_twin_comparison_sets',
            function (Blueprint $table) {
                $table->dropIndex(
                    'dt_comparison_sets_created_at_index'
                );
            }
        );
    }
};