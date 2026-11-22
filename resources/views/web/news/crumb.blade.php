{{-- Breadcrumb: Home / Newsroom [/ $label] --}}
<div class="nr-crumb">
    <a href="{{ web_url('') }}">{{ __web('news.home') }}</a>
    <span>/</span>
    <a href="{{ web_url('news') }}">{{ __web('news.newsroom') }}</a>
    @isset($label)
        <span>/</span>
        <span>{{ $label }}</span>
    @endisset
</div>