@extends('admin.layouts.mainlayout')

@php
    $editing = $group->exists;
    $pageTitle = $editing ? __('admin/organization.edit_group') : __('admin/organization.add_group');
    $sectionTitle = __('admin/menuleft.org_sub.'.($section === 'management' ? 'management_team' : 'director_board'));
@endphp

@section('title', $pageTitle)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">{{ __('admin/menuleft.home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route("admin.organization.{$section}.index") }}">{{ $sectionTitle }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $pageTitle }}</li>
@endsection

@section('content')
    @include('admin.layouts.partials.alerts')

    <form method="POST" action="{{ $editing ? route("admin.organization.{$section}.groups.update", $group) : route("admin.organization.{$section}.groups.store") }}" enctype="multipart/form-data">
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <div class="card mx-auto" style="max-width: 720px;">
            <div class="card-body">
                {{-- One name per language; the default language is required --}}
                @foreach ($languages as $language)
                    @php
                        $code = $language->code;
                        $current = $group->translations->firstWhere('locale', $code)?->name;
                    @endphp
                    <div class="mb-3">
                        <label class="form-label" for="name-{{ $code }}">
                            {{ __('admin/organization.group_name') }} ({{ $language->native_name }})
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

                <div class="mb-3">
                    <label class="form-label" for="sort_order">{{ __('admin/organization.group_order') }}</label>
                    <input type="number" id="sort_order" name="sort_order" min="0" max="999" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', $group->sort_order) }}">
                    @error('sort_order')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                @if ($group->type === 'committee')
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $group->is_active))>
                        <label class="form-check-label" for="is_active">{{ __('admin/organization.group_active') }}</label>
                    </div>
                @endif

                <div class="mb-0">
                    <label class="form-label" for="document">{{ __('admin/organization.group_doc') }}</label>
                    @if ($group->document_path)
                        <div class="mb-2">
                            <a href="{{ web_file_url($group->document_path) }}" target="_blank" rel="noopener"><i class="bi bi-file-earmark-pdf me-1"></i>{{ __('admin/organization.view_doc') }}</a>
                            <div class="form-check mt-1">
                                <input class="form-check-input" type="checkbox" name="remove_document" value="1" id="remove_document">
                                <label class="form-check-label" for="remove_document">{{ __('admin/organization.remove_doc') }}</label>
                            </div>
                        </div>
                    @endif
                    <input type="file" id="document" name="document" accept="application/pdf" class="form-control @error('document') is-invalid @enderror">
                    <div class="form-text">{{ __('admin/organization.group_doc_hint') }}</div>
                    @error('document')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="card-footer d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> {{ __('admin/common.save') }}</button>
                <a href="{{ route("admin.organization.{$section}.index") }}" class="btn btn-outline-secondary">{{ __('admin/common.back') }}</a>
            </div>
        </div>
    </form>
@endsection