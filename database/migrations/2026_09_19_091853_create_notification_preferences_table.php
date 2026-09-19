<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // Channel-level toggles
            $table->boolean('email_enabled')->default(true);
            $table->boolean('push_enabled')->default(true);

            // Per-event email toggles
            $table->boolean('email_case_status_change')->default(true);
            $table->boolean('email_assignment')->default(true);
            $table->boolean('email_follow_up_due')->default(true);
            $table->boolean('email_sla_breach')->default(true);
            $table->boolean('email_case_resolved')->default(true);

            // Per-event push toggles
            $table->boolean('push_case_status_change')->default(true);
            $table->boolean('push_assignment')->default(true);
            $table->boolean('push_follow_up_due')->default(true);
            $table->boolean('push_sla_breach')->default(true);
            $table->boolean('push_case_resolved')->default(true);

            // Quiet hours (null = no quiet hours)
            $table->time('quiet_hours_start')->nullable();
            $table->time('quiet_hours_end')->nullable();

            $table->timestamps();

            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
