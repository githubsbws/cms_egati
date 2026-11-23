@extends('admin.layouts.mainlayout')

@php
    $pageTitle = $category->exists ? __('admin/news.edit_category') : __('admin/news.add_category');
@endphp

@section('title', $pageTitle)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">{{ __('admin/menuleft.home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.news.category.index') }}">{{ __('admin/menuleft.news_sub.news_category') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $pageTitle }}</li>
@endsection

@section('content')
    @include('admin.layouts.partials.alerts')

    <form method="POST" action="{{ $category->exists ? route('admin.news.category.update', $category) : route('admin.news.category.store') }}">
        @csrf
        @if ($category->exists)
            @method('PUT')
        @endif

        <div class="card mx-auto" style="max-width: 720px;">
            <div class="card-body">
                {{-- One name per language; the default language is required --}}
                @foreach ($languages as $language)
                    @php
                        $code = $language->code;
                        $current = $category->translations->firstWhere('locale', $code)?->name;
                    @endphp
                    <div class="mb-3">
                        <label class="form-label" for="name-{{ $code }}">
                            {{ __('admin/news.category_name') }} ({{ $language->native_name }})
                            @if ($code === $defaultLocale)
                                <span class="text-danger">*</span>
                            @endif
                        </label>
                        <input type="text" id="name-{{ $code }}" name="names[{{ $code }}]" maxlength="255"
                               class="form-control @error("names.$code") is-invalid @enderror"
                               value="{{ old("names.$code", $current) }}">
                        @error("names.$code")
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @endforeach

                <div class="row">
                    <div class="col-sm-6 mb-3">
                        <label class="form-label" for="accent_color">{{ __('admin/news.category_color') }}</label>
                        <input type="color" id="accent_color" name="accent_color" class="form-control form-control-color @error('accent_color') is-invalid @enderror"
                               value="{{ old('accent_color', $category->accent_color) }}">
                        <div class="form-text">{{ __('admin/news.category_color_hint') }}</div>
                        @error('accent_color')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-sm-6 mb-3">
                        <label class="form-label" for="sort_order">{{ __('admin/news.category_sort') }}</label>
                        <input type="number" id="sort_order" name="sort_order" min="0" max="9999"
                               class="form-control @error('sort_order') is-invalid @enderror"
                               value="{{ old('sort_order', $category->sort_order) }}">
                        @error('sort_order')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> {{ __('admin/common.save') }}</button>
                <a href="{{ route('admin.news.category.index') }}" class="btn btn-outline-secondary">{{ __('admin/common.back') }}</a>
            </div>
        </div>
    </form>
@endsection