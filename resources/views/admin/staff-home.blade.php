@extends('layouts.admin')
@section('title', 'Dashboard — ChairGhor')
@section('content')
<div class="settings-page-heading"><h1>Welcome, {{ auth()->user()->name }}</h1><p>Use the menu to open the sections assigned to you. Contact your Super Admin if you need more access.</p></div>
@endsection
