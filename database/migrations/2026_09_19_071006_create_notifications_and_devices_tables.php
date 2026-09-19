<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('token')->unique();
            $table->string('platform', 20)->default('android'); // android, ios, web
            $table->string('locale', 10)->default('gu');
            $table->timestamps();
        });

        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('event_key')->unique();
            $table->string('channel', 20)->default('all');
            $table->json('title_template'); // {"gu": "...", "en": "..."}
            $table->json('body_template');  // {"gu": "...", "en": "..."}
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('notifications_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('channel', 20); // fcm, in_app, sms
            $table->string('type', 50);
            $table->string('title');
            $table->text('body');
            $table->json('data')->nullable();
            $table->string('status', 20)->default('sent'); // sent, delivered, opened, failed
            $table->text('error')->nullable();
            $table->string('fcm_message_id')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications_log');
        Schema::dropIfExists('notification_templates');
        Schema::dropIfExists('device_tokens');
    }
};
