<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_applications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')
                ->constrained('categories')
                ->restrictOnDelete();

            $table->foreignId('level_id')
                ->constrained('levels')
                ->restrictOnDelete();

            $table->string('reference_no')->unique();
            $table->string('full_name', 150);
            $table->string('email');
            $table->string('phone', 30)->nullable();
            $table->string('address')->nullable();

            $table->string('category_name');
            $table->string('level_name');
            $table->decimal('amount_due', 10, 2);
            $table->unsignedInteger('validity_months');
            $table->text('benefits')->nullable();

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_applications');
    }
};