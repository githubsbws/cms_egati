@extends('admin.layouts.mainlayout')

@php
    $editing = $member->exists;
    $pageTitle = $editing ? __('admin/organization.edit_member') : __('admin/organization.add_member');
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

    <form method="POST" action="{{ $editing ? route("admin.organization.{$section}.members.update", $member) : route("admin.organization.{$section}.members.store") }}" enctype="multipart/form-data">
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <div class="row g-3">
            {{-- Left: name and position per language --}}
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" role="tablist">
                            @foreach ($languages as $language)
                                @php
                                    $tabHasError = $errors->has("translations.{$language->code}.name") || $errors->has("translations.{$language->code}.role");
                                @endphp
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
                                $translation = $member->translation($code);
                            @endphp
                            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="lang-{{ $code }}" role="tabpanel">
                                <p class="text-muted small">{{ $code === $defaultLocale ? __('admin/organization.language_required') : __('admin/organization.language_optional') }}</p>
                                <div class="mb-3">
                                    <label class="form-label" for="name-{{ $code }}">{{ __('admin/organization.field_name') }}</label>
                                    <input type="text" id="name-{{ $code }}" name="translations[{{ $code }}][name]" maxlength="255"
                                           class="form-control @error("translations.$code.name") is-invalid @enderror"
                                           value="{{ old("translations.$code.name", $translation?->name) }}">
                                    @error("translations.$code.name")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-0">
                                    <label class="form-label" for="role-{{ $code }}">{{ __('admin/organization.field_role') }}</label>
                                    <input type="text" id="role-{{ $code }}" name="translations[{{ $code }}][role]" maxlength="255"
                                           class="form-control @error("translations.$code.role") is-invalid @enderror"
                                           value="{{ old("translations.$code.role", $translation?->role) }}">
                                    @error("translations.$code.role")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            {{-- Right: group, position on the page, photo --}}
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label" for="group_id">{{ __('admin/organization.field_group') }}</label>
                            <select id="group_id" name="group_id" class="form-select @error('group_id') is-invalid @enderror">
                                @foreach ($groups as $group)
                                    <option value="{{ $group->id }}" @selected((int) old('group_id', $member->group_id) === $group->id)>{{ $group->nameFor(app()->getLocale(), $defaultLocale) }}</option>
                                @endforeach
                            </select>
                            @error('group_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $member->exists ? $member->is_active : true))>
                            <label class="form-check-label" for="is_active">{{ __('admin/organization.field_active') }}</label>
                        </div>

                        <div class="mb-0">
                            <label class="form-label" for="photo">{{ __('admin/organization.field_photo') }}</label>
                            @if ($member->photo_url)
                                <div class="mb-2">
                                    <img src="{{ $member->photo_url }}" alt="" class="img-thumbnail" style="max-height: 140px;">
                                    <div class="form-check mt-1">
                                        <input class="form-check-input" type="checkbox" name="remove_photo" value="1" id="remove_photo">
                                        <label class="form-check-label" for="remove_photo">{{ __('admin/organization.remove_photo') }}</label>
                                    </div>
                                </div>
                            @endif
                            <input type="file" id="photo" name="photo" accept="image/png,image/jpeg,image/webp" class="form-control @error('photo') is-invalid @enderror">
                            <div class="form-text">{{ __('admin/organization.photo_hint') }}</div>
                            @error('photo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> {{ __('admin/common.save') }}</button>
            <a href="{{ route("admin.organization.{$section}.index") }}" class="btn btn-outline-secondary">{{ __('admin/common.back') }}</a>
        </div>
    </form>
@endsection