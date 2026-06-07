<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('topology_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_device_id')->constrained('devices')->cascadeOnDelete();
            $table->string('source_if_index', 32)->nullable();
            $table->string('source_port_label')->nullable();
            $table->foreignId('target_device_id')->nullable()->constrained('devices')->nullOnDelete();
            $table->string('remote_sys_name')->nullable();
            $table->string('remote_chassis_id')->nullable();
            $table->string('remote_port_label')->nullable();
            $table->string('remote_mgmt_ip', 45)->nullable();
            $table->string('protocol', 16)->default('lldp');
            $table->timestamp('discovered_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->index(['source_device_id', 'last_seen_at']);
            $table->index('target_device_id');
            $table->unique(
                ['source_device_id', 'source_if_index', 'remote_chassis_id', 'protocol'],
                'topology_links_source_remote_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('topology_links');
    }
};
