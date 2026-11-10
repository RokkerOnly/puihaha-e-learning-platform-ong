<?php

namespace App\Console\Commands;

use App\Models\Member;
use App\Models\MembershipTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExpireMemberships extends Command
{
    protected $signature = 'memberships:expire';

    protected $description =
        'Expire completed membership periods and deactivate expired memberships';

    public function handle(): int
    {
        $result = DB::transaction(function () {
            $now = now();

            // A renewed member remains active if their current expiry is later.
            $members = Member::where('active', true)
                ->whereNotNull('membership_expires_at')
                ->where('membership_expires_at', '<=', $now)
                ->update([
                    'active' => false,
                ]);

            // Historical transaction periods expire independently.
            $transactions = MembershipTransaction::where(
                'status',
                MembershipTransaction::STATUS_APPROVED
            )
                ->whereNotNull('expires_at')
                ->where('expires_at', '<=', $now)
                ->update([
                    'status' => MembershipTransaction::STATUS_EXPIRED,
                ]);

            return compact('members', 'transactions');
        }, 3);

        $this->info(
            "Expired transaction periods: {$result['transactions']}"
        );

        $this->info(
            "Deactivated expired memberships: {$result['members']}"
        );

        return self::SUCCESS;
    }
}