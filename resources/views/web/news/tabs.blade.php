{{-- Newsroom tabs: news list / corporate video. Pass 'video' => true on the video page. --}}
<nav class="nr-tabs" aria-label="{{ __web('news.newsroom_sections') }}">
    <a href="{{ web_url('news') }}" @if (! ($video ?? false)) aria-current="page" @endif><span aria-hidden="true">▤</span>{{ __web('news.news_activities') }}</a>
    <a href="{{ web_url('news/videos') }}" @if ($video ?? false) aria-current="page" @endif><span aria-hidden="true">▷</span>{{ __web('news.corporate_video') }}</a>
</nav>