@extends('admin.layouts.mainlayout')

@section('title', __('admin/language.edit_text_title').' - '.$language->native_name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">{{ __('admin/menuleft.home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.settings.language.index') }}">{{ __('admin/menuleft.set_sub.language') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $language->code }}.json</li>
@endsection

@section('content')
    @include('admin.layouts.partials.alerts')

    <div class="card mb-3">
        <div class="card-body">
            <p class="text-muted mb-3">{{ __('admin/language.editor_hint') }}</p>

            {{-- Edit the JSON text and save over the file --}}
            <form method="POST" action="{{ route('admin.settings.language.strings.save', $language) }}">
                @csrf
                @method('PUT')
                <textarea name="json" rows="28" spellcheck="false" class="form-control font-monospace @error('json') is-invalid @enderror"
                          style="font-size: 0.85rem; white-space: pre; overflow: auto;">{{ old('json', $json) }}</textarea>
                @error('json')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> {{ __('admin/language.save_json') }}</button>
                    <a href="{{ route('admin.settings.language.download', $language) }}" class="btn btn-outline-secondary"><i class="bi bi-download me-1"></i> {{ __('admin/language.download') }}</a>
                    <a href="{{ route('admin.settings.language.index') }}" class="btn btn-outline-secondary">{{ __('admin/language.back') }}</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Replace the file with an uploaded one --}}
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.settings.language.strings.upload', $language) }}" enctype="multipart/form-data" class="d-flex flex-wrap align-items-end gap-2">
                @csrf
                <div>
                    <label class="form-label" for="json_file">{{ __('admin/language.upload_replace') }}</label>
                    <input type="file" id="json_file" name="json_file" accept=".json,application/json" class="form-control @error('json_file') is-invalid @enderror">
                    @error('json_file')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-outline-primary"><i class="bi bi-upload me-1"></i> {{ __('admin/language.upload_replace') }}</button>
            </form>
        </div>
    </div>
@endsection