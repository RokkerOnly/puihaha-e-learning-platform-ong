@extends('layouts.members')

@section('title', 'Edit Member')
@section('subtitle', 'Update the member’s details.')
@section('container-class', 'narrow')

@section('content')
<form action="{{ route('members.update', $member->id) }}" method="POST">
    @csrf
    @method('PUT')

    @include('members._form', ['submitLabel' => 'Update Member'])
</form>
@endsection