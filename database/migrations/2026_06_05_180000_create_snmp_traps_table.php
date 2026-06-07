<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('snmp_traps', function (Blueprint $table) {
            $table->id();
            $table->string('source_ip', 45);
            $table->foreignId('device_id')->nullable()->constrained()->nullOnDelete();
            $table->string('snmp_version', 8);
            $table->string('trap_oid')->nullable();
            $table->string('trap_name')->default('unknown');
            $table->json('varbinds')->nullable();
            $table->text('summary')->nullable();
            $table->timestamp('received_at');
            $table->timestamps();

            $table->index('received_at');
            $table->index('device_id');
            $table->index('source_ip');
            $table->index('trap_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('snmp_traps');
    }
};
