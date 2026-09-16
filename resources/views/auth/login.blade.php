@extends('layouts.app')

@section('title', $siteContent->text('login','heading','page_title'))

@section('content')
<div class="page-hero"><div class="container">        <span class="eyebrow">{{ $siteContent->text('login','heading','field_1') }}</span>
        <h1>{{ $siteContent->text('login','heading','field_2') }}</h1>
        <p>{{ $siteContent->text('login','heading','field_3') }}</p></div></div>
<section class="auth-section">
    <div class="auth-card">


        <form method="post" action="{{route('login')}}">
            @csrf
            <label for="email">{{ $siteContent->text('login','login_form','field_1') }}</label>
            <input id="email" class="form-control" type="email" name="email" value="{{old('email')}}" autocomplete="email" required autofocus>
            @error('email')<small class="text-danger">{{$message}}</small>@enderror

            <label for="password">{{ $siteContent->text('login','login_form','field_2') }}</label>
            <input id="password" class="form-control" type="password" name="password" autocomplete="current-password" required>
            @error('password')<small class="text-danger">{{$message}}</small>@enderror

            <label class="remember"><input type="checkbox" name="remember" value="1"> {{ $siteContent->text('login','login_form','field_3') }}</label>
            <button class="btn btn-brand w-100" type="submit">{{ $siteContent->text('login','login_form','field_4') }}</button>
        </form>
    </div>
</section>
@endsection
