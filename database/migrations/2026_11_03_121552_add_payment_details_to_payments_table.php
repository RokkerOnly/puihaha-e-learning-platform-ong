<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Existing incomplete rows would need to be reconciled first.
        if (DB::table('payments')->exists()) {
            throw new RuntimeException(
                'The payments table contains records. Stop here and report this message before continuing.'
            );
        }

        Schema::table('payments', function (Blueprint $table) {
            $table->string('receipt_no')->unique();

            $table->foreignId('membership_transaction_id')
                ->constrained('membership_transactions')
                ->restrictOnDelete();

            $table->decimal('amount', 10, 2);

            $table->enum('method', [
                'cash',
                'bank_transfer',
                'e_wallet',
            ]);

            $table->string('external_reference')->nullable();

            $table->dateTime('paid_at');

            $table->foreignId('recorded_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->text('notes')->nullable();

            $table->index('paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('membership_transaction_id');
            $table->dropConstrainedForeignId('recorded_by');

            $table->dropUnique(['receipt_no']);
            $table->dropIndex(['paid_at']);

            $table->dropColumn([
                'receipt_no',
                'amount',
                'method',
                'external_reference',
                'paid_at',
                'notes',
            ]);
        });
    }
};