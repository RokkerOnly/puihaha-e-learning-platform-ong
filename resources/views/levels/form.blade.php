@extends('layouts.members')

@section('title', $level->exists ? 'Edit Membership Level' : 'Add Membership Level')
@section('subtitle', 'Define what this membership includes.')
@section('container-class', 'narrow')

@section('content')
<form action="{{ $level->exists
                ? route('levels.update', $level)
                : route('levels.store') }}" method="POST">
    @csrf

    @if ($level->exists)
    @method('PUT')
    @endif

    <div class="field">
        <label for="name">Level Name</label>

        <input id="name" type="text" name="name" value="{{ old('name', $level->name) }}" placeholder="For example: Gold"
            maxlength="255" required>
    </div>

    <div class="field">
        <label for="fee">Membership Fee (PHP)</label>

        <input id="fee" type="number" name="fee" value="{{ old('fee', $level->fee) }}" placeholder="0.00" min="0"
            max="99999999.99" step="0.01" required>
    </div>

    <div class="field">
        <label for="validity_months">Validity Period (Months)</label>

        <input id="validity_months" type="number" name="validity_months"
            value="{{ old('validity_months', $level->validity_months) }}" placeholder="For example: 12" min="1"
            max="1200" step="1" required>
    </div>

    <div class="field">
        <label for="benefits">Benefits (Optional)</label>

        <textarea id="benefits" name="benefits" rows="5" maxlength="10000"
            placeholder="Enter the benefits included in this level.">{{ old('benefits', $level->benefits) }}</textarea>
    </div>

    <div class="actions">
        <button class="btn" type="submit">
            {{ $level->exists ? 'Update Level' : 'Save Level' }}
        </button>

        <a class="btn btn-secondary" href="{{ route('levels.index') }}">
            Cancel
        </a>
    </div>
</form>
@endsection