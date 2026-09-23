<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_transitions', function (Blueprint $table) {
            $table->id();
            $table->string('from_status', 30);
            $table->string('to_status', 30);
            $table->json('allowed_roles');
            $table->boolean('requires_note')->default(false);
            $table->boolean('requires_documents')->default(false);
            $table->timestamps();

            $table->unique(['from_status', 'to_status']);
        });

        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->string('case_no', 30)->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories');
            $table->foreignId('sub_category_id')->nullable()->constrained('sub_categories')->nullOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('urgency', 20)->default('normal'); // low, normal, urgent, critical
            $table->string('priority', 20)->default('medium'); // low, medium, high, critical
            $table->string('status', 30)->default('received'); // received, verification, categorised, assigned, assistance, followUp, resolved, needMoreInfo, onHold, rejected, reopened
            $table->foreignId('village_id')->nullable()->constrained('villages')->nullOnDelete();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->foreignId('current_assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('sla_due_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->text('feedback')->nullable();
            $table->string('idempotency_key', 64)->nullable()->unique();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'created_at']);
            $table->index(['category_id', 'status']);
            $table->index('sla_due_at');
        });

        Schema::create('application_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->string('type', 50);
            $table->string('path');
            $table->string('status', 20)->default('pending'); // pending, verified, rejected
            $table->text('reject_reason')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('application_timeline_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->string('event_type', 50);
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30)->nullable();
            $table->string('title_key');
            $table->text('body')->nullable();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_role', 50)->nullable();
            $table->enum('visibility', ['public', 'internal'])->default('public');
            $table->json('meta')->nullable();
            $table->json('notification_status')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['application_id', 'visibility', 'created_at'], 'ate_app_visibility_created_idx');
        });

        Schema::create('application_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->foreignId('assignee_id')->constrained('users')->cascadeOnDelete();
            $table->string('assignee_type', 30); // staff, mentor, volunteer, partner
            $table->foreignId('assigned_by')->constrained('users');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->text('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('application_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users');
            $table->text('body');
            $table->json('attachments')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->timestamp('scheduled_for');
            $table->foreignId('assigned_to')->constrained('users');
            $table->string('status', 20)->default('pending'); // pending, completed, missed, cancelled
            $table->text('notes')->nullable();
            $table->timestamp('done_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follow_ups');
        Schema::dropIfExists('application_messages');
        Schema::dropIfExists('application_assignments');
        Schema::dropIfExists('application_timeline_events');
        Schema::dropIfExists('application_documents');
        Schema::dropIfExists('applications');
        Schema::dropIfExists('workflow_transitions');
    }
};
