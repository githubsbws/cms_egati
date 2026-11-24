{{-- About page header: breadcrumb + title. Params: $title, optional $lead --}}
<div class="ac-page-header">
    <div class="ac-breadcrumb">
        <a href="{{ web_url('') }}">{{ __web('about.home') }}</a>
        <span>/</span>
        <a href="{{ web_url('about') }}">{{ __web('about.about_egati_2') }}</a>
        <span>/</span>
        <span>{{ $title }}</span>
    </div>
    <div class="ac-title-row">
        <div>
            <span class="ac-kicker">EGAT INTERNATIONAL</span>
            <h1>{{ $title }}</h1>
        </div>
        @isset($lead)
            <p>{{ $lead }}</p>
        @endisset
    </div>
</div>