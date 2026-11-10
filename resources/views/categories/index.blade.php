@extends('layouts.members')

@section('title', 'Membership Categories')
@section('subtitle', 'Manage the categories available to your members.')

@section('content')
<div class="toolbar">
    <h2 class="section-title">Categories</h2>

    <a class="btn" href="{{ route('categories.create') }}">
        Add Category
    </a>
</div>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th>Members</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($categories as $category)
            <tr>
                <td>{{ $category->name }}</td>
                <td>{{ $category->members_count }}</td>
                <td>
                    <a class="btn btn-secondary btn-small" href="{{ route('categories.edit', $category) }}">
                        Edit
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="empty">
                    No categories yet.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection