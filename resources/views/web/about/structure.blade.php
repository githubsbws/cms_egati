@extends('web.layouts.mainlayout')

@php $title = __web('about.page_structure'); @endphp

@section('title', $title.' | '.__web('site.title'))
@section('body_class', 'about-document')

@section('content')
    <div class="about-page ac-page">
        @include('web.about.header', ['title' => $title, 'lead' => __web('about.the_company_organisation_chart_published')])

        <section class="ac-chart-section">
            <div class="ac-chart-toolbar">
                <div>
                    <span class="ac-kicker">ORGANIZATION CHART</span>
                    <h2>{{ __web('about.egati_organisation_chart') }}</h2>
                </div>
                <div class="ac-zoom-tools">
                    <button type="button" data-ac-zoom="out" aria-label="{{ __web('about.zoom_out') }}">−</button>
                    <output id="ac-zoom-value" aria-live="polite">100%</output>
                    <button type="button" data-ac-zoom="in" aria-label="{{ __web('about.zoom_in') }}">+</button>
                    <button type="button" data-ac-zoom="reset">{{ __web('about.fit') }}</button>
                </div>
            </div>

            @if ($chart)
                <div class="ac-chart-scroll" tabindex="0" role="region" aria-label="{{ __web('about.organisation_chart_scroll_to_explore') }}">
                    <img id="ac-organization-image" src="{{ $chart }}" alt="{{ $title }}">
                </div>
                <div class="ac-chart-footer">
                    <p>{{ __web('about.zoom_in_to_read_the') }}</p>
                    <a href="{{ $chart }}" target="_blank" rel="noopener">{{ __web('about.open_original_image') }} ↗</a>
                </div>
            @endif
        </section>
    </div>
@endsection