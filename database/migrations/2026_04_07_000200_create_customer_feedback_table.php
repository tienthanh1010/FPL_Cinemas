<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('movie_id')->nullable()->constrained('movies')->nullOnDelete();
            $table->foreignId('show_id')->nullable()->constrained('shows')->nullOnDelete();
            $table->string('reviewer_name')->nullable();
            $table->string('reviewer_email')->nullable();
            $table->unsignedTinyInteger('movie_rating')->nullable();
            $table->text('movie_comment')->nullable();
            $table->unsignedTinyInteger('food_rating')->nullable();
            $table->text('food_comment')->nullable();
            $table->unsignedTinyInteger('facility_rating')->nullable();
            $table->text('facility_comment')->nullable();
            $table->unsignedTinyInteger('staff_rating')->nullable();
            $table->text('staff_comment')->nullable();
            $table->text('overall_comment')->nullable();
            $table->string('status', 16)->default('PUBLISHED');
            $table->timestamps();

            $table->unique('booking_id');
            $table->index(['movie_id', 'status']);
            $table->index(['show_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_feedback');
    }
};
