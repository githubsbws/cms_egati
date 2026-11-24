{{-- Publications header: breadcrumb, title, symbol and the category tabs. $type is null on the overview. --}}
@php
    $types = config('publications.types');
    $meta = $type ? $types[$type] : null;
@endphp
<section class="pub-header ac-page-header {{ $type ? 'pub-header--compact' : '' }}">
    <div class="pub-crumb">
        <a href="{{ web_url('') }}">{{ __web('documents.home') }}</a><span>/</span><a href="{{ web_url('documents') }}">{{ __web('documents.publications') }}</a>
        @if ($type)<span>/</span><span>{{ __web($meta['title']) }}</span>@endif
    </div>
    <div class="pub-header-row">
        <div>
            <span class="pub-eyebrow"><i></i>{{ $meta ? $meta['tag'] : 'EGATi PUBLICATIONS' }}</span>
            <h1>{!! $meta ? __web($meta['title']) : __web('documents.shared_information_growing_confidence_2') !!}</h1>
            <p>{!! $meta ? __web($meta['description']) : __web('documents.the_egati_publication_hub_find_the') !!}</p>
        </div>
        @if ($type)
            <div class="pub-header-symbol" style="--pub-accent:{{ $meta['color'] }}">@include('web.documents.icon', ['name' => $type])</div>
        @else
            @include('web.documents.art')
        @endif
    </div>
    <nav class="pub-tabs" aria-label="{{ __web('documents.publication_categories') }}">
        <a href="{{ web_url('documents') }}" @if (! $type) aria-current="page" @endif>{{ __web('documents.overview') }}</a>
        @foreach ($types as $key => $item)
            <a href="{{ web_url('documents/'.$key) }}" @if ($type === $key) aria-current="page" @endif>@include('web.documents.icon', ['name' => $key]){{ __web($item['title']) }}</a>
        @endforeach
    </nav>
</section>
