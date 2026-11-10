<?php

namespace App\Http\Controllers;

use App\Models\MembershipTransaction;
use App\Models\Payment;
use App\Support\Money;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with([
            'transaction.member',
            'recordedBy',
        ])
            ->orderByDesc('id')
            ->paginate(10);

        return view('payments.index', compact('payments'));
    }

    public function create(
        Request $request,
        MembershipTransaction $transaction
    ) {
        $transaction->load('member');

        if ($transaction->status !== MembershipTransaction::STATUS_PENDING) {
            return redirect()
                ->route('transactions.show', $transaction)
                ->withErrors([
                    'payment' => 'Payments can only be recorded for pending transactions.',
                ]);
        }

        $amountPaid = Money::toCents(
            (string) $transaction->payments()->sum('amount')
        );

        $amountDue = Money::toCents(
            (string) $transaction->amount_due
        );

        $remaining = $amountDue - $amountPaid;

        if ($remaining <= 0) {
            return redirect()
                ->route('transactions.show', $transaction)
                ->withErrors([
                    'payment' => 'This transaction has no outstanding balance.',
                ]);
        }

        $balance = Money::fromCents($remaining);

        // Prevent the same payment form from creating duplicate payments.
        $formToken = (string) Str::uuid();

        $request->session()->put(
            'payment_forms.' . $formToken,
            $transaction->id
        );

        return view('payments.create', compact(
            'transaction',
            'balance',
            'formToken'
        ));
    }

    public function store(
        Request $request,
        MembershipTransaction $transaction
    ) {
        $validated = $request->validate([
            'form_token' => ['required', 'uuid'],
            'amount' => [
                'required',
                'numeric',
                'regex:/^\d+(?:\.\d{1,2})?$/D',
                'min:0.01',
                'max:99999999.99',
            ],
            'method' => [
                'required',
                Rule::in(['cash', 'bank_transfer', 'e_wallet']),
            ],
            'external_reference' => [
                'nullable',
                'required_unless:method,cash',
                'string',
                'max:255',
            ],
            'paid_at' => [
                'required',
                'date_format:Y-m-d\TH:i',
            ],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $sessionKey = 'payment_forms.' . $validated['form_token'];

        if (
            (int) $request->session()->get($sessionKey)
            !== (int) $transaction->id
        ) {
            throw ValidationException::withMessages([
                'payment' => 'This payment form has expired or was already submitted. Reopen Record Payment from the transaction.',
            ]);
        }

        $paidAt = CarbonImmutable::createFromFormat(
            'Y-m-d\TH:i',
            $validated['paid_at'],
            'Asia/Manila'
        )->startOfMinute();

        if ($paidAt->isFuture()) {
            throw ValidationException::withMessages([
                'paid_at' => 'The payment date cannot be in the future.',
            ]);
        }

        $receiptNo = 'PAY-' . $validated['form_token'];

        $payment = DB::transaction(function () use (
            $transaction,
            $validated,
            $paidAt,
            $receiptNo,
            $request
        ) {
            $lockedTransaction = MembershipTransaction::lockForUpdate()
                ->findOrFail($transaction->id);

            // A repeated submission of this form must not insert twice.
            $existingPayment = Payment::where('receipt_no', $receiptNo)
                ->first();

            if ($existingPayment) {
                if (
                    (int) $existingPayment->membership_transaction_id
                    !== (int) $lockedTransaction->id
                ) {
                    throw ValidationException::withMessages([
                        'payment' => 'This payment form belongs to another transaction.',
                    ]);
                }

                return $existingPayment;
            }

            if (
                $lockedTransaction->status
                !== MembershipTransaction::STATUS_PENDING
            ) {
                throw ValidationException::withMessages([
                    'payment' => 'This transaction is no longer accepting payments.',
                ]);
            }

            $amountDue = Money::toCents(
                (string) $lockedTransaction->amount_due
            );

            $amountPaid = Money::toCents(
                (string) $lockedTransaction->payments()->sum('amount')
            );

            $amount = Money::toCents((string) $validated['amount']);

            $remaining = $amountDue - $amountPaid;

            if ($remaining <= 0 || $amount > $remaining) {
                throw ValidationException::withMessages([
                    'amount' => 'The payment must not exceed the outstanding balance.',
                ]);
            }

            $payment = Payment::create([
                'receipt_no' => $receiptNo,
                'membership_transaction_id' => $lockedTransaction->id,
                'amount' => Money::fromCents($amount),
                'method' => $validated['method'],
                'external_reference' =>
                    $validated['external_reference'] ?? null,
                'paid_at' => $paidAt->setTimezone(
                    config('app.timezone', 'UTC')
                ),
                'recorded_by' => $request->user()->id,
                'notes' => $validated['notes'] ?? null,
            ]);

            if ($amountPaid + $amount === $amountDue) {
                $lockedTransaction->status =
                    MembershipTransaction::STATUS_PAID;

                $lockedTransaction->save();
            }

            return $payment;
        }, 3);

        $request->session()->forget($sessionKey);

        return redirect()
            ->route('transactions.show', $transaction)
            ->with(
                'success',
                'Payment recorded successfully. Receipt: ' . $payment->receipt_no
            );
    }

    public function receipt(Payment $payment)
    {
    $payment->load([
        'transaction.member',
        'recordedBy',
    ]);

    return view('payments.receipt', compact('payment'));
    }
}