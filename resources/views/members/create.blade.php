@extends('layouts.members')

@section('title', 'Add Member')
@section('subtitle', 'Enter the new member’s details.')
@section('container-class', 'narrow')

@section('content')
<form action="{{ route('members.store') }}" method="POST">
    @csrf

    @include('members._form', ['submitLabel' => 'Save Member'])
</form>
@endsection