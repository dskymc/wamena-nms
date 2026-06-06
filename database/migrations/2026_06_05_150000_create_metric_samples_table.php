<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metric_samples', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->string('metric', 64);
            $table->string('source', 64)->nullable();
            $table->string('source_label')->nullable();
            $table->decimal('value', 24, 4);
            $table->string('unit', 20)->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['device_id', 'metric', 'recorded_at']);
            $table->index(['device_id', 'metric', 'source', 'recorded_at'], 'metric_samples_device_metric_source_time');
            $table->index('recorded_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metric_samples');
    }
};
