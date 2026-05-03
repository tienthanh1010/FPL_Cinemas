<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('content_posts')) {
            return;
        }

        Schema::create('content_posts', function (Blueprint $table) {
            $table->id();
            $table->string('type', 16)->default('NEWS');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();
            $table->string('cover_image_url', 512)->nullable();
            $table->string('badge_label', 100)->nullable();
            $table->string('status', 20)->default('DRAFT');
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->index(['type', 'status']);
            $table->index(['is_featured', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_posts');
    }
};
