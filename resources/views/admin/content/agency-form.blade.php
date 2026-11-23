@extends('admin.layouts.mainlayout')

@php
    $editing = $agency->exists;
    $pageTitle = $editing ? __('admin/home_content.agency.edit') : __('admin/home_content.agency.add');
@endphp

@section('title', $pageTitle)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">{{ __('admin/menuleft.home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.content.agencies.index') }}">{{ __('admin/menuleft.content_sub.related_agencies') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $pageTitle }}</li>
@endsection

@section('content')
    @include('admin.layouts.partials.alerts')

    <form method="POST" action="{{ $editing ? route('admin.content.agencies.update', $agency) : route('admin.content.agencies.store') }}" enctype="multipart/form-data">
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <div class="row g-3">
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" role="tablist">
                            @foreach ($languages as $language)
                                <li class="nav-item" role="presentation">
                                    <button type="button" class="nav-link {{ $loop->first ? 'active' : '' }} {{ $errors->has("translations.{$language->code}.name") ? 'text-danger' : '' }}" data-bs-toggle="tab" data-bs-target="#lang-{{ $language->code }}" role="tab">
                                        {{ $language->native_name }}@if ($language->code === $defaultLocale) <span class="text-danger">*</span>@endif
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="card-body tab-content">
                        @foreach ($languages as $language)
                            @php $code = $language->code; @endphp
                            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="lang-{{ $code }}" role="tabpanel">
                                <label class="form-label" for="name-{{ $code }}">{{ __('admin/home_content.agency.name') }}</label>
                                <input type="text" id="name-{{ $code }}" name="translations[{{ $code }}][name]" maxlength="255"
                                       class="form-control @error("translations.$code.name") is-invalid @enderror"
                                       value="{{ old("translations.$code.name", $agency->translation($code)?->name) }}">
                                @error("translations.$code.name")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label" for="url">{{ __('admin/home_content.agency.url') }}</label>
                            <input type="url" id="url" name="url" maxlength="500" placeholder="https://" class="form-control @error('url') is-invalid @enderror" value="{{ old('url', $agency->url) }}">
                            @error('url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $editing ? $agency->is_active : true))>
                            <label class="form-check-label" for="is_active">{{ __('admin/home_content.agency.active') }}</label>
                        </div>

                        <div class="mb-0">
                            <label class="form-label" for="logo">{{ __('admin/home_content.agency.logo') }}</label>
                            @if ($agency->logo_url)
                                <div class="mb-2"><img src="{{ $agency->logo_url }}" alt="" class="img-thumbnail" style="max-height: 120px;"></div>
                            @endif
                            <input type="file" id="logo" name="logo" accept="image/png,image/jpeg,image/webp,image/svg+xml" class="form-control @error('logo') is-invalid @enderror">
                            <div class="form-text">{{ __('admin/home_content.agency.logo_hint', ['size' => (int) (config('home_content.max_logo_kb') / 1024)]) }}</div>
                            @error('logo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> {{ __('admin/common.save') }}</button>
            <a href="{{ route('admin.content.agencies.index') }}" class="btn btn-outline-secondary">{{ __('admin/common.back') }}</a>
        </div>
    </form>
@endsection
