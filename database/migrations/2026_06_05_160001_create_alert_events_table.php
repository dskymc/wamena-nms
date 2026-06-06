<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alert_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alert_rule_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->string('severity');
            $table->string('state')->default('open');
            $table->string('message');
            $table->text('detail')->nullable();
            $table->decimal('metric_value', 20, 4)->nullable();
            $table->timestamp('fired_at');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('last_notified_at')->nullable();
            $table->json('notification_log')->nullable();
            $table->timestamps();

            $table->index(['device_id', 'state']);
            $table->index(['alert_rule_id', 'state']);
            $table->index('fired_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alert_events');
    }
};
