<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guest_allocations', function (Blueprint $table) {
            $table->id();
            $table->string('guest_name');
            $table->foreignId('activity_session_id')->constrained('activity_sessions')->onDelete('cascade');
            $table->integer('pax');
            $table->string('source');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->index('activity_session_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_allocations');
    }
};
