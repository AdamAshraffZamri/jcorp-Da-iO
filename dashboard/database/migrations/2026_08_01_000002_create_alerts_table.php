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
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kpi_metric_id')->constrained('kpi_metrics')->cascadeOnDelete();
            $table->string('title');
            $table->enum('risk_score', ['Critical', 'High', 'Low']);
            $table->text('root_cause');
            $table->text('recommended_action');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
