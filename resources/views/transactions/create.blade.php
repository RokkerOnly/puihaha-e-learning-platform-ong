@extends('layouts.members')

@section('title', 'New Membership Transaction')
@section('subtitle', 'Create an application or renewal for a registered member.')
@section('container-class', 'narrow')

@section('content')
<form action="{{ route('transactions.store') }}" method="POST">
    @csrf

    <div class="field">
        <label for="member_id">Member</label>

        <select id="member_id" name="member_id" required>
            <option value="">Select a member</option>

            @foreach ($members as $member)
            <option value="{{ $member->id }}" @selected( old('member_id', request('member_id'))==$member->id
                )>
                {{ $member->last_name }},
                {{ $member->first_name }}
                — {{ $member->membership_no }}
            </option>
            @endforeach
        </select>
    </div>

    <div class="field">
        <label for="type">Transaction Type</label>

        <select id="type" name="type" required>
            <option value="">Select a transaction type</option>

            <option value="application" @selected(old('type')==='application' )>
                New Membership Application
            </option>

            <option value="renewal" @selected(old('type')==='renewal' )>
                Membership Renewal
            </option>
        </select>

        <p class="transaction-help">
            Choose Application for the member’s first membership.
            Renewal requires a previously approved membership.
        </p>
    </div>

    <div class="field">
        <label for="level_id">Membership Level</label>

        <select id="level_id" name="level_id" required>
            <option value="">Select a membership level</option>

            @foreach ($levels as $level)
            <option value="{{ $level->id }}" @selected(old('level_id')==$level->id)>
                {{ $level->name }}
                — PHP {{ number_format($level->fee, 2) }}
                — {{ $level->validity_months }}
                {{ $level->validity_months == 1 ? 'month' : 'months' }}
            </option>
            @endforeach
        </select>

        <p class="transaction-help">
            The fee, validity, and benefits are copied into this
            transaction when you save.
        </p>
    </div>

    <div class="transaction-notice">
        Creating a transaction does not approve or renew the membership.
        Payment and approval must be completed afterward.
    </div>

    @if ($members->isEmpty() || $levels->isEmpty())
    <div class="alert" role="alert">
        Register at least one member and create a membership level
        before creating a transaction.
    </div>
    @endif

    <div class="actions">
        <button class="btn" type="submit" @disabled($members->isEmpty() || $levels->isEmpty())>
            Create Transaction
        </button>

        <a class="btn btn-secondary" href="{{ route('transactions.index') }}">
            Cancel
        </a>
    </div>
</form>
@endsection