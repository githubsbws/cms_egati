@extends('admin.layouts.mainlayout')

@php
    $pageTitle = __('admin/menuleft.org_sub.'.($section === 'management' ? 'management_team' : 'director_board'));
@endphp

@section('title', $pageTitle)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">{{ __('admin/menuleft.home') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $pageTitle }}</li>
@endsection

@section('content')
    @include('admin.layouts.partials.alerts')

    {{-- Group tabs (board + committees) --}}
    @if ($groups->count() > 1 || $section === 'board')
        <div class="mb-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex flex-wrap gap-2">
                    @foreach ($groups as $group)
                        @php $isCurrent = $current && $group->id === $current->id; @endphp
                        <a href="{{ route("admin.organization.{$section}.index", ['group' => $group->code]) }}"
                           class="badge rounded-pill fs-6 fw-normal text-decoration-none px-3 py-2 {{ $isCurrent ? 'text-bg-primary' : 'bg-primary-subtle text-primary-emphasis' }}"
                           @if ($isCurrent) aria-current="page" @endif>
                            {{ $group->nameFor(app()->getLocale(), $defaultLocale) }}
                            <span class="badge rounded-pill ms-1 {{ $isCurrent ? 'text-bg-light' : 'text-bg-primary' }}">{{ $group->members_count }}</span>
                        </a>
                    @endforeach
                </div>
                @if ($section === 'board')
                    <a href="{{ route('admin.organization.board.groups.create') }}" class="btn btn-outline-primary">
                        <i class="bi bi-plus-lg me-1"></i> {{ __('admin/organization.add_group') }}
                    </a>
                @endif
            </div>
        </div>
    @endif
    @if ($current)
        <div class="card">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <strong>{{ $current->nameFor(app()->getLocale(), $defaultLocale) }}</strong>
                <div class="d-flex flex-wrap gap-2 ms-auto">
                    @if ($current->document_path)
                        <a href="{{ web_file_url($current->document_path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-file-earmark-pdf me-1"></i> {{ __('admin/organization.view_doc') }}
                        </a>
                    @endif
                    <a href="{{ route("admin.organization.{$section}.groups.edit", $current) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-gear me-1"></i> {{ __('admin/organization.edit_group') }}
                    </a>
                    @if ($current->type === 'committee')
                        <form method="POST" action="{{ route("admin.organization.{$section}.groups.destroy", $current) }}" onsubmit="return confirm(@js(__('admin/organization.confirm_delete_group')));">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1"></i> {{ __('admin/organization.delete_group') }}</button>
                        </form>
                    @endif
                    <a href="{{ route("admin.organization.{$section}.members.create", ['group' => $current->code]) }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> {{ __('admin/organization.add_member') }}
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 90px;">{{ __('admin/organization.col_photo') }}</th>
                                <th>{{ __('admin/organization.col_name') }}</th>
                                <th>{{ __('admin/organization.col_role') }}</th>
                                <th style="width: 120px;">{{ __('admin/organization.col_status') }}</th>
                                <th style="width: 110px;">{{ __('admin/common.manage') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($members as $member)
                                @php
                                    $translation = $member->translation(app()->getLocale()) ?? $member->translation($defaultLocale) ?? $member->translations->first();
                                @endphp
                                <tr>
                                    <td>
                                        @if ($member->photo_url)
                                            <img src="{{ $member->photo_url }}" alt="" class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                                        @endif
                                    </td>
                                    <td>{{ $translation?->name ?? '-' }}</td>
                                    <td>{{ $translation?->role ?? '-' }}</td>
                                    <td>
                                        @if ($member->is_active)
                                            <span class="badge text-bg-success">{{ __('admin/common.active') }}</span>
                                        @else
                                            <span class="badge text-bg-secondary">{{ __('admin/common.inactive') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route("admin.organization.{$section}.members.edit", $member) }}" class="btn btn-sm btn-warning" title="{{ __('admin/common.edit') }}"><i class="bi bi-pencil"></i></a>
                                            <form method="POST" action="{{ route("admin.organization.{$section}.members.destroy", $member) }}" onsubmit="return confirm(@js(__('admin/common.confirm_delete_message')));">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="{{ __('admin/common.delete') }}"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">{{ __('admin/organization.no_people') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
@endsection