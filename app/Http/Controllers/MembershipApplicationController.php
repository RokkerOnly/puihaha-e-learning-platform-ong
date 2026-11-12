<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMembershipApplicationRequest;
use App\Models\Category;
use App\Models\Level;
use App\Models\MembershipApplication;
use App\Notifications\MembershipTransactionSubmitted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\Member;
use App\Models\MembershipTransaction;
use Illuminate\Http\Request;

class MembershipApplicationController extends Controller
{
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $levels = Level::orderBy('fee')->get();

        return view('membership.create', compact(
            'categories',
            'levels'
        ));
    }

    public function store(StoreMembershipApplicationRequest $request)
    {
        $validated = $request->validated();

        $application = DB::transaction(function () use ($validated) {
            $category = Category::lockForUpdate()
                ->findOrFail($validated['category_id']);

            $level = Level::lockForUpdate()
                ->findOrFail($validated['level_id']);

            if ($level->fee < 0 || $level->validity_months < 1) {
                throw ValidationException::withMessages([
                    'level_id' => 'This membership level is currently unavailable.',
                ]);
            }

            $application = MembershipApplication::create([
                'category_id' => $category->id,
                'level_id' => $level->id,
                'reference_no' => 'APP-' . Str::uuid(),
                'full_name' => trim($validated['full_name']),
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'category_name' => $category->name,
                'level_name' => $level->name,
                'amount_due' => $level->fee,
                'validity_months' => $level->validity_months,
                'benefits' => $level->benefits,
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'status' => 'pending',
            ]);

            Notification::route('mail', $application->email)
                ->notify(new MembershipTransactionSubmitted(
                    memberName: $application->full_name,
                    referenceNo: $application->reference_no,
                    transactionType: 'application',
                    levelName: $application->level_name,
                    amountDue: (string) $application->amount_due,
                ));

            return $application;
        }, 3);

        return redirect()
            ->route('membership.create')
            ->with(
                'success',
                "Application received. Your reference number is {$application->reference_no}. An acknowledgment email has been queued."
            );
    }

    public function index()
    {
        $applications = MembershipApplication::orderByDesc('id')
            ->paginate(10);

        return view('membership.index', compact('applications'));
    }

    public function show(MembershipApplication $application)
    {
        return view('membership.show', compact('application'));
    }

    public function process(
    Request $request,
    MembershipApplication $application
    ) {
    $validated = $request->validate([
        'first_name' => ['required', 'string', 'max:255'],
        'last_name' => ['required', 'string', 'max:255'],
    ]);

    $transaction = DB::transaction(function () use (
        $request,
        $application,
        $validated
    ) {
        $application = MembershipApplication::query()
            ->lockForUpdate()
            ->findOrFail($application->id);

        // Repeated submissions open the existing transaction.
        if ($application->membership_transaction_id !== null) {
            return MembershipTransaction::findOrFail(
                $application->membership_transaction_id
            );
        }

        if ($application->status !== 'pending') {
            throw ValidationException::withMessages([
                'application' => 'Only pending applications can be processed.',
            ]);
        }

        if (Member::where('email', $application->email)->exists()) {
            throw ValidationException::withMessages([
                'application' => (
                    'A member already uses this email address. '
                    . 'Open their member record and use the existing '
                    . 'transaction or renewal flow.'
                ),
            ]);
        }

        if (
            (float) $application->amount_due < 0
            || $application->validity_months < 1
        ) {
            throw ValidationException::withMessages([
                'application' => (
                    'This application has an invalid fee or validity period.'
                ),
            ]);
        }

        $member = Member::create([
            'membership_no' => 'MEM-' . Str::uuid(),
            'first_name' => trim($validated['first_name']),
            'last_name' => trim($validated['last_name']),
            'email' => $application->email,
            'category_id' => $application->category_id,
            'level_id' => $application->level_id,
            'active' => false,
        ]);

        $transaction = MembershipTransaction::create([
            'reference_no' => 'TXN-' . Str::uuid(),
            'member_id' => $member->id,
            'level_id' => $application->level_id,
            'type' => MembershipTransaction::TYPE_APPLICATION,
            'category_name' => $application->category_name,
            'level_name' => $application->level_name,
            'amount_due' => $application->amount_due,
            'validity_months' => $application->validity_months,
            'benefits' => $application->benefits,
            'status' => MembershipTransaction::STATUS_PENDING,
            'created_by' => $request->user()->id,
        ]);

        $application->member_id = $member->id;
        $application->membership_transaction_id = $transaction->id;
        $application->processed_by = $request->user()->id;
        $application->processed_at = now();
        $application->status = 'processed';
        $application->save();

        return $transaction;
    }, 3);

    return redirect()
        ->route('transactions.show', $transaction)
        ->with(
            'success',
            'Application processed. The member is inactive until payment and approval are completed.'
        );
    }
}