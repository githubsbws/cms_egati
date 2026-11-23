@extends('admin.layouts.mainlayout')

@php
    $editing = $language->exists;
    $pageTitle = $editing ? __('admin/language.edit') : __('admin/language.add');
    $isDefault = $language->is_default === 'y';
@endphp

@section('title', $pageTitle)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">{{ __('admin/menuleft.home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.settings.language.index') }}">{{ __('admin/menuleft.set_sub.language') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $pageTitle }}</li>
@endsection

@section('content')
    @include('admin.layouts.partials.alerts')

    <form method="POST" action="{{ $editing ? route('admin.settings.language.update', $language) : route('admin.settings.language.store') }}" enctype="multipart/form-data">
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <div class="card mx-auto" style="max-width: 760px;">
            <div class="card-body">
                {{-- Code (fixed after creation) --}}
                <div class="mb-3">
                    <label class="form-label" for="code">{{ __('admin/language.field_code') }}</label>
                    @if ($editing)
                        <input type="text" id="code" class="form-control" value="{{ $language->code }}" disabled>
                    @else
                        <input type="text" id="code" name="code" maxlength="3" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}">
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    @endif
                    <div class="form-text">{{ __('admin/language.code_hint') }}</div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="name">{{ __('admin/language.field_name') }}</label>
                        <input type="text" id="name" name="name" maxlength="100" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $language->name) }}">
                        <div class="form-text">{{ __('admin/language.name_hint') }}</div>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="native_name">{{ __('admin/language.field_native') }}</label>
                        <input type="text" id="native_name" name="native_name" maxlength="100" class="form-control @error('native_name') is-invalid @enderror" value="{{ old('native_name', $language->native_name) }}">
                        <div class="form-text">{{ __('admin/language.native_hint') }}</div>
                        @error('native_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="direction">{{ __('admin/language.field_direction') }}</label>
                        <select id="direction" name="direction" class="form-select @error('direction') is-invalid @enderror">
                            <option value="ltr" @selected(old('direction', $language->direction) === 'ltr')>{{ __('admin/language.ltr') }}</option>
                            <option value="rtl" @selected(old('direction', $language->direction) === 'rtl')>{{ __('admin/language.rtl') }}</option>
                        </select>
                        <div class="form-text">{{ __('admin/language.direction_hint') }}</div>
                        @error('direction')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="sort_order">{{ __('admin/language.field_order') }}</label>
                        <input type="number" id="sort_order" name="sort_order" min="0" max="999" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', $language->sort_order) }}">
                        <div class="form-text">{{ __('admin/language.order_hint') }}</div>
                        @error('sort_order')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Flag --}}
                <div class="mb-3">
                    <label class="form-label" for="flag">{{ __('admin/language.field_flag') }}</label>
                    @if ($language->flag)
                        <div class="mb-2">
                            <img src="{{ web_file_url($language->flag) }}" alt="" class="border" style="height: 32px; width: 48px; object-fit: cover;">
                            <div class="form-check mt-1">
                                <input class="form-check-input" type="checkbox" name="remove_flag" value="1" id="remove_flag">
                                <label class="form-check-label" for="remove_flag">{{ __('admin/language.remove_flag') }}</label>
                            </div>
                        </div>
                    @endif
                    <input type="file" id="flag" name="flag" accept="image/png,image/jpeg,image/webp" class="form-control @error('flag') is-invalid @enderror">
                    <div class="form-text">{{ __('admin/language.flag_hint') }}</div>
                    @error('flag')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Status --}}
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $language->is_active === 'y' || $isDefault)) @disabled($isDefault)>
                    <label class="form-check-label" for="is_active">{{ __('admin/language.field_active') }}</label>
                    @error('is_active')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-check mb-1">
                    <input class="form-check-input" type="checkbox" name="is_default" value="1" id="is_default" @checked(old('is_default', $isDefault)) @disabled($isDefault)>
                    <label class="form-check-label" for="is_default">{{ __('admin/language.field_default') }}</label>
                </div>
                <div class="form-text mb-3">{{ __('admin/language.default_hint') }}</div>

                {{-- Text file: only when adding (after that use the text editor) --}}
                @unless ($editing)
                    <hr>
                    <h6>{{ __('admin/language.section_text') }}</h6>
                    <div class="mb-2">
                        <input type="file" id="json_file" name="json_file" accept=".json,application/json" class="form-control @error('json_file') is-invalid @enderror">
                        <div class="form-text">{{ __('admin/language.json_upload_hint') }}</div>
                        @error('json_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    @if ($defaultLanguage)
                        <a href="{{ route('admin.settings.language.download', $defaultLanguage) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-download me-1"></i> {{ __('admin/language.download_template', ['code' => $defaultLanguage->code]) }}</a>
                    @endif
                @endunless
            </div>
            <div class="card-footer d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> {{ __('admin/common.save') }}</button>
                @if ($editing)
                    <a href="{{ route('admin.settings.language.strings', $language) }}" class="btn btn-info"><i class="bi bi-braces me-1"></i> {{ __('admin/language.edit_text') }}</a>
                @endif
                <a href="{{ route('admin.settings.language.index') }}" class="btn btn-outline-secondary">{{ __('admin/language.back') }}</a>
            </div>
        </div>
    </form>
@endsection