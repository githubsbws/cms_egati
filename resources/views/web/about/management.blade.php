@extends('web.layouts.mainlayout')

@php $title = __web('about.page_management'); @endphp

@section('title', $title.' | '.__web('site.title'))
@section('body_class', 'about-document')

@section('content')
    <div class="about-page ac-page">
        @include('web.about.header', ['title' => $title])

        <section class="ac-board-section ac-management-section">
            <div class="ac-panel-heading">
                <div>
                    <span class="ac-kicker">MANAGEMENT TEAM</span>
                    <h2>{{ __web('about.our_management_team') }}</h2>
                </div>
            </div>

            @include('web.about.people-grid', ['people' => $people, 'label' => __web('about.management_team_in_listed_order'), 'class' => 'ac-management-grid'])
        </section>
    </div>
@endsection