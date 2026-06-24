<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::create('ai_analyses', function (Blueprint $table) {
        $table->id();

        $table->foreignId('shipment_id')->constrained()->onDelete('cascade');

        $table->string('risk_level');
        $table->string('waste_probability');
        $table->integer('sustainability_score');

        $table->text('recommendations');

        $table->timestamps();
    });
}
};
