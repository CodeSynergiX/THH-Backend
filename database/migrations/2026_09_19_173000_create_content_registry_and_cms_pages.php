<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_modules', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title_en');
            $table->string('title_gu');
            $table->text('description_en')->nullable();
            $table->text('description_gu')->nullable();
            $table->string('icon')->default('layers');
            $table->string('accent_color', 20)->default('#B45309');
            $table->boolean('is_enabled')->default(true);
            $table->boolean('is_public')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('show_apply_form')->default(true);
            $table->string('type', 30)->default('cms'); // cms | operational
            $table->timestamps();
        });

        Schema::create('content_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('content_modules')->cascadeOnDelete();
            $table->string('slug');
            $table->string('title_en');
            $table->string('title_gu');
            $table->text('excerpt_en')->nullable();
            $table->text('excerpt_gu')->nullable();
            $table->longText('body_en')->nullable();
            $table->longText('body_gu')->nullable();
            $table->string('cover_image')->nullable();
            $table->json('meta')->nullable();
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['module_id', 'slug']);
            $table->index(['module_id', 'is_published', 'sort_order']);
        });

        Schema::table('static_pages', function (Blueprint $table) {
            $table->string('title_en')->nullable()->after('slug');
            $table->string('title_gu')->nullable()->after('title_en');
            $table->longText('body_en')->nullable()->after('title_gu');
            $table->longText('body_gu')->nullable()->after('body_en');
        });
    }

    public function down(): void
    {
        Schema::table('static_pages', function (Blueprint $table) {
            $table->dropColumn(['title_en', 'title_gu', 'body_en', 'body_gu']);
        });
        Schema::dropIfExists('content_items');
        Schema::dropIfExists('content_modules');
    }
};
