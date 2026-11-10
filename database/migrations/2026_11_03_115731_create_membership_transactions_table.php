<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_transactions', function (Blueprint $table) {
            $table->id();

            $table->string('reference_no')->unique();

            $table->foreignId('member_id')
                ->constrained('members')
                ->restrictOnDelete();

            $table->foreignId('level_id')
                ->constrained('levels')
                ->restrictOnDelete();

            $table->enum('type', [
                'application',
                'renewal',
            ]);

            // Membership terms recorded when this transaction is created.
            $table->string('category_name');
            $table->string('level_name');
            $table->decimal('amount_due', 10, 2);
            $table->unsignedInteger('validity_months');
            $table->text('benefits')->nullable();

            $table->enum('status', [
                'pending',
                'paid',
                'approved',
                'expired',
                'cancelled',
            ])->default('pending');

            // Assigned when a paid transaction is approved.
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('expires_at')->nullable();

            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamp('approved_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();

            $table->timestamps();

            $table->index(['member_id', 'status']);
            $table->index(['status', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_transactions');
    }
};