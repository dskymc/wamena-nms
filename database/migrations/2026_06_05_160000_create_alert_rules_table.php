<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alert_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_enabled')->default(true);
            $table->string('trigger_type');
            $table->string('scope_type')->default('global');
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('device_id')->nullable()->constrained()->nullOnDelete();
            $table->string('metric')->nullable();
            $table->string('operator')->nullable();
            $table->decimal('threshold', 20, 4)->nullable();
            $table->unsignedTinyInteger('consecutive_breaches')->default(1);
            $table->string('severity')->default('warning');
            $table->unsignedInteger('cooldown_minutes')->default(15);
            $table->json('channels');
            $table->boolean('notify_on_resolve')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alert_rules');
    }
};
