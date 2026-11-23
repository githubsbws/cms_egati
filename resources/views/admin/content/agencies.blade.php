@extends('admin.layouts.mainlayout')

@php
    $pageTitle = __('admin/menuleft.content_sub.related_agencies');
@endphp

@section('title', $pageTitle)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">{{ __('admin/menuleft.home') }}</a></li>
    <li class="breadcrumb-item">{{ __('admin/menuleft.content_sub.content') }}</li>
    <li class="breadcrumb-item active" aria-current="page">{{ $pageTitle }}</li>
@endsection

@section('content')
    @include('admin.layouts.partials.alerts')

    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('admin.content.agencies.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> {{ __('admin/home_content.agency.add') }}</a>
    </div>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 110px;">{{ __('admin/home_content.agency.col_order') }}</th>
                        <th style="width: 100px;">{{ __('admin/home_content.agency.col_logo') }}</th>
                        <th>{{ __('admin/home_content.agency.name') }}</th>
                        <th>{{ __('admin/home_content.agency.url') }}</th>
                        <th style="width: 110px;"></th>
                        <th style="width: 260px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($agencies as $agency)
                        <tr>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    @foreach (['up' => 'bi-arrow-up', 'down' => 'bi-arrow-down'] as $direction => $icon)
                                        <form method="POST" action="{{ route('admin.content.agencies.move', $agency) }}">
                                            @csrf
                                            <input type="hidden" name="direction" value="{{ $direction }}">
                                            <button type="submit" class="btn btn-outline-secondary" title="{{ __("admin/home_content.agency.move_{$direction}") }}" @disabled(($direction === 'up' && $loop->parent->first) || ($direction === 'down' && $loop->parent->last))><i class="bi {{ $icon }}"></i></button>
                                        </form>
                                    @endforeach
                                </div>
                            </td>
                            <td>@if ($agency->logo_url)<img src="{{ $agency->logo_url }}" alt="" style="max-height: 40px; max-width: 80px;">@endif</td>
                            <td>{{ $agency->nameFor(app()->getLocale(), $defaultLocale) }}</td>
                            <td class="small"><a href="{{ $agency->url }}" target="_blank" rel="noopener">{{ $agency->url }}</a></td>
                            <td>
                                <span class="badge {{ $agency->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $agency->is_active ? __('admin/common.active') : __('admin/common.inactive') }}</span>
                            </td>
                            <td class="text-end text-nowrap">
                                <form method="POST" action="{{ route('admin.content.agencies.toggle', $agency) }}" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi {{ $agency->is_active ? 'bi-eye-slash' : 'bi-eye' }} me-1"></i>{{ $agency->is_active ? __('admin/home_content.agency.hide') : __('admin/home_content.agency.show') }}
                                    </button>
                                </form>
                                <a href="{{ route('admin.content.agencies.edit', $agency) }}" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('admin.content.agencies.destroy', $agency) }}" class="d-inline" onsubmit="return confirm(@js(__('admin/home_content.agency.confirm_delete')));">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">{{ __('admin/home_content.agency.empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
