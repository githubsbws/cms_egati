@extends('admin.layouts.mainlayout')

@php
    $editing = $job->exists;
    $pageTitle = $editing ? __('admin/careers.edit_job') : __('admin/careers.add_job');
    $listTitle = __('admin/menuleft.work_us_sub.career_jobs');
@endphp

@section('title', $pageTitle)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">{{ __('admin/menuleft.home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.careers.jobs.index') }}">{{ $listTitle }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $pageTitle }}</li>
@endsection

@section('content')
    @include('admin.layouts.partials.alerts')

    <form method="POST" action="{{ $editing ? route('admin.careers.jobs.update', $job) : route('admin.careers.jobs.store') }}">
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <div class="row g-3">
            {{-- Text per language --}}
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" role="tablist">
                            @foreach ($languages as $language)
                                @php $tabHasError = collect($errors->keys())->contains(fn ($key) => str_starts_with($key, "translations.{$language->code}.")); @endphp
                                <li class="nav-item" role="presentation">
                                    <button type="button" class="nav-link {{ $loop->first ? 'active' : '' }} {{ $tabHasError ? 'text-danger' : '' }}" data-bs-toggle="tab" data-bs-target="#lang-{{ $language->code }}" role="tab">
                                        {{ $language->native_name }}@if ($language->code === $defaultLocale) <span class="text-danger">*</span>@endif
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="card-body tab-content">
                        @foreach ($languages as $language)
                            @php
                                $code = $language->code;
                                $translation = $job->translation($code);
                                $qualifications = old("translations.$code.qualifications", implode("\n", $translation?->qualifications ?? []));
                            @endphp
                            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="lang-{{ $code }}" role="tabpanel">
                                <p class="text-muted small">{{ $code === $defaultLocale ? __('admin/careers.language_required') : __('admin/careers.language_optional') }}</p>
                                <div class="mb-3">
                                    <label class="form-label" for="title-{{ $code }}">{{ __('admin/careers.field_title') }}</label>
                                    <input type="text" id="title-{{ $code }}" name="translations[{{ $code }}][title]" maxlength="255" class="form-control @error("translations.$code.title") is-invalid @enderror" value="{{ old("translations.$code.title", $translation?->title) }}">
                                    @error("translations.$code.title")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="department-{{ $code }}">{{ __('admin/careers.field_department') }}</label>
                                    <input type="text" id="department-{{ $code }}" name="translations[{{ $code }}][department]" maxlength="255" class="form-control" value="{{ old("translations.$code.department", $translation?->department) }}">
                                </div>
                                <div class="mb-0">
                                    <label class="form-label" for="qualifications-{{ $code }}">{{ __('admin/careers.field_qualifications') }}</label>
                                    <textarea id="qualifications-{{ $code }}" name="translations[{{ $code }}][qualifications]" rows="5" class="form-control @error("translations.$code.qualifications") is-invalid @enderror">{{ $qualifications }}</textarea>
                                    <div class="form-text">{{ __('admin/careers.qualifications_hint') }}</div>
                                    @error("translations.$code.qualifications")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Dates, stage, family --}}
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label" for="category_id">{{ __('admin/careers.field_category') }}</label>
                            <select id="category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected((int) old('category_id', $job->category_id) === $category->id)>{{ $category->labelFor(app()->getLocale()) }}</option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="vacancies">{{ __('admin/careers.field_vacancies') }}</label>
                            <input type="number" id="vacancies" name="vacancies" min="1" max="999" class="form-control @error('vacancies') is-invalid @enderror" value="{{ old('vacancies', $job->vacancies) }}">
                            @error('vacancies')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @foreach (['start_on' => 'field_start', 'deadline_on' => 'field_deadline', 'updated_on' => 'field_updated'] as $field => $label)
                            <div class="mb-3">
                                <label class="form-label" for="{{ $field }}">{{ __("admin/careers.{$label}") }}</label>
                                <input type="date" id="{{ $field }}" name="{{ $field }}" class="form-control @error($field) is-invalid @enderror" value="{{ old($field, $job->{$field}?->format('Y-m-d')) }}">
                                @error($field)
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endforeach
                        <div class="mb-3">
                            <label class="form-label" for="stage">{{ __('admin/careers.field_stage') }}</label>
                            <select id="stage" name="stage" class="form-select @error('stage') is-invalid @enderror">
                                @foreach (\App\Models\CareerJob::STAGES as $stage)
                                    <option value="{{ $stage }}" @selected(old('stage', $job->stage) === $stage)>{{ __("admin/careers.stage_{$stage}") }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">{{ __('admin/careers.stage_hint') }}</div>
                            @error('stage')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_published" value="1" id="is_published" @checked(old('is_published', $editing ? $job->is_published : true))>
                            <label class="form-check-label" for="is_published">{{ __('admin/careers.field_published') }}</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> {{ __('admin/common.save') }}</button>
            <a href="{{ route('admin.careers.jobs.index') }}" class="btn btn-outline-secondary">{{ __('admin/common.back') }}</a>
        </div>
    </form>

    {{-- Documents --}}
    <div class="card mt-4" id="documents">
        <div class="card-header"><strong>{{ __('admin/careers.documents') }}</strong></div>
        @if (! $editing)
            <div class="card-body text-muted">{{ __('admin/careers.doc_save_first') }}</div>
        @else
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <tbody>
                        @forelse ($job->documents as $document)
                            <tr>
                                <td style="width: 100px;">
                                    <div class="btn-group btn-group-sm">
                                        @foreach (['up' => 'bi-arrow-up', 'down' => 'bi-arrow-down'] as $direction => $icon)
                                            <form method="POST" action="{{ route('admin.careers.documents.move', $document) }}">
                                                @csrf
                                                <input type="hidden" name="direction" value="{{ $direction }}">
                                                <button type="submit" class="btn btn-outline-secondary" title="{{ __("admin/careers.move_{$direction}") }}" @disabled(($direction === 'up' && $loop->parent->first) || ($direction === 'down' && $loop->parent->last))><i class="bi {{ $icon }}"></i></button>
                                            </form>
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ $document->url }}" target="_blank" rel="noopener">{{ $document->labelFor(app()->getLocale(), $defaultLocale) }}</a>
                                    @if ($document->is_notice)
                                        <span class="badge text-bg-primary ms-1">{{ __('admin/careers.doc_notice') }}</span>
                                    @endif
                                    @if ($document->size_mb)
                                        <div class="small text-muted">PDF · {{ $document->size_mb }} MB</div>
                                    @endif
                                </td>
                                <td class="text-end" style="width: 70px;">
                                    <form method="POST" action="{{ route('admin.careers.documents.destroy', $document) }}" onsubmit="return confirm(@js(__('admin/careers.confirm_delete_doc')));">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td class="text-center text-muted py-3">{{ __('admin/careers.doc_none') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <form method="POST" action="{{ route('admin.careers.jobs.documents.store', $job) }}" enctype="multipart/form-data" class="card-body border-top">
                @csrf
                <h6 class="mb-3">{{ __('admin/careers.add_document') }}</h6>
                <div class="row g-3">
                    @foreach ($languages as $language)
                        <div class="col-md-6">
                            <label class="form-label" for="doc-label-{{ $language->code }}">{{ __('admin/careers.doc_label') }} ({{ $language->native_name }})@if ($language->code === $defaultLocale) <span class="text-danger">*</span>@endif</label>
                            <input type="text" id="doc-label-{{ $language->code }}" name="labels[{{ $language->code }}]" maxlength="255" class="form-control @error("labels.{$language->code}") is-invalid @enderror" value="{{ old("labels.{$language->code}") }}">
                            @error("labels.{$language->code}")
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endforeach
                    <div class="col-md-6">
                        <label class="form-label" for="doc-file">{{ __('admin/careers.doc_file') }}</label>
                        <input type="file" id="doc-file" name="file" accept="application/pdf" class="form-control @error('file') is-invalid @enderror">
                        <div class="form-text">{{ __('admin/careers.doc_hint', ['size' => (int) (config('careers.max_document_kb') / 1024)]) }}</div>
                        @error('file')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="doc-url">{{ __('admin/careers.doc_url') }}</label>
                        <input type="url" id="doc-url" name="external_url" maxlength="1000" placeholder="https://" class="form-control @error('external_url') is-invalid @enderror" value="{{ old('external_url') }}">
                        @error('external_url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_notice" value="1" id="doc-notice" @checked(old('is_notice'))>
                            <label class="form-check-label" for="doc-notice">{{ __('admin/careers.doc_notice') }}</label>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-outline-primary mt-3"><i class="bi bi-plus-lg me-1"></i> {{ __('admin/careers.add_document') }}</button>
            </form>
        @endif
    </div>
@endsection
