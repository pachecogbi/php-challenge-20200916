<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->text('message');
            $table->dateTime('log_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
