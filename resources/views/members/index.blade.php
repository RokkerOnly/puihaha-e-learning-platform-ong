@extends('layouts.members')

@section('title', 'Members')
@section('subtitle', 'Manage your members and membership details.')

@section('content')
<div class="toolbar">
    <form class="search-form" action="{{ route('members.index') }}" method="GET">

        <input type="search" name="search" aria-label="Search members" value="{{ request('search') }}"
            placeholder="Search by name, category, or level">

        <button class="btn" type="submit">Search</button>

        @if (request('search') !== null && request('search') !== '')
        <a class="btn btn-secondary" href="{{ route('members.index') }}">
            Clear
        </a>
        @endif
    </form>

    <a class="btn" href="{{ route('members.create') }}">
        Add Member
    </a>
</div>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Membership No</th>
                <th>Name</th>
                <th>Email</th>
                <th>Category</th>
                <th>Level</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($members as $member)
            <tr>
                <td>{{ $member->id }}</td>
                <td>{{ $member->membership_no }}</td>
                <td>{{ $member->first_name }} {{ $member->last_name }}</td>
                <td>{{ $member->email }}</td>
                <td>{{ $member->category?->name ?? '—' }}</td>
                <td>{{ $member->level?->name ?? '—' }}</td>

                <td>
                    <span class="badge {{ $member->active ? 'badge-active' : 'badge-inactive' }}">
                        {{ $member->active ? 'Active' : 'Inactive' }}
                    </span>
                </td>

                <td>
                    <div class="actions">
                        <a class="btn btn-secondary btn-small" href="{{ route('members.show', $member->id) }}">
                            View
                        </a>

                        <a class="btn btn-secondary btn-small" href="{{ route('members.edit', $member->id) }}">
                            Edit
                        </a>

                        @if ($member->active)
                        <form action="{{ route('members.deactivate', $member->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <button class="btn btn-danger btn-small" type="submit">
                                Deactivate
                            </button>
                        </form>
                        @else
                        <form action="{{ route('members.reactivate', $member->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <button class="btn btn-small" type="submit">
                                Reactivate
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="empty">
                    No members found.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection