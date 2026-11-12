@extends('layouts.members')

@section('title', 'Dashboard')
@section('subtitle', 'Your membership community at a glance.')
@section('panel-class', 'dashboard-panel')

@section('content')
<div class="toolbar">
    <h2 class="section-title">Membership applications</h2>

    <a href="{{ route('applications.index') }}" class="btn">
        Review applications
    </a>
</div>
<section class="dashboard-finances" aria-label="Transactions and payments">
    <a href="{{ route('transactions.index', ['status' => 'pending']) }}" class="dashboard-finance-item">
        <span class="dashboard-finance-label">Pending transactions</span>

        <strong>{{ number_format($pendingTransactions) }}</strong>

        <span class="dashboard-finance-description">
            Review transactions awaiting full payment. ↗
        </span>
    </a>

    <a href="{{ route('payments.index') }}" class="dashboard-finance-item dashboard-finance-payments">
        <span class="dashboard-finance-label">Collected payments</span>

        <strong>PHP {{ number_format($collectedPayments, 2) }}</strong>

        <span class="dashboard-finance-description">
            View all recorded membership payments. ↗
        </span>
    </a>
</section>
@php
$activeRate = $totalMembers > 0
? round(($activeMembers / $totalMembers) * 100)
: 0;
@endphp

<div class="membership-overview">
    <section class="community-card">
        <div class="community-top">
            <span class="dashboard-label">THE BIG PICTURE</span>
            <span class="community-symbol" aria-hidden="true">✦</span>
        </div>

        <div class="community-total">
            <strong>{{ number_format($totalMembers) }}</strong>
            <span>Total members</span>
        </div>

        <p class="community-description">
            Every membership, connected in one place.
        </p>

        <a class="community-link" href="{{ route('members.index') }}">
            Explore members <span aria-hidden="true">↗</span>
        </a>

        <div class="community-decoration" aria-hidden="true"></div>
    </section>

    <section class="activity-card">
        <div class="activity-heading">
            <h2>Membership pulse</h2>
            <span class="live-dot" aria-hidden="true"></span>
        </div>

        <p class="activity-percent">{{ $activeRate }}<span>%</span></p>
        <p class="activity-caption">of members are currently active</p>

        <progress class="membership-progress" value="{{ $activeMembers }}" max="{{ max($totalMembers, 1) }}"
            aria-label="Active members">
            {{ $activeRate }}%
        </progress>

        <div class="activity-breakdown">
            <div>
                <strong>{{ number_format($activeMembers) }}</strong>
                <span>Active</span>
            </div>

            <div>
                <strong>{{ number_format($inactiveMembers) }}</strong>
                <span>Inactive</span>
            </div>
        </div>
    </section>
</div>

<div class="dashboard-bottom">
    <section class="membership-options">
        <div class="dashboard-section-heading">
            <span class="dashboard-label">YOUR SETUP</span>
            <h2>Membership building blocks</h2>
        </div>

        <div class="option-grid">
            <article class="option-card option-lavender">
                <span class="option-symbol" aria-hidden="true">▦</span>
                <strong>{{ number_format($totalCategories) }}</strong>
                <h3>Categories</h3>
                <p>The groups your members belong to.</p>
            </article>

            <a class="option-card option-peach option-link" href="{{ route('levels.index') }}">
                <span class="option-symbol" aria-hidden="true">☆</span>
                <strong>{{ number_format($totalLevels) }}</strong>
                <h3>Membership Levels ↗</h3>
                <p>Manage fees, validity periods, and benefits.</p>
            </a>
        </div>
    </section>

    <section class="action-panel">
        <span class="dashboard-label">MAKE YOUR NEXT MOVE</span>
        <h2>A little admin.<br>A more organized day.</h2>

        <a class="dashboard-action action-primary" href="{{ route('members.create') }}">
            <span>
                <strong>Register a member</strong>
                <small>Welcome someone new.</small>
            </span>
            <span aria-hidden="true">+</span>
        </a>

        <a class="dashboard-action" href="{{ route('members.index') }}">
            <span>
                <strong>Manage your members</strong>
                <small>Search, update, and review records.</small>
            </span>
            <span aria-hidden="true">↗</span>
        </a>
    </section>
</div>
@endsection