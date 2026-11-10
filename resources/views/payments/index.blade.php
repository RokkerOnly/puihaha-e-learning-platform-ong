@extends('layouts.members')

@section('title', 'Payments')
@section('subtitle', 'Payments recorded against membership transactions.')

@section('content')
<div class="toolbar">
    <h2 class="section-title">Payment register</h2>

    <a class="btn" href="{{ route('transactions.index') }}">
        Find a Transaction to Record Payment
    </a>
</div>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Receipt No.</th>
                <th>Member</th>
                <th>Amount</th>
                <th>Method</th>
                <th>Payment Date</th>
                <th>Recorded By</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($payments as $payment)
            <tr>
                <td>
                    <a href="{{ route('payments.receipt', $payment) }}">
                        {{ $payment->receipt_no }}
                    </a>
                </td>

                <td>
                    {{ $payment->transaction->member->first_name }}
                    {{ $payment->transaction->member->last_name }}
                </td>

                <td>PHP {{ number_format($payment->amount, 2) }}</td>

                <td>
                    {{ ucwords(str_replace('_', ' ', $payment->method)) }}
                </td>

                <td>
                    {{ $payment->paid_at
                                ->timezone('Asia/Manila')
                                ->format('M j, Y · g:i A') }}
                </td>

                <td>{{ $payment->recordedBy?->name ?? '—' }}</td>

                <td>
                    <a class="btn btn-secondary btn-small" href="{{ route(
                                   'transactions.show',
                                   $payment->membership_transaction_id
                               ) }}">
                        Transaction
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="empty">
                    No payments recorded yet.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($payments->hasPages())
<nav class="transaction-pagination" aria-label="Payment pagination">
    <span>
        Page {{ $payments->currentPage() }}
        of {{ $payments->lastPage() }}
    </span>

    <div class="actions">
        @if (! $payments->onFirstPage())
        <a class="btn btn-secondary btn-small" href="{{ $payments->previousPageUrl() }}">
            Previous
        </a>
        @endif

        @if ($payments->hasMorePages())
        <a class="btn btn-secondary btn-small" href="{{ $payments->nextPageUrl() }}">
            Next
        </a>
        @endif
    </div>
</nav>
@endif
@endsection