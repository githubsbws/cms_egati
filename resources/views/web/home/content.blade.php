{{-- Home page: hero video, then the sections below in order --}}
<section class="video-hero" aria-label="{{ __web('home.energy_beyond_borders') }}">
  <video id="hero-video" autoplay muted loop playsinline preload="auto" poster="{{ web_media('home.clean_energy_flood_mitigation_poster', 'clean-energy-flood-mitigation-poster.jpg') }}" aria-label="{{ __web('home.clean_energy_and_flood_mitigation') }}">
    <source src="{{ web_media('home.clean_energy_flood_mitigation', 'clean-energy-flood-mitigation.mp4') }}" type="video/mp4">
  </video>
  <div class="video-shade"></div>
  <div class="video-hero-copy">
    <div class="eyebrow">{{ __web('home.egat_international_energy_beyond_borders') }}</div>
    <h1>{!! __web('home.thailand_s_gateway_to_global') !!}</h1>
    <p>{!! __web('home.as_the_international_investment_arm') !!}</p>
    <div class="video-hero-buttons"><a class="video-primary" href="{{ web_url('business') }}">{{ __web('home.explore_our_business') }} <span>↗</span></a><a class="video-secondary" href="{{ web_url('about') }}">{{ __web('home.discover_egati') }} <span>↗</span></a></div>
  </div>
  <div class="video-hero-bottom">
    <a href="#quick-access" class="hero-scroll"><span>↓</span>{{ __web('home.scroll_to_explore') }}</a>
    <div class="hero-film-caption"><span>CLEAN ENERGY / FLOOD MITIGATION</span><strong>{{ __web('home.clean_energy_and_flood_mitigation_2') }}</strong><a href="https://www.youtube.com/watch?v=oBXLjTpzpbY" rel="noopener">{{ __web('home.watch_on_youtube') }} ↗</a></div>
  </div>
</section>
@include('web.home.careers-banner')@include('web.home.quick-access')
<section class="journal" id="home-news">
  <div class="home-news-artwork" aria-hidden="true">
    <svg viewBox="0 0 1600 1400" preserveAspectRatio="none" fill="none" focusable="false">
      <defs>
        <pattern id="news-editorial-grid" width="96" height="96" patternUnits="userSpaceOnUse">
          <path d="M0 12V0h12M84 96h12V84" stroke="#a1dcef" stroke-opacity=".15" stroke-width="1"/>
          <path d="M46 48h4M48 46v4" stroke="#7bcde4" stroke-opacity=".16"/>
        </pattern>
        <linearGradient id="news-network-gradient" x1="0" y1="1400" x2="1600" y2="0" gradientUnits="userSpaceOnUse">
          <stop stop-color="#62cbdc" stop-opacity=".3"/>
          <stop offset=".5" stop-color="#a4deef" stop-opacity=".12"/>
          <stop offset="1" stop-color="#53c3e9" stop-opacity=".5"/>
        </linearGradient>
      </defs>
      <rect width="1600" height="1400" fill="url(#news-editorial-grid)"/>
      <g stroke="url(#news-network-gradient)" stroke-width="1.2">
        <path d="M-80 170H240L450 380H920L1200 100H1680"/>
        <path d="M-80 500H180L430 750H1050L1300 500H1680"/>
        <path d="M-80 1100H340L600 840H1100L1370 1110H1680"/>
        <path d="M-80 1320H180L510 990H850L1210 1350H1680"/>
        <path d="M120-50V300L350 530V1000L110 1240V1450"/>
        <path d="M1480-50V260L1210 530V920L1490 1200V1450"/>
      </g>
      <g stroke-width="2.5" stroke-linecap="round">
        <path class="news-data-trace" d="M-80 170H240L450 380H920L1200 100H1680" stroke="#82e0ec" stroke-dasharray="65 1035"/>
        <path class="news-data-trace news-data-trace--gold" d="M-80 1100H340L600 840H1100L1370 1110H1680" stroke="#ffdc66" stroke-dasharray="48 1052"/>
        <path class="news-data-trace news-data-trace--vertical" d="M1480-50V260L1210 530V920L1490 1200V1450" stroke="#87d7f5" stroke-dasharray="45 1055"/>
      </g>
      <g class="news-editorial-frame news-editorial-frame--one" stroke="#8fdced" stroke-opacity=".38" stroke-width="1.4">
        <rect x="1200" y="30" width="280" height="205" rx="9"/>
        <path d="M1225 60h92v65h-92zm118 0h110m-110 18h83m-83 18h100m-218 55h228m-228 18h228m-228 18h160"/>
        <path d="M1185 75V15h60m250 175v60h-60" stroke="#ffdc66" stroke-opacity=".7" stroke-width="2.5"/>
      </g>
      <g class="news-editorial-frame news-editorial-frame--two" stroke="#88cce5" stroke-opacity=".3" stroke-width="1.4">
        <rect x="35" y="1050" width="285" height="215" rx="9"/>
        <path d="M60 1078h175m-175 19h220m-220 19h105m-105 26h98v91H60zm122 0h98m-98 22h98m-98 22h72m-72 22h98"/>
        <path d="M20 1100v-65h65m250 190v55h-60" stroke="#ffdc66" stroke-opacity=".6" stroke-width="2.5"/>
      </g>
      <g class="news-editorial-frame news-editorial-frame--three" stroke="#7bc9e2" stroke-opacity=".25" stroke-width="1.2">
        <rect x="-100" y="260" width="320" height="180" rx="8"/>
        <path d="M-65 290H180M-65 312H120M-65 334H180M-65 356H90M-65 378H180"/>
      </g>
      <g fill="#91deed">
        <rect class="news-network-node" x="234" y="164" width="12" height="12" rx="2"/>
        <rect class="news-network-node" x="1044" y="744" width="12" height="12" rx="2" style="animation-delay:-2s"/>
        <rect class="news-network-node" x="334" y="1094" width="12" height="12" rx="2" style="animation-delay:-4s"/>
        <rect class="news-network-node" x="1364" y="1104" width="12" height="12" rx="2" style="animation-delay:-6s"/>
      </g>
    </svg>
  </div>
  <div class="journal-header">
    <div>
      <h2>{{ __web('home.our_news') }}</h2>
      <p>{!! __web('home.discover_the_latest_energy_stories') !!}</p>
    </div>
    <a class="line-link" href="{{ web_url('news') }}">{{ __web('home.all_news') }} ↗</a>
  </div>
  @include('web.home.news-cards')
  <button class="home-news-motion-toggle" type="button" aria-pressed="false"><span aria-hidden="true">Ⅱ</span>{{ __web('home.pause_background_motion') }}</button>
</section>
@include('web.home.about-story')
<section class="portfolio" id="footprint">
  <div class="portfolio-art" aria-hidden="true">
    <svg class="portfolio-orbit" viewBox="0 0 700 700" fill="none" focusable="false">
      <g stroke="#2580bb">
        <circle cx="350" cy="350" r="210" stroke-width="1.4" opacity=".38"/>
        <circle cx="350" cy="350" r="250" stroke-width="1.5" opacity=".32"/>
        <circle cx="350" cy="350" r="292" stroke-width="1" opacity=".25"/>
      </g>
      <g class="portfolio-orbit-ring">
        <circle cx="350" cy="350" r="250" stroke="#e1ac00" stroke-width="2.5" stroke-dasharray="140 1431" stroke-linecap="round"/>
        <circle cx="350" cy="100" r="7" fill="#0c78b8"/>
        <circle cx="350" cy="600" r="5" fill="#e1ac00"/>
        <circle cx="100" cy="350" r="4" fill="#0c78b8"/>
      </g>
      <g class="portfolio-orbit-dots">
        <circle cx="350" cy="350" r="292" stroke="#1c76ad" stroke-width="2" stroke-dasharray="2 18" opacity=".6"/>
        <circle cx="350" cy="58" r="5" fill="#1c76ad"/>
      </g>
      <circle cx="350" cy="350" r="172" fill="#f1f7fc" opacity=".5"/>
      <path d="M329 346h42m-21-21v42" stroke="#17699f" stroke-width="2" opacity=".5"/>
    </svg>
    <svg class="portfolio-waves" viewBox="0 0 1200 480" fill="none" focusable="false">
      <g class="portfolio-wave-float" stroke-linecap="round">
        <path d="M-80 280C160 20 290 430 555 245S900 135 1250 380" stroke="#247bb4" stroke-width="1.7" opacity=".4"/>
        <path d="M-80 306C160 46 290 456 555 271S900 161 1250 406" stroke="#247bb4" stroke-width="1.5" opacity=".34"/>
        <path d="M-80 332C160 72 290 482 555 297S900 187 1250 432" stroke="#247bb4" stroke-width="1.3" opacity=".28"/>
        <path d="M-80 254C160-6 290 404 555 219S900 109 1250 354" stroke="#d7a500" stroke-width="2" opacity=".5"/>
        <path class="portfolio-flow" d="M-80 280C160 20 290 430 555 245S900 135 1250 380" stroke="#1273b3" stroke-width="3" stroke-dasharray="8 18" opacity=".75"/>
        <circle cx="555" cy="245" r="7" fill="#ffd32d" stroke="#b58a00" stroke-width="1"/>
        <circle cx="858" cy="190" r="5" fill="#1273b3" stroke="white" stroke-width="2"/>
      </g>
    </svg>
    <svg class="portfolio-spark portfolio-spark--one" viewBox="0 0 44 44" fill="none" focusable="false">
      <path d="M22 4v36M4 22h36" stroke="#d9a600" stroke-width="2"/>
      <circle cx="22" cy="22" r="10" stroke="#d9a600" stroke-width="1.2"/>
    </svg>
    <svg class="portfolio-spark portfolio-spark--two" viewBox="0 0 44 44" fill="none" focusable="false">
      <path d="m22 4 4 14 14 4-14 4-4 14-4-14-14-4 14-4Z" stroke="#1676b4" stroke-width="1.5"/>
      <circle cx="22" cy="22" r="3" fill="#1676b4"/>
    </svg>
    <svg class="portfolio-dot-grid" viewBox="0 0 100 80" fill="#1676b4" focusable="false">
      <circle cx="10" cy="10" r="2"/>
      <circle cx="29" cy="10" r="2"/>
      <circle cx="48" cy="10" r="2"/>
      <circle cx="67" cy="10" r="2"/>
      <circle cx="86" cy="10" r="2"/>
      <circle cx="10" cy="29" r="2"/>
      <circle cx="29" cy="29" r="2"/>
      <circle cx="48" cy="29" r="2"/>
      <circle cx="67" cy="29" r="2"/>
      <circle cx="86" cy="29" r="2"/>
      <circle cx="10" cy="48" r="2"/>
      <circle cx="29" cy="48" r="2"/>
      <circle cx="48" cy="48" r="2"/>
      <circle cx="67" cy="48" r="2"/>
      <circle cx="86" cy="48" r="2"/>
      <circle cx="10" cy="67" r="2"/>
      <circle cx="29" cy="67" r="2"/>
      <circle cx="48" cy="67" r="2"/>
      <circle cx="67" cy="67" r="2"/>
      <circle cx="86" cy="67" r="2"/>
    </svg>
  </div>
  <div class="portfolio-main">
    <div class="section-heading">
      <h2>{!! __web('home.different_energies_connected_opportunities') !!}</h2>
      <p>{!! __web('home.from_hydropower_to_solar_explore') !!}</p>
    </div>
    @include('web.home.footprint-cards')
  </div>
  <button class="portfolio-motion-toggle" id="portfolio-motion-toggle" type="button" hidden></button>
</section>
@include('web.home.presentation')@include('web.home.documents')@include('web.home.stakeholders')
