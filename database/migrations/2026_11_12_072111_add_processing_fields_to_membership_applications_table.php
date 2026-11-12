<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('membership_applications', function (Blueprint $table) {
            $table->foreignId('member_id')
                ->nullable()
                ->constrained('members')
                ->restrictOnDelete();

            $table->foreignId('membership_transaction_id')
                ->nullable()
                ->unique()
                ->constrained('membership_transactions')
                ->restrictOnDelete();

            $table->foreignId('processed_by')
                ->nullable()
                ->constrained('users')
                ->restrictOnDelete();

            $table->dateTime('processed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('membership_applications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('membership_transaction_id');
            $table->dropConstrainedForeignId('member_id');
            $table->dropConstrainedForeignId('processed_by');
            $table->dropColumn('processed_at');
        });
    }
};