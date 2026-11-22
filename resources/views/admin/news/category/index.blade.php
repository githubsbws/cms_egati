@extends('admin.layouts.mainlayout')

@section('title', __('admin/menuleft.news_sub.news_category'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">{{ __('admin/menuleft.home') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('admin/menuleft.news_sub.news_category') }}</li>
@endsection

@section('content')
    @include('admin.layouts.partials.alerts')

    <div class="card">
        <div class="card-body">
            <div class="mb-3">
                <a href="{{ route('admin.news.category.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>
                    {{ __('admin/news.add_category') }}
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 70px;">{{ __('admin/news.category_sort') }}</th>
                            <th>{{ __('admin/news.category_name') }}</th>
                            <th style="width: 90px;">{{ __('admin/news.category_color') }}</th>
                            <th style="width: 110px;">{{ __('admin/news.category_posts') }}</th>
                            <th style="width: 110px;">{{ __('admin/common.manage') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $category)
                            <tr>
                                <td>{{ $category->sort_order }}</td>
                                <td>
                                    {{ $category->nameFor($defaultLocale) }}
                                    <span class="text-muted small ms-1">
                                        @foreach ($category->translations->where('locale', '!=', $defaultLocale) as $translation)
                                            {{ $translation->name }}@if (! $loop->last), @endif
                                        @endforeach
                                    </span>
                                </td>
                                <td><span class="badge" style="background: {{ $category->accent_color }};">{{ $category->accent_color }}</span></td>
                                <td>{{ $category->posts_count }}</td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('admin.news.category.edit', $category) }}" class="btn btn-sm btn-warning" title="{{ __('admin/common.edit') }}"><i class="bi bi-pencil"></i></a>
                                        <form method="POST" action="{{ route('admin.news.category.destroy', $category) }}" onsubmit="return confirm(@js(__('admin/news.confirm_delete_category')));">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="{{ __('admin/common.delete') }}"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">{{ __('admin/common.no_data') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection