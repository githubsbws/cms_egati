@php $translation = $post->translation(app()->getLocale()); @endphp
<a href="{{ web_url('news/'.$translation->slug) }}" class="nr-card {{ $style ?? '' }}" style="--nr-accent:{{ $post->category?->accent_color ?? '#0756a0' }}">
    <div class="nr-card-image">
        <img src="{{ $post->cover_url }}" alt="" loading="lazy">
    </div>
    <div class="nr-card-copy">
        <div class="nr-card-meta">
            <span class="nr-category">{{ $post->category?->nameFor(app()->getLocale()) }}</span>
            @include('web.partials.date-chip', ['date' => $post->published_at, 'class' => 'nr-date-chip'])
        </div>
        <h3>{{ $translation->title }}</h3>
        <p>{{ \Illuminate\Support\Str::limit($translation->excerpt, 180) }}</p>
        <span class="nr-card-action">{{ __web('news.read_story') }}<span aria-hidden="true">↗</span></span>
    </div>
</a>