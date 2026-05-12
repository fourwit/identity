<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('log_name')->nullable();
            $table->string('description');
            $table->nullableMorphs('subject');      // user, order, etc.
            $table->nullableMorphs('causer');       // who did the action
            $table->json('properties')->nullable(); // old/new values
            $table->string('event')->nullable();    // created, updated, deleted
            $table->string('source')->default('web'); // web or api
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['log_name', 'event']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};