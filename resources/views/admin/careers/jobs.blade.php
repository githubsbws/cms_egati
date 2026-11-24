@extends('admin.layouts.mainlayout')

@php
    $pageTitle = __('admin/menuleft.work_us_sub.career_jobs');
    $locale = app()->getLocale();
@endphp

@section('title', $pageTitle)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">{{ __('admin/menuleft.home') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $pageTitle }}</li>
@endsection

@section('content')
    @include('admin.layouts.partials.alerts')

    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('admin.careers.jobs.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> {{ __('admin/careers.add_job') }}</a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>{{ __('admin/careers.col_position') }}</th>
                        <th>{{ __('admin/careers.col_category') }}</th>
                        <th>{{ __('admin/careers.col_dates') }}</th>
                        <th style="width: 190px;">{{ __('admin/careers.col_status') }}</th>
                        <th style="width: 110px;">{{ __('admin/careers.col_documents') }}</th>
                        <th style="width: 120px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($jobs as $job)
                        @php
                            $status = $job->status();
                            $statusLabel = match ($status) {
                                'assessment' => __('admin/careers.stage_assessment'),
                                'results' => __('admin/careers.stage_results'),
                                default => __("admin/careers.status_{$status}"),
                            };
                            $badge = ['open' => 'text-bg-success', 'upcoming' => 'text-bg-info', 'assessment' => 'text-bg-primary', 'results' => 'text-bg-warning', 'closed' => 'text-bg-secondary'][$status];
                        @endphp
                        <tr>
                            <td>
                                {{ $job->translationFor($locale, $defaultLocale)?->title }}
                                @unless ($job->is_published)
                                    <span class="badge text-bg-light border ms-1">{{ __('admin/common.inactive') }}</span>
                                @endunless
                            </td>
                            <td>{{ $job->category?->labelFor($locale) }}</td>
                            <td class="small text-nowrap">{{ $job->start_on->format('d/m/Y') }} - {{ $job->deadline_on->format('d/m/Y') }}</td>
                            <td><span class="badge {{ $badge }}">{{ $statusLabel }}</span></td>
                            <td>{{ $job->documents_count }}</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('admin.careers.jobs.edit', $job) }}" class="btn btn-sm btn-warning" title="{{ __('admin/common.edit') }}"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('admin.careers.jobs.destroy', $job) }}" class="d-inline" onsubmit="return confirm(@js(__('admin/common.confirm_delete_message')));">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="{{ __('admin/common.delete') }}"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">{{ __('admin/common.no_data') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
