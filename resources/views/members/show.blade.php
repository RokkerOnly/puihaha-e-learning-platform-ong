@extends('layouts.members')

@section('title', 'Member Profile')
@section('subtitle', 'View membership and contact details.')
@section('container-class', 'narrow')

@section('content')
<dl class="profile">
    <div>
        <dt>Membership No</dt>
        <dd>{{ $member->membership_no }}</dd>
    </div>

    <div>
        <dt>First Name</dt>
        <dd>{{ $member->first_name }}</dd>
    </div>

    <div>
        <dt>Last Name</dt>
        <dd>{{ $member->last_name }}</dd>
    </div>

    <div>
        <dt>Email</dt>
        <dd>{{ $member->email }}</dd>
    </div>

    <div>
        <dt>Category</dt>
        <dd>{{ $member->category?->name ?? '—' }}</dd>
    </div>

    <div>
        <dt>Level</dt>
        <dd>{{ $member->level?->name ?? '—' }}</dd>
    </div>

    <div>
        <dt>Status</dt>
        <dd>
            <span class="badge {{ $member->active ? 'badge-active' : 'badge-inactive' }}">
                {{ $member->active ? 'Active' : 'Inactive' }}
            </span>
        </dd>
    </div>

    <div>
        <dt>Membership Started</dt>
        <dd>
            {{ $member->membership_starts_at
            ?->timezone('Asia/Manila')
            ->format('M j, Y · g:i A') ?? 'No approved membership yet' }}
        </dd>
    </div>

    <div>
        <dt>Membership Expires</dt>
        <dd>
            {{ $member->membership_expires_at
            ?->timezone('Asia/Manila')
            ->format('M j, Y · g:i A') ?? 'No approved membership yet' }}
        </dd>
    </div>
</dl>

<div class="actions">
    <a class="btn" href="{{ route('members.edit', $member->id) }}">
        Edit Member
    </a>

    <a class="btn btn-secondary" href="{{ route('members.index') }}">
        Back to Members
    </a>

    <a class="btn btn-secondary" href="{{ route('transactions.index', ['member_id' => $member->id]) }}">
        Transaction History
    </a>

    <a href="{{ route('members.print', $member->id) }}" class="btn">
        Print Membership Record
    </a>
</div>
@endsection