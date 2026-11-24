@extends('admin.layouts.mainlayout')

@section('title', __('admin/menuleft.org_sub.orgchart'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">{{ __('admin/menuleft.home') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('admin/menuleft.org_sub.orgchart') }}</li>
@endsection

@section('content')
    @include('admin.layouts.partials.alerts')

    <p class="text-muted">{{ __('admin/organization.chart_intro') }}</p>

    <div class="row g-3">
        @foreach ($languages as $language)
            @php
                $chart = $charts->get($language->code);
                $usesDefault = ! $chart && $charts->has($defaultLocale);
            @endphp
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header"><strong>{{ $language->native_name }}</strong> <span class="text-muted small">({{ $language->code }})</span></div>
                    <div class="card-body">
                        @if ($chart)
                            <img src="{{ web_file_url($chart->image_path) }}" alt="" class="img-fluid border mb-3">
                        @elseif ($usesDefault)
                            <p class="text-muted">{{ __('admin/organization.chart_uses_default') }}</p>
                        @else
                            <p class="text-muted">{{ __('admin/organization.chart_none') }}</p>
                        @endif

                        <form method="POST" action="{{ route('admin.organization.chart.store', $language) }}" enctype="multipart/form-data">
                            @csrf
                            <label class="form-label" for="image-{{ $language->code }}">{{ __('admin/organization.chart_upload') }}</label>
                            <input type="file" id="image-{{ $language->code }}" name="image" accept="image/png,image/jpeg,image/webp" class="form-control">
                            <div class="form-text mb-2">{{ __('admin/organization.chart_file_hint') }}</div>
                            <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-upload me-1"></i> {{ __('admin/organization.chart_upload') }}</button>
                        </form>

                        @if ($chart)
                            <form method="POST" action="{{ route('admin.organization.chart.destroy', $language) }}" onsubmit="return confirm(@js(__('admin/common.confirm_delete_message')));" class="mt-2">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash me-1"></i> {{ __('admin/common.delete') }}</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection