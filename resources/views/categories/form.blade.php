@extends('layouts.members')

@section('title', $category->exists ? 'Edit Category' : 'Add Category')
@section('subtitle', 'Set the category name used in member registration.')
@section('container-class', 'narrow')

@section('content')
<form action="{{ $category->exists
                ? route('categories.update', $category)
                : route('categories.store') }}" method="POST">
    @csrf

    @if ($category->exists)
    @method('PUT')
    @endif

    <div class="field">
        <label for="name">Category Name</label>

        <input id="name" type="text" name="name" value="{{ old('name', $category->name) }}"
            placeholder="For example: Student" maxlength="255" required>
    </div>

    <div class="actions">
        <button class="btn" type="submit">
            {{ $category->exists ? 'Update Category' : 'Save Category' }}
        </button>

        <a class="btn btn-secondary" href="{{ route('categories.index') }}">
            Cancel
        </a>
    </div>
</form>
@endsection