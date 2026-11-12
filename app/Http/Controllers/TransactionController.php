<?php

namespace App\Http\Controllers;

use App\Support\Money;
use App\Models\Level;
use App\Models\Member;
use App\Models\MembershipTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Notifications\MembershipTransactionSubmitted;
use Illuminate\Support\Facades\Notification;

class TransactionController extends Controller
{
    private const STATUSES = [
        'pending',
        'paid',
        'approved',
        'expired',
        'cancelled',
    ];

    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(self::STATUSES)],
            'member_id' => ['nullable', 'integer', 'exists:members,id'],
        ]);

        $search = trim($filters['search'] ?? '');
        $status = $filters['status'] ?? null;

        $historyMember = ! empty($filters['member_id'])
            ? Member::findOrFail($filters['member_id'])
            : null;

        $transactions = MembershipTransaction::with('member')
            ->when($historyMember, function ($query) use ($historyMember) {
                $query->where('member_id', $historyMember->id);
            })
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($matches) use ($search) {
                    $matches->where(
                        'reference_no',
                        'like',
                        "%{$search}%"
                    )->orWhereHas('member', function ($member) use ($search) {
                        $member->where(function ($name) use ($search) {
                            $name->where(
                                'first_name',
                                'like',
                                "%{$search}%"
                            )->orWhere(
                                'last_name',
                                'like',
                                "%{$search}%"
                            )->orWhere(
                                'membership_no',
                                'like',
                                "%{$search}%"
                            );
                        });
                    });
                });
            })
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $statuses = self::STATUSES;

        return view('transactions.index', compact(
            'transactions',
            'statuses',
            'historyMember'
        ));
    }

    public function create()
    {
        $members = Member::with(['category', 'level'])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $levels = Level::orderBy('fee')->get();

        return view('transactions.create', compact('members', 'levels'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'member_id' => ['required', 'integer', 'exists:members,id'],
            'level_id' => ['required', 'integer', 'exists:levels,id'],
            'type' => [
                'required',
                Rule::in([
                    MembershipTransaction::TYPE_APPLICATION,
                    MembershipTransaction::TYPE_RENEWAL,
                ]),
            ],
        ]);

        $transaction = DB::transaction(function () use ($validated, $request) {
            // Serialize transaction requests for the same member.
            $member = Member::lockForUpdate()
                ->findOrFail($validated['member_id']);

            $level = Level::lockForUpdate()
                ->findOrFail($validated['level_id']);

            $category = $member->category;

            if (! $category) {
                throw ValidationException::withMessages([
                    'member_id' => 'Assign a category to this member first.',
                ]);
            }

            $hasOpenTransaction = $member->transactions()
                ->whereIn('status', [
                    MembershipTransaction::STATUS_PENDING,
                    MembershipTransaction::STATUS_PAID,
                ])
                ->exists();

            if ($hasOpenTransaction) {
                throw ValidationException::withMessages([
                    'member_id' => 'This member already has a pending or paid transaction. Complete or resolve it first.',
                ]);
            }

            $hasApprovedMembership = $member->transactions()
                ->whereNotNull('approved_at')
                ->exists();

            if (
                $validated['type'] === MembershipTransaction::TYPE_APPLICATION
                && $hasApprovedMembership
            ) {
                throw ValidationException::withMessages([
                    'type' => 'This member already has an approved membership. Choose Renewal.',
                ]);
            }

            if (
                $validated['type'] === MembershipTransaction::TYPE_RENEWAL
                && ! $hasApprovedMembership
            ) {
                throw ValidationException::withMessages([
                    'type' => 'A renewal requires a previously approved membership. Create an application first.',
                ]);
            }

            if ($level->validity_months < 1 || $level->fee < 0) {
                throw ValidationException::withMessages([
                    'level_id' => 'This level has an invalid fee or validity period. Correct it first.',
                ]);
            }

            $newTransaction = MembershipTransaction::create([
                'reference_no' => 'TXN-' . Str::uuid(),
                'member_id' => $member->id,
                'level_id' => $level->id,
                'type' => $validated['type'],
                'category_name' => $category->name,
                'level_name' => $level->name,
                'amount_due' => $level->fee,
                'validity_months' => $level->validity_months,
                'benefits' => $level->benefits,
                'status' => MembershipTransaction::STATUS_PENDING,
                'created_by' => $request->user()->id,
            ]);

            Notification::route('mail', $member->email)
                ->notify(new MembershipTransactionSubmitted(
                    memberName: trim(
                        $member->first_name . ' ' . $member->last_name
                    ),
                    referenceNo: $newTransaction->reference_no,
                    transactionType: $newTransaction->type,
                    levelName: $newTransaction->level_name,
                    amountDue: (string) $newTransaction->amount_due,
                ));

            return $newTransaction;
        }, 3);

        return redirect()
            ->route('transactions.show', $transaction)
            ->with('success', 'Transaction created successfully.');
    }

    public function show(MembershipTransaction $transaction)
    {
        $transaction->load([
            'member',
            'createdBy',
            'approvedBy',
            'payments.recordedBy',
        ]);

        $transaction->loadSum('payments', 'amount');

        return view('transactions.show', compact('transaction'));
    }

    public function cancel(
        Request $request,
        MembershipTransaction $transaction
    ) {
        $validated = $request->validate([
            'cancellation_reason' => [
                'required',
                'string',
                'max:1000',
            ],
        ]);

        DB::transaction(function () use ($transaction, $validated) {
            $lockedTransaction = MembershipTransaction::lockForUpdate()
                ->findOrFail($transaction->id);

            if (
                $lockedTransaction->status
                !== MembershipTransaction::STATUS_PENDING
            ) {
                throw ValidationException::withMessages([
                    'status' => 'Only pending transactions can be cancelled here.',
                ]);
            }

            if ($lockedTransaction->payments()->exists()) {
                throw ValidationException::withMessages([
                    'status' => 'A transaction with recorded payments cannot be cancelled here.',
                ]);
            }

            $lockedTransaction->status =
                MembershipTransaction::STATUS_CANCELLED;

            $lockedTransaction->cancelled_at = now();

            $lockedTransaction->cancellation_reason =
                $validated['cancellation_reason'];

            $lockedTransaction->save();
        }, 3);

        return redirect()
            ->route('transactions.show', $transaction)
            ->with('success', 'Transaction cancelled successfully.');
    }

    public function approve(
        Request $request,
        MembershipTransaction $transaction
    ) {
        DB::transaction(function () use ($request, $transaction) {
            // Lock the member first, matching transaction creation.
            $member = Member::lockForUpdate()
                ->findOrFail($transaction->member_id);

            $lockedTransaction = MembershipTransaction::lockForUpdate()
                ->findOrFail($transaction->id);

            if ($lockedTransaction->approved_at !== null) {
                throw ValidationException::withMessages([
                    'approval' => 'This transaction has already been approved.',
                ]);
            }

            if (! in_array($lockedTransaction->status, [
                MembershipTransaction::STATUS_PENDING,
                MembershipTransaction::STATUS_PAID,
            ], true)) {
                throw ValidationException::withMessages([
                    'approval' => 'This transaction cannot be approved in its current status.',
                ]);
            }

            $amountDue = Money::toCents(
                (string) $lockedTransaction->amount_due
            );

            $amountPaid = Money::toCents(
                (string) $lockedTransaction->payments()->sum('amount')
            );

            if ($amountPaid !== $amountDue) {
                throw ValidationException::withMessages([
                    'approval' => 'Recorded payments must exactly cover the amount due before approval.',
                ]);
            }

            // A zero-fee transaction has a settled balance without a payment.
            if (
                $amountDue === 0
                && $lockedTransaction->status
                === MembershipTransaction::STATUS_PENDING
            ) {
                $lockedTransaction->status =
                    MembershipTransaction::STATUS_PAID;

                $lockedTransaction->save();
            }

            if (
                $lockedTransaction->status
                !== MembershipTransaction::STATUS_PAID
            ) {
                throw ValidationException::withMessages([
                    'approval' => 'Only paid transactions can be approved.',
                ]);
            }

            if ($lockedTransaction->validity_months < 1) {
                throw ValidationException::withMessages([
                    'approval' => 'The transaction has an invalid validity period.',
                ]);
            }

            $hasApprovedMembership = $member->transactions()
                ->where('id', '!=', $lockedTransaction->id)
                ->whereNotNull('approved_at')
                ->exists();

            $isRenewal = $lockedTransaction->type
                === MembershipTransaction::TYPE_RENEWAL;

            if ($isRenewal && ! $hasApprovedMembership) {
                throw ValidationException::withMessages([
                    'approval' => 'A renewal requires a previously approved membership.',
                ]);
            }

            if (! $isRenewal && $hasApprovedMembership) {
                throw ValidationException::withMessages([
                    'approval' => 'This member already has an approved membership. Use a renewal.',
                ]);
            }

            if ($isRenewal && ! $member->membership_expires_at) {
                throw ValidationException::withMessages([
                    'approval' => 'The previous membership expiry date is missing.',
                ]);
            }

            $approvedAt = now();

            $hasRemainingTime = $isRenewal
                && $member->membership_expires_at->gt($approvedAt);

            // Calculate calendar months using Philippine local time.
            $expiryBase = $hasRemainingTime
                ? $member->membership_expires_at
                ->copy()
                ->timezone('Asia/Manila')
                : $approvedAt->copy()->timezone('Asia/Manila');

            $expiresAt = $expiryBase
                ->copy()
                ->addMonthsNoOverflow($lockedTransaction->validity_months)
                ->timezone(config('app.timezone', 'UTC'));

            $lockedTransaction->status =
                MembershipTransaction::STATUS_APPROVED;

            $lockedTransaction->approved_by = $request->user()->id;
            $lockedTransaction->approved_at = $approvedAt;
            $lockedTransaction->starts_at = $approvedAt;
            $lockedTransaction->expires_at = $expiresAt;
            $lockedTransaction->save();

            // Keep the original start date for continuous membership.
            if (! $hasRemainingTime || ! $member->membership_starts_at) {
                $member->membership_starts_at = $approvedAt;
            }

            $member->membership_expires_at = $expiresAt;
            $member->level_id = $lockedTransaction->level_id;
            $member->active = true;
            $member->save();
        }, 3);

        return redirect()
            ->route('transactions.show', $transaction)
            ->with('success', 'Membership transaction approved successfully.');
    }
}