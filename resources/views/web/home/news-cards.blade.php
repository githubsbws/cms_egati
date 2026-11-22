{{-- Home page news block: first card is the lead, next 2 secondary, rest compact --}}
<div class="news-cards news-editorial-grid">
    @foreach ($latestNews as $post)
        @php
            $translation = $post->translation(app()->getLocale());
            $size = $loop->first ? 'news-card--lead' : ($loop->index < 3 ? 'news-card--secondary' : 'news-card--compact');
            $accent = ['#1768ae', '#147d75', '#7352aa'][$loop->index % 3];
        @endphp
        <a class="news-card {{ $size }}" href="{{ web_url('news/'.$translation->slug) }}" aria-labelledby="news-title-{{ $loop->index }}" style="--news-accent:{{ $accent }}">
            <div class="news-card-image">
                @if ($loop->first)
                    <span class="news-lead-label">{{ __web('homenews.featured_story') }}</span>
                @endif
                <img src="{{ $post->cover_url }}" loading="lazy" alt="{{ $translation->title }}">
            </div>
            <div class="news-card-copy">
                <div class="news-card-meta">
                    <span class="news-card-category">{{ $post->category?->nameFor(app()->getLocale()) }}</span>
                    @include('web.partials.date-chip', ['date' => $post->published_at])
                </div>
                <h3 id="news-title-{{ $loop->index }}">{{ $translation->title }}</h3>
                <p>{{ \Illuminate\Support\Str::limit($translation->excerpt, 160) }}</p>
                <span class="card-action"><span>{{ __web('homenews.read_news') }}</span><span aria-hidden="true">↗</span></span>
            </div>
        </a>
    @endforeach
</div>