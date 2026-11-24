@extends('admin.layouts.mainlayout')

@php
    $editing = $item->exists;
    $pageTitle = $editing ? __('admin/documents.edit_item') : __('admin/documents.add_item');
    $listTitle = __('admin/menuleft.document_sub.document_'.$type);
    $maxPdf = (int) (config('publications.max_pdf_kb') / 1024);
@endphp

@section('title', $pageTitle)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">{{ __('admin/menuleft.home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route("admin.documents.{$type}.index") }}">{{ $listTitle }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $pageTitle }}</li>
@endsection

@section('content')
    @include('admin.layouts.partials.alerts')

    <form method="POST" action="{{ $editing ? route("admin.documents.{$type}.update", $item) : route("admin.documents.{$type}.store") }}" enctype="multipart/form-data">
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <div class="card mx-auto" style="max-width: 760px;">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label" for="year">{{ __('admin/documents.field_year') }}</label>
                        <input type="number" id="year" name="year" min="1990" max="2200" class="form-control @error('year') is-invalid @enderror" value="{{ old('year', $item->year) }}">
                        @error('year')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-8 align-self-end"><div class="form-text mb-2">{{ __('admin/documents.year_hint') }}</div></div>

                    @if ($type === 'credit')
                        <div class="col-md-6">
                            <label class="form-label" for="agency">{{ __('admin/documents.field_agency') }}</label>
                            <select id="agency" name="agency" class="form-select @error('agency') is-invalid @enderror">
                                @foreach (array_keys(config('publications.agencies')) as $agency)
                                    <option value="{{ $agency }}" @selected(old('agency', $item->agency) === $agency)>{{ $agency }}</option>
                                @endforeach
                            </select>
                            @error('agency')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="rating">{{ __('admin/documents.field_rating') }}</label>
                            <input type="text" id="rating" name="rating" maxlength="20" class="form-control @error('rating') is-invalid @enderror" value="{{ old('rating', $item->rating) }}">
                            <div class="form-text">{{ __('admin/documents.rating_hint') }}</div>
                            @error('rating')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    @if ($type === 'finance')
                        <div class="col-md-6">
                            <label class="form-label" for="period">{{ __('admin/documents.field_period') }}</label>
                            <select id="period" name="period" class="form-select @error('period') is-invalid @enderror">
                                @foreach (\App\Models\Publication::PERIODS as $period)
                                    <option value="{{ $period }}" @selected((string) old('period', $item->period) === $period)>{{ $period === 'annual' ? __('admin/documents.period_annual') : __('admin/documents.period_quarter', ['n' => $period]) }}</option>
                                @endforeach
                            </select>
                            @error('period')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    {{-- Files: one (credit, annual) or Thai + English (finance) --}}
                    @foreach ($type === 'finance' ? ['th' => ['file', 'external_url', 'remove_file', 'field_file_th'], 'en' => ['file_en', 'external_url_en', 'remove_file_en', 'field_file_en']] : ['th' => ['file', 'external_url', 'remove_file', 'field_file']] as $lang => [$fileInput, $urlInput, $removeInput, $label])
                        @php $currentUrl = $item->exists ? $item->documentUrl($lang) : null; @endphp
                        <div class="col-12">
                            <div class="border rounded p-3">
                                <label class="form-label fw-semibold" for="{{ $fileInput }}">{{ __("admin/documents.{$label}") }}</label>
                                @if ($currentUrl)
                                    <div class="small mb-2">
                                        {{ __('admin/documents.current_file') }}: <a href="{{ $currentUrl }}" target="_blank" rel="noopener">{{ __('admin/documents.open') }} ↗</a>
                                        @if ($lang === 'en' ? $item->file_path_en : $item->file_path)
                                            <div class="form-check mt-1">
                                                <input class="form-check-input" type="checkbox" name="{{ $removeInput }}" value="1" id="{{ $removeInput }}">
                                                <label class="form-check-label" for="{{ $removeInput }}">{{ __('admin/documents.remove_file') }}</label>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                                <input type="file" id="{{ $fileInput }}" name="{{ $fileInput }}" accept="application/pdf" class="form-control mb-2 @error($fileInput) is-invalid @enderror">
                                <div class="form-text mb-2">{{ __('admin/documents.file_hint', ['size' => $maxPdf]) }}</div>
                                @error($fileInput)
                                    <div class="invalid-feedback d-block mb-2">{{ $message }}</div>
                                @enderror
                                <label class="form-label small" for="{{ $urlInput }}">{{ __('admin/documents.field_url') }}</label>
                                <input type="url" id="{{ $urlInput }}" name="{{ $urlInput }}" maxlength="1000" placeholder="https://" class="form-control @error($urlInput) is-invalid @enderror" value="{{ old($urlInput, $lang === 'en' ? $item->external_url_en : $item->external_url) }}">
                                @error($urlInput)
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    @endforeach

                    @if ($type === 'annual')
                        <div class="col-12">
                            <label class="form-label" for="cover">{{ __('admin/documents.field_cover') }}</label>
                            @if ($item->cover_url)
                                <div class="mb-2"><img src="{{ $item->cover_url }}" alt="" class="img-thumbnail" style="max-height: 140px;"></div>
                            @endif
                            <input type="file" id="cover" name="cover" accept="image/jpeg,image/png,image/webp" class="form-control @error('cover') is-invalid @enderror">
                            <div class="form-text">{{ __('admin/documents.cover_hint', ['size' => (int) (config('publications.max_cover_kb') / 1024)]) }}</div>
                            @error('cover')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_published" value="1" id="is_published" @checked(old('is_published', $editing ? $item->is_published : true))>
                            <label class="form-check-label" for="is_published">{{ __('admin/documents.field_published') }}</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mt-3 mx-auto" style="max-width: 760px;">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> {{ __('admin/common.save') }}</button>
            <a href="{{ route("admin.documents.{$type}.index") }}" class="btn btn-outline-secondary">{{ __('admin/common.back') }}</a>
        </div>
    </form>
@endsection
