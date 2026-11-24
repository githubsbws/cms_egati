@extends('admin.layouts.mainlayout')

@use('App\Helpers\HomeContent')
@php
    $pageTitle = __('admin/menuleft.content_sub.'.$menuKey);
    $multiple = count($blocks) > 1;
@endphp

@section('title', $pageTitle)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">{{ __('admin/menuleft.home') }}</a></li>
    <li class="breadcrumb-item">{{ __('admin/menuleft.content_sub.content') }}</li>
    <li class="breadcrumb-item active" aria-current="page">{{ $pageTitle }}</li>
@endsection

@section('content')
    @include('admin.layouts.partials.alerts')

    <form method="POST" action="{{ route("admin.content.{$section}.update") }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @foreach ($blocks as $code => $definition)
            @php
                $block = $saved->get($code);
                $index = $loop->iteration;
            @endphp
            <div class="card mb-3">
                @if ($multiple)
                    <div class="card-header"><strong>{{ __('admin/home_content.slide', ['n' => $index]) }}</strong></div>
                @endif
                <div class="card-body">
                    <div class="row g-4">
                        {{-- Text per language --}}
                        <div class="col-lg-7">
                            <ul class="nav nav-tabs" role="tablist">
                                @foreach ($languages as $language)
                                    @php
                                        $tabHasError = collect($errors->keys())->contains(fn ($key) => str_starts_with($key, "blocks.{$code}.texts.{$language->code}."));
                                    @endphp
                                    <li class="nav-item" role="presentation">
                                        <button type="button" class="nav-link {{ $loop->first ? 'active' : '' }} {{ $tabHasError ? 'text-danger' : '' }}" data-bs-toggle="tab" data-bs-target="#text-{{ $code }}-{{ $language->code }}" role="tab">{{ $language->native_name }}</button>
                                    </li>
                                @endforeach
                            </ul>
                            <div class="tab-content border border-top-0 rounded-bottom p-3">
                                @foreach ($languages as $language)
                                    @php
                                        $locale = $language->code;
                                        $savedFields = $block?->texts->firstWhere('locale', $locale)?->fields ?? [];
                                    @endphp
                                    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="text-{{ $code }}-{{ $locale }}" role="tabpanel">
                                        <p class="text-muted small">{{ __('admin/home_content.text_hint') }}</p>
                                        @foreach (collect($definition['texts'])->filter(fn ($meta) => ($meta['admin'] ?? true) !== false) as $field => $meta)
                                            @php
                                                $name = "blocks.{$code}.texts.{$locale}.{$field}";
                                                $value = old($name, HomeContent::saved($code, $field, $locale) ?: HomeContent::defaultPlain($code, $field, $locale));
                                                $inputName = "blocks[{$code}][texts][{$locale}][{$field}]";
                                            @endphp
                                            <div class="mb-3">
                                                <label class="form-label" for="{{ $code }}-{{ $locale }}-{{ $field }}">{{ __("admin/home_content.fields.{$field}") }}</label>
                                                @if ($meta['type'] === 'rich' || ($meta['long'] ?? false))
                                                    <textarea id="{{ $code }}-{{ $locale }}-{{ $field }}" name="{{ $inputName }}" rows="{{ ($meta['long'] ?? false) ? 4 : 2 }}" maxlength="{{ ($meta['long'] ?? false) ? 2000 : 255 }}"
                                                              class="form-control @error($name) is-invalid @enderror">{{ $value }}</textarea>
                                                @else
                                                    <input type="text" id="{{ $code }}-{{ $locale }}-{{ $field }}" name="{{ $inputName }}" maxlength="255" value="{{ $value }}"
                                                           class="form-control @error($name) is-invalid @enderror">
                                                @endif
                                                @error($name)
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Files and links --}}
                        <div class="col-lg-5">
                            @foreach (['video', 'poster'] as $kind)
                                @php $hasSaved = HomeContent::hasSavedMedia($code, $kind); @endphp
                                <div class="mb-3">
                                    <label class="form-label" for="{{ $code }}-{{ $kind }}">{{ __("admin/home_content.{$kind}") }}</label>
                                    <div class="mb-2">
                                        @if ($kind === 'video')
                                            <video src="{{ HomeContent::video($code) }}" poster="{{ HomeContent::poster($code) }}" controls preload="none" class="w-100 rounded border" style="max-height: 180px;"></video>
                                        @else
                                            <img src="{{ HomeContent::poster($code) }}" alt="" class="img-thumbnail" style="max-height: 140px;">
                                        @endif
                                        <div class="small {{ $hasSaved ? 'text-success' : 'text-muted' }}">{{ $hasSaved ? __('admin/home_content.media_custom') : __('admin/home_content.media_default') }}</div>
                                    </div>
                                    <input type="file" id="{{ $code }}-{{ $kind }}" name="blocks[{{ $code }}][{{ $kind }}]"
                                           accept="{{ $kind === 'video' ? 'video/mp4,video/webm' : 'image/png,image/jpeg,image/webp' }}"
                                           class="form-control @error("blocks.{$code}.{$kind}") is-invalid @enderror">
                                    <div class="form-text">{{ __("admin/home_content.{$kind}_hint", ['size' => (int) (config($kind === 'video' ? 'home_content.max_video_kb' : 'home_content.max_image_kb') / 1024)]) }}</div>
                                    @error("blocks.{$code}.{$kind}")
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endforeach

                            @foreach (collect($definition['plain'] ?? [])->filter(fn ($meta) => ($meta['admin'] ?? true) !== false) as $field => $meta)
                                @php
                                    $name = "blocks.{$code}.plain.{$field}";
                                    $value = old($name, $block?->plain[$field] ?? $meta['default']);
                                @endphp
                                <div class="mb-3">
                                    <label class="form-label" for="{{ $code }}-plain-{{ $field }}">{{ __("admin/home_content.fields.{$field}") }}</label>
                                    <input type="text" id="{{ $code }}-plain-{{ $field }}" name="blocks[{{ $code }}][plain][{{ $field }}]" maxlength="500" value="{{ $value }}"
                                           class="form-control @error($name) is-invalid @enderror">
                                    <div class="form-text">{{ $meta['type'] === 'duration' ? __('admin/home_content.duration_hint') : __('admin/home_content.link_hint') }}</div>
                                    @error($name)
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> {{ __('admin/common.save') }}</button>
    </form>
@endsection
