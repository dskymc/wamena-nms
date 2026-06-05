<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('snmp_profile_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('management_ip')->unique();
            $table->string('hostname')->nullable();
            $table->string('vendor');
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_monitored')->default(true);
            $table->unsignedInteger('poll_interval_sec')->default(300);
            $table->timestamp('last_seen_at')->nullable();
            $table->string('status')->default('unknown');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
