@extends('layouts.members')

@section('title', 'Application Details')
@section('subtitle', 'Review the applicant’s submitted information.')
@section('container-class', 'narrow')

@section('content')
<div class="toolbar">
    <span class="transaction-reference">
        {{ $application->reference_no }}
    </span>

    <span class="badge status-{{ $application->status }}">
        {{ ucfirst($application->status) }}
    </span>
</div>

<dl class="profile">
    <div>
        <dt>Applicant</dt>
        <dd>{{ $application->full_name }}</dd>
    </div>

    <div>
        <dt>Email</dt>
        <dd>{{ $application->email }}</dd>
    </div>

    <div>
        <dt>Phone</dt>
        <dd>{{ $application->phone ?: '—' }}</dd>
    </div>

    <div>
        <dt>Address</dt>
        <dd>{{ $application->address ?: '—' }}</dd>
    </div>

    <div>
        <dt>Category</dt>
        <dd>{{ $application->category_name }}</dd>
    </div>

    <div>
        <dt>Level</dt>
        <dd>{{ $application->level_name }}</dd>
    </div>

    <div>
        <dt>Amount due</dt>
        <dd>PHP {{ number_format($application->amount_due, 2) }}</dd>
    </div>

    <div>
        <dt>Validity</dt>
        <dd>{{ $application->validity_months }} months</dd>
    </div>

    <div>
        <dt>Benefits</dt>
        <dd class="transaction-benefits">{{ $application->benefits ?: '—' }}</dd>
    </div>

    <div>
        <dt>Submitted</dt>
        <dd>
            {{ $application->created_at->timezone('Asia/Manila')->format('M j, Y · g:i A') }}
        </dd>
    </div>

    <div>
        <dt>Latitude</dt>
        <dd>{{ $application->latitude ?? 'Not provided' }}</dd>
    </div>

    <div>
        <dt>Longitude</dt>
        <dd>{{ $application->longitude ?? 'Not provided' }}</dd>
    </div>
</dl>

@if ($application->membership_transaction_id)
<div class="transaction-notice">
    This application has been processed.
    Membership activation follows payment and approval.
</div>

<div class="actions">
    <a href="{{ route('members.show', $application->member_id) }}" class="btn btn-secondary">
        View member
    </a>

    <a href="{{ route(
                'transactions.show',
                $application->membership_transaction_id
            ) }}" class="btn">
        View transaction
    </a>
</div>
@elseif ($application->status === 'pending')
<form action="{{ route('applications.process', $application) }}" method="POST">
    @csrf

    <h2>Create member and transaction</h2>

    <p class="transaction-help">
        Confirm the applicant’s first and last names.
        Their submitted name is {{ $application->full_name }}.
    </p>

    <div class="field">
        <label for="first_name">First name</label>

        <input id="first_name" name="first_name" value="{{ old('first_name') }}" maxlength="255" required>
    </div>

    <div class="field">
        <label for="last_name">Last name</label>

        <input id="last_name" name="last_name" value="{{ old('last_name') }}" maxlength="255" required>
    </div>

    <p class="transaction-notice">
        This creates an inactive member and a Pending transaction
        using the fee and membership details recorded in this application.
    </p>

    <div class="actions">
        <button type="submit" class="btn">
            Create member and transaction
        </button>
    </div>
</form>
@endif

<div class="actions">
    <a href="{{ route('applications.index') }}" class="btn btn-secondary">
        Back to applications
    </a>
</div>
@endsection