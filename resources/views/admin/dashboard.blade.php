@extends('admin.layouts.mainlayout')

@section('title', __('admin/menuleft.home'))

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <p class="mb-0">
                        {{ __('admin/common.dashboard_intro') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
