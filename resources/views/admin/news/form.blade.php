@extends('admin.layouts.mainlayout')

@php
    $pageTitle = $post->exists ? __('admin/news.edit') : __('admin/news.add');
    $mainLocale = $defaultLocale ?? $languages->first()?->code;
@endphp

@section('title', $pageTitle)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">{{ __('admin/menuleft.home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.news.index') }}">{{ __('admin/menuleft.news_sub.news') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $pageTitle }}</li>
@endsection

@section('content')
    @include('admin.layouts.partials.alerts')

    <form method="POST" action="{{ $post->exists ? route('admin.news.update', $post) : route('admin.news.store') }}" enctype="multipart/form-data">
        @csrf
        @if ($post->exists)
            @method('PUT')
        @endif

        <div class="row g-3">
            {{-- Left: content per language --}}
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" role="tablist">
                            @foreach ($languages as $language)
                                @php
                                    $tabHasError = collect(['title', 'slug', 'excerpt', 'content'])
                                        ->contains(fn ($field) => $errors->has("translations.{$language->code}.{$field}"));
                                @endphp
                                <li class="nav-item" role="presentation">
                                    <button type="button" class="nav-link {{ $loop->first ? 'active' : '' }} {{ $tabHasError ? 'text-danger' : '' }}"
                                            data-bs-toggle="tab" data-bs-target="#lang-{{ $language->code }}" role="tab">
                                        {{ $language->native_name }}
                                        @if ($language->code === $mainLocale)
                                            <span class="text-danger">*</span>
                                        @endif
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="card-body tab-content">
                        @foreach ($languages as $language)
                            @php
                                $code = $language->code;
                                $translation = $post->translations->firstWhere('locale', $code);
                            @endphp
                            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="lang-{{ $code }}" role="tabpanel">
                                <p class="text-muted small">
                                    {{ $code === $mainLocale ? __('admin/news.language_required') : __('admin/news.language_optional') }}
                                </p>

                                <div class="mb-3">
                                    <label class="form-label" for="title-{{ $code }}">{{ __('admin/news.title') }}</label>
                                    <input type="text" id="title-{{ $code }}" name="translations[{{ $code }}][title]" maxlength="500"
                                           class="form-control @error("translations.$code.title") is-invalid @enderror"
                                           value="{{ old("translations.$code.title", $translation?->title) }}">
                                    @error("translations.$code.title")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="slug-{{ $code }}">{{ __('admin/news.slug') }}</label>
                                    <input type="text" id="slug-{{ $code }}" name="translations[{{ $code }}][slug]" maxlength="150"
                                           class="form-control @error("translations.$code.slug") is-invalid @enderror"
                                           value="{{ old("translations.$code.slug", $translation?->slug) }}">
                                    <div class="form-text">{{ __('admin/news.slug_hint') }}</div>
                                    @error("translations.$code.slug")
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="excerpt-{{ $code }}">{{ __('admin/news.excerpt') }}</label>
                                    <textarea id="excerpt-{{ $code }}" name="translations[{{ $code }}][excerpt]" rows="3" maxlength="1000"
                                              class="form-control @error("translations.$code.excerpt") is-invalid @enderror">{{ old("translations.$code.excerpt", $translation?->excerpt) }}</textarea>
                                    <div class="form-text">{{ __('admin/news.excerpt_hint') }}</div>
                                    @error("translations.$code.excerpt")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-0">
                                    <label class="form-label" for="content-{{ $code }}">{{ __('admin/news.content') }}</label>
                                    <div class="form-text mb-1">{{ __('admin/news.gallery_note') }}</div>
                                    <textarea id="content-{{ $code }}" name="translations[{{ $code }}][content]" rows="12"
                                              class="form-control rich-editor" data-upload-feature="news">{{ old("translations.$code.content", $translation?->content) }}</textarea>
                                    @error("translations.$code.content")
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            {{-- Right: publishing options and images --}}
            <div class="col-lg-4">
                <div class="card mb-3">
                    <div class="card-header">{{ __('admin/news.section_details') }}</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label" for="category_id">{{ __('admin/news.col_category') }}</label>
                            <select id="category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                                <option value="">{{ __('admin/news.no_category') }}</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected((int) old('category_id', $post->category_id) === $category->id)>{{ $category->nameFor($mainLocale) }}</option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="status">{{ __('admin/news.col_status') }}</label>
                            <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="draft" @selected(old('status', $post->status) === 'draft')>{{ __('admin/news.status_draft') }}</option>
                                <option value="published" @selected(old('status', $post->status) === 'published')>{{ __('admin/news.status_published') }}</option>
                            </select>
                            <div class="form-text">{{ __('admin/news.status_hint') }}</div>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-0">
                            <label class="form-label" for="published_at">{{ __('admin/news.published_at') }}</label>
                            <input type="datetime-local" id="published_at" name="published_at"
                                   class="form-control @error('published_at') is-invalid @enderror"
                                   value="{{ old('published_at', $post->published_at?->format('Y-m-d\TH:i')) }}">
                            <div class="form-text">{{ __('admin/news.published_at_hint') }}</div>
                            @error('published_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">{{ __('admin/news.section_images') }}</div>
                    <div class="card-body">
                        {{-- Cover --}}
                        <div class="mb-3">
                            <label class="form-label" for="cover">{{ __('admin/news.cover') }}</label>
                            @if ($post->cover_image)
                                <div class="mb-2">
                                    <img src="{{ $post->cover_url }}" alt="" class="img-thumbnail" style="max-height: 140px;">
                                    <div class="form-check mt-1">
                                        <input class="form-check-input" type="checkbox" name="remove_cover" value="1" id="remove_cover">
                                        <label class="form-check-label" for="remove_cover">{{ __('admin/news.remove_cover') }}</label>
                                    </div>
                                </div>
                            @endif
                            <input type="file" id="cover" name="cover" accept="image/jpeg,image/png,image/webp"
                                   class="form-control @error('cover') is-invalid @enderror">
                            <div class="form-text">{{ __('admin/news.cover_hint') }}</div>
                            @error('cover')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Event photos (gallery under the article) --}}
                <div class="card mb-3">
                    <div class="card-header">{{ __('admin/news.section_gallery') }}</div>
                    <div class="card-body">
                        @if ($post->exists && $post->images->isNotEmpty())
                            <div class="row g-2 mb-3">
                                @foreach ($post->images as $image)
                                    <div class="col-4">
                                        <img src="{{ $image->url }}" alt="" class="img-thumbnail w-100" style="height: 90px; object-fit: cover;">
                                        <div class="form-check mt-1">
                                            <input class="form-check-input" type="checkbox" name="remove_images[]" value="{{ $image->id }}" id="remove_image_{{ $image->id }}">
                                            <label class="form-check-label small" for="remove_image_{{ $image->id }}">{{ __('admin/news.remove_image') }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        <label class="form-label" for="gallery">{{ __('admin/news.gallery_add') }}</label>
                        <input type="file" id="gallery" name="gallery[]" multiple accept="image/jpeg,image/png,image/webp"
                               class="form-control @error('gallery') is-invalid @enderror @error('gallery.*') is-invalid @enderror">
                        <div class="form-text">{{ __('admin/news.gallery_hint') }}</div>
                        @error('gallery')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @error('gallery.*')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="d-flex gap-2 mt-1">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> {{ __('admin/common.save') }}</button>
            <a href="{{ route('admin.news.index') }}" class="btn btn-outline-secondary">{{ __('admin/common.back') }}</a>
        </div>
    </form>
@endsection