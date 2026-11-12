@extends('layouts.members')

@section('title', 'Membership Applications')
@section('subtitle', 'Review applications submitted through the public form.')

@section('content')
<div class="toolbar">
    <h2 class="section-title">Submitted applications</h2>

    <a href="{{ route('dashboard') }}" class="btn btn-secondary">
        Back to dashboard
    </a>
</div>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Reference</th>
                <th>Applicant</th>
                <th>Email</th>
                <th>Category</th>
                <th>Level</th>
                <th>Status</th>
                <th>Submitted</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($applications as $application)
            <tr>
                <td>
                    <span class="transaction-reference">
                        {{ $application->reference_no }}
                    </span>
                </td>
                <td>{{ $application->full_name }}</td>
                <td>{{ $application->email }}</td>
                <td>{{ $application->category_name }}</td>
                <td>{{ $application->level_name }}</td>
                <td>
                    <span class="badge status-{{ $application->status }}">
                        {{ ucfirst($application->status) }}
                    </span>
                </td>
                <td>
                    {{ $application->created_at->timezone('Asia/Manila')->format('M j, Y') }}
                </td>
                <td>
                    <a href="{{ route('applications.show', $application) }}" class="btn btn-secondary btn-small">
                        View
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="empty">
                    No applications submitted yet.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($applications->hasPages())
<div class="transaction-pagination">
    <span>
        Page {{ $applications->currentPage() }}
        of {{ $applications->lastPage() }}
    </span>

    <div class="actions">
        @if ($applications->previousPageUrl())
        <a href="{{ $applications->previousPageUrl() }}" class="btn btn-secondary btn-small">
            Previous
        </a>
        @endif

        @if ($applications->nextPageUrl())
        <a href="{{ $applications->nextPageUrl() }}" class="btn btn-secondary btn-small">
            Next
        </a>
        @endif
    </div>
</div>
@endif
@endsection