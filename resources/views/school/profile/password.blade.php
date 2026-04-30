@extends('layouts.school')

@section('title', 'Change Password')

@section('content')
<script>window.location.replace('{{ route('school.profile.show') }}');</script>
@endsection
