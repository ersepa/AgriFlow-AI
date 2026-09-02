<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->timestamp('delivered_at')
                ->nullable()
                ->after('status');

            $table->json('completion_snapshot')
                ->nullable()
                ->after('delivered_at');

            $table->index(['status', 'delivered_at']);
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropIndex(['status', 'delivered_at']);
            $table->dropColumn([
                'delivered_at',
                'completion_snapshot',
            ]);
        });
    }
};
