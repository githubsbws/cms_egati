@extends('web.layouts.main')

@section('title', __web('home.welcome'))

@section('content')
    <div class="p-5 mb-4 bg-light rounded-3">
        <h1 class="display-5 fw-bold">{{ __web('home.welcome') }}</h1>
        <p class="col-md-8 fs-4">{{ config('app.name', 'Laravel') }}</p>
        <a href="#" class="btn btn-primary">{{ __web('common.read_more') }}</a>
    </div>
@endsection