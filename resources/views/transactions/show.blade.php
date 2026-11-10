@extends('layouts.members')

@section('title', 'Transaction Details')
@section('subtitle', 'Review the recorded membership terms and transaction status.')

@section('content')
<div class="toolbar">
    <span class="transaction-reference">
        {{ $transaction->reference_no }}
    </span>

    <span class="badge status-{{ $transaction->status }}">
        {{ ucfirst($transaction->status) }}
    </span>
</div>

@if (
$transaction->status === 'paid'
|| (
$transaction->status === 'pending'
&& $transaction->amount_due == 0
)
)
<div class="transaction-notice">
    @if ($transaction->amount_due == 0)
    <p>
        This membership has no fee.
        Approval will activate the membership without recording a payment.
    </p>
    @else
    <p>
        Payment is complete. Approve this transaction to activate
        or renew the membership.
    </p>
    @endif

    <form action="{{ route('transactions.approve', $transaction) }}" method="POST">
        @csrf
        @method('PUT')

        <button class="btn" type="submit">
            Approve Membership
        </button>
    </form>
</div>
@endif

<div class="transaction-details-grid">
    <section aria-labelledby="transaction-heading">
        <h2 class="section-title" id="transaction-heading">
            Transaction record
        </h2>

        <dl class="profile">
            <div>
                <dt>Member</dt>
                <dd>
                    <a href="{{ route('members.show', $transaction->member_id) }}">
                        {{ $transaction->member->first_name }}
                        {{ $transaction->member->last_name }}
                    </a>
                </dd>
            </div>

            <div>
                <dt>Membership No.</dt>
                <dd>{{ $transaction->member->membership_no }}</dd>
            </div>

            <div>
                <dt>Type</dt>
                <dd>{{ ucfirst($transaction->type) }}</dd>
            </div>

            <div>
                <dt>Created By</dt>
                <dd>{{ $transaction->createdBy?->name ?? '—' }}</dd>
            </div>

            <div>
                <dt>Created</dt>
                <dd>
                    {{ $transaction->created_at
                            ->timezone('Asia/Manila')
                            ->format('M j, Y · g:i A') }}
                </dd>
            </div>

            <div>
                <dt>Approved By</dt>
                <dd>{{ $transaction->approvedBy?->name ?? 'Not approved' }}</dd>
            </div>

            <div>
                <dt>Membership Starts</dt>
                <dd>
                    {{ $transaction->starts_at
                            ? $transaction->starts_at
                                ->timezone('Asia/Manila')
                                ->format('M j, Y · g:i A')
                            : 'Assigned on approval' }}
                </dd>
            </div>

            <div>
                <dt>Membership Expires</dt>
                <dd>
                    {{ $transaction->expires_at
                            ? $transaction->expires_at
                                ->timezone('Asia/Manila')
                                ->format('M j, Y · g:i A')
                            : 'Assigned on approval' }}
                </dd>
            </div>
        </dl>
    </section>

    <section aria-labelledby="terms-heading">
        <h2 class="section-title" id="terms-heading">
            Recorded membership terms
        </h2>

        <dl class="profile">
            <div>
                <dt>Category</dt>
                <dd>{{ $transaction->category_name }}</dd>
            </div>

            <div>
                <dt>Level</dt>
                <dd>{{ $transaction->level_name }}</dd>
            </div>

            <div>
                <dt>Amount Due</dt>
                <dd>
                    PHP {{ number_format($transaction->amount_due, 2) }}
                </dd>
            </div>

            <div>
                <dt>Payments Recorded</dt>
                <dd>
                    PHP {{ number_format(
                            $transaction->payments_sum_amount ?? 0,
                            2
                        ) }}
                </dd>
            </div>

            <div>
                <dt>Validity</dt>
                <dd>
                    {{ $transaction->validity_months }}
                    {{ $transaction->validity_months == 1 ? 'month' : 'months' }}
                </dd>
            </div>

            <div>
                <dt>Benefits</dt>
                <dd class="transaction-benefits">{{ $transaction->benefits ?: 'No benefits specified.' }}</dd>
            </div>
        </dl>
    </section>
</div>

<section class="transaction-section">
    <div class="toolbar">
        <h2 class="section-title">Payment history</h2>

        @if (
        $transaction->status === 'pending'
        && $transaction->amount_due > 0
        )
        <a class="btn" href="{{ route('transactions.payments.create', $transaction) }}">
            Record Payment
        </a>
        @endif
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Receipt No.</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Notes</th>
                    <th>Payment Date</th>
                    <th>Recorded By</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($transaction->payments as $payment)
                <tr>
                    <td>
                        <a href="{{ route('payments.receipt', $payment) }}">
                            {{ $payment->receipt_no }}
                        </a>
                    </td>

                    <td>
                        PHP {{ number_format($payment->amount, 2) }}
                    </td>

                    <td>
                        {{ ucwords(str_replace('_', ' ', $payment->method)) }}
                    </td>

                    <td class="payment-notes">{{ $payment->notes ?: '—' }}</td>

                    <td>
                        {{ $payment->paid_at
                                    ->timezone('Asia/Manila')
                                    ->format('M j, Y · g:i A') }}
                    </td>

                    <td>{{ $payment->recordedBy?->name ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="empty">
                        No payments recorded.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

@if (
$transaction->status === 'pending'
&& $transaction->payments->isEmpty()
)
<section class="transaction-section transaction-cancellation">
    <h2 class="section-title">Cancel this transaction</h2>

    <p class="transaction-help">
        Cancellation keeps the transaction in the history.
        It does not delete the record.
    </p>

    <form action="{{ route('transactions.cancel', $transaction) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="field">
            <label for="cancellation_reason">Reason for cancellation</label>

            <textarea id="cancellation_reason" name="cancellation_reason" rows="3" maxlength="1000"
                required>{{ old('cancellation_reason') }}</textarea>
        </div>

        <button class="btn btn-danger" type="submit">
            Cancel Transaction
        </button>
    </form>
</section>
@endif

@if ($transaction->status === 'cancelled')
<div class="transaction-notice">
    <strong>Cancellation reason:</strong>
    {{ $transaction->cancellation_reason }}

    <br>

    Cancelled:
    {{ $transaction->cancelled_at
                ?->timezone('Asia/Manila')
                ->format('M j, Y · g:i A') }}
</div>
@endif

<div class="actions transaction-section">
    <a class="btn btn-secondary" href="{{ route('transactions.index') }}">
        All Transactions
    </a>

    <a class="btn btn-secondary" href="{{ route('transactions.index', [
               'member_id' => $transaction->member_id,
           ]) }}">
        Member History
    </a>
</div>
@endsection