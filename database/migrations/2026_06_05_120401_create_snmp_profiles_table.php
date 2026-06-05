<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('snmp_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('version', 2);
            $table->unsignedSmallInteger('port')->default(161);
            $table->unsignedInteger('timeout_ms')->default(5000);
            $table->unsignedTinyInteger('retries')->default(3);
            $table->text('community')->nullable();
            $table->string('security_level')->nullable();
            $table->string('username')->nullable();
            $table->string('auth_protocol')->nullable();
            $table->text('auth_passphrase')->nullable();
            $table->string('priv_protocol')->nullable();
            $table->text('priv_passphrase')->nullable();
            $table->string('context_name')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('snmp_profiles');
    }
};
