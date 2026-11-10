@extends('layouts.members')

@section('title', $historyMember ? 'Member Transaction History' : 'Transactions')

@section('subtitle', $historyMember
? $historyMember->first_name . ' ' . $historyMember->last_name . ' · ' . $historyMember->membership_no
: 'Membership applications, renewals, and their current status.')

@section('content')
<div class="toolbar">
    <div>
        <h2 class="section-title">
            {{ $historyMember ? 'Member history' : 'Transaction register' }}
        </h2>

        @if ($historyMember)
        <a class="transaction-back" href="{{ route('members.show', $historyMember->id) }}">
            ← Back to member
        </a>
        @endif
    </div>

    <a class="btn" href="{{ route('transactions.create', $historyMember
                ? ['member_id' => $historyMember->id]
                : []) }}">
        New transaction
    </a>
</div>

<form class="transaction-filters" action="{{ route('transactions.index') }}" method="GET">

    @if ($historyMember)
    <input type="hidden" name="member_id" value="{{ $historyMember->id }}">
    @endif

    <div class="transaction-filter-search">
        <label for="search">Search transactions</label>

        <input id="search" type="search" name="search" value="{{ request('search') }}" maxlength="255"
            placeholder="Reference, first name, last name, or membership no.">
    </div>

    <div class="transaction-filter-status">
        <label for="status">Status</label>

        <select id="status" name="status">
            <option value="">All statuses</option>

            @foreach ($statuses as $status)
            <option value="{{ $status }}" @selected(request('status')===$status)>
                {{ ucfirst($status) }}
            </option>
            @endforeach
        </select>
    </div>

    <button class="btn" type="submit">Filter</button>

    <a class="btn btn-secondary" href="{{ route('transactions.index', $historyMember
                ? ['member_id' => $historyMember->id]
                : []) }}">
        Reset
    </a>
</form>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Reference</th>
                <th>Member</th>
                <th>Type</th>
                <th>Level</th>
                <th>Amount Due</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($transactions as $transaction)
            <tr>
                <td>
                    <span class="transaction-reference">
                        {{ $transaction->reference_no }}
                    </span>
                </td>

                <td>
                    {{ $transaction->member->first_name }}
                    {{ $transaction->member->last_name }}
                </td>

                <td>{{ ucfirst($transaction->type) }}</td>

                <td>{{ $transaction->level_name }}</td>

                <td>
                    PHP {{ number_format($transaction->amount_due, 2) }}
                </td>

                <td>
                    <span class="badge status-{{ $transaction->status }}">
                        {{ ucfirst($transaction->status) }}
                    </span>
                </td>

                <td>
                    {{ $transaction->created_at
                                ->timezone('Asia/Manila')
                                ->format('M j, Y') }}
                </td>

                <td>
                    <a class="btn btn-secondary btn-small" href="{{ route('transactions.show', $transaction) }}">
                        View
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="empty">
                    No transactions found.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($transactions->hasPages())
<nav class="transaction-pagination" aria-label="Transaction pagination">

    <span>
        Page {{ $transactions->currentPage() }}
        of {{ $transactions->lastPage() }}
    </span>

    <div class="actions">
        @if (! $transactions->onFirstPage())
        <a class="btn btn-secondary btn-small" href="{{ $transactions->previousPageUrl() }}">
            Previous
        </a>
        @endif

        @if ($transactions->hasMorePages())
        <a class="btn btn-secondary btn-small" href="{{ $transactions->nextPageUrl() }}">
            Next
        </a>
        @endif
    </div>
</nav>
@endif
@endsection