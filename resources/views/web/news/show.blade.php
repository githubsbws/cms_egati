@extends('web.layouts.mainlayout')

@section('title', $translation->title.' | '.__web('site.title'))
@section('description', \Illuminate\Support\Str::limit($translation->excerpt, 160))
@section('body_class', 'about-overview-color')

@section('content')
    @php
        $locale = app()->getLocale();
    @endphp

    <div class="nr-page nr-article-page">
        <div class="nr-article-top ac-page-header">
            @include('web.news.crumb', ['label' => __web('news.news_activities')])
            @include('web.news.tabs', ['video' => false])
        </div>

        <article>
            {{-- Heading: category, date, title, share --}}
            <section class="nr-article-heading">
                <div class="nr-article-meta">
                    <span class="nr-category" style="--nr-accent:{{ $post->category?->accent_color ?? '#0756a0' }}">{{ $post->category?->nameFor($locale) }}</span>
                    <time datetime="{{ $post->published_at->toDateString() }}">{{ \App\Helpers\WebDate::chip($post->published_at)['label'] }}</time>
                </div>
                <h1>{{ $translation->title }}</h1>
                <div class="nr-article-byline">
                    <span>EGAT INTERNATIONAL</span>
                    <button type="button" id="nr-share">{{ __web('news.copy_article_link') }} <span aria-hidden="true">↗</span></button>
                    <span id="nr-share-status" role="status"></span>
                </div>
            </section>

            <div class="nr-article-cover">
                <img src="{{ $post->cover_url }}" alt="{{ $translation->title }}">
                <span>{{ __web('news.news_activities') }} / EGATi</span>
            </div>

            <div class="nr-reading-layout">
                {{-- Body --}}
                <div class="nr-reading">
                    <div class="nr-reading-rule" aria-hidden="true"></div>
                    <div class="nr-prose">{!! $translation->content !!}</div>

                    {{-- Event photos --}}
                    @if ($post->images->isNotEmpty())
                        <div class="nr-gallery">
                            <h2>{{ __web('news.photo_gallery') }}</h2>
                            <div>
                                @foreach ($post->images as $image)
                                    <button type="button" data-nr-image="{{ $loop->index }}" aria-label="{{ __web('news.enlarge_image') }} {{ $loop->iteration }}"><img src="{{ $image->url }}" loading="lazy" alt="{{ __web('news.event_photo') }} {{ $loop->iteration }}"><span aria-hidden="true">＋</span></button>
                                @endforeach
                            </div>
                        </div>
                    @endif


                    <a href="{{ web_url('news') }}" class="nr-back">← {{ __web('news.back_to_news_activities') }}</a>
                </div>

                {{-- Related stories --}}
                <aside class="nr-reading-aside">
                    <span class="nr-kicker">KEEP EXPLORING</span>
                    <h2>{!! __web('news.more_stories_more_perspectives') !!}</h2>
                    @foreach ($related as $item)
                        @include('web.news.card', ['post' => $item, 'style' => 'nr-card--related'])
                    @endforeach
                    <a href="{{ web_url('news/videos') }}" class="nr-aside-video"><span aria-hidden="true">▷</span>{{ __web('news.corporate_presentation') }} ↗</a>
                </aside>
            </div>
        </article>
    </div>
@endsection