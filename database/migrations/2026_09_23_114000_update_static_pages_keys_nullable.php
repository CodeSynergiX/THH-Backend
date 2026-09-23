<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('static_pages', function (Blueprint $table) {
            $table->longText('content_key')->nullable()->change();
            $table->string('title_key')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('static_pages', function (Blueprint $table) {
            $table->string('content_key')->nullable(false)->change();
            $table->string('title_key')->nullable(false)->change();
        });
    }
};
