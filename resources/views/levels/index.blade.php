@extends('layouts.members')

@section('title', 'Membership Levels')
@section('subtitle', 'Manage fees, membership duration, and benefits.')

@section('content')
<div class="toolbar">
    <h2 class="section-title">Membership Levels</h2>

    <a class="btn" href="{{ route('levels.create') }}">
        Add Level
    </a>
</div>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Level</th>
                <th>Fee (PHP)</th>
                <th>Validity</th>
                <th>Benefits</th>
                <th>Members</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($levels as $level)
            <tr>
                <td>{{ $level->name }}</td>

                <td>{{ number_format($level->fee, 2) }}</td>

                <td>
                    {{ $level->validity_months }}
                    {{ $level->validity_months == 1 ? 'month' : 'months' }}
                </td>

                <td class="benefits-text">{{ $level->benefits ?: 'No benefits specified.' }}</td>

                <td>{{ $level->members_count }}</td>

                <td>
                    <a class="btn btn-secondary btn-small" href="{{ route('levels.edit', $level) }}">
                        Edit
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="empty">
                    No levels yet. Add a membership level to get started.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection