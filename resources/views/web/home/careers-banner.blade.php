{{-- Home: careers banner --}}
<section class="home-careers-section" aria-label="{{ __web('careers.careers_at_egati') }}">
  <a class="home-careers-banner" href="{{ web_url('careers') }}">
    <span class="home-careers-art" aria-hidden="true">
      <svg viewBox="0 0 1600 260" preserveAspectRatio="none" fill="none" focusable="false">
        <defs>
          <linearGradient id="career-banner-sky" x1="0" y1="0" x2="1600" y2="260" gradientUnits="userSpaceOnUse">
            <stop stop-color="#fff5c8"/>
            <stop offset=".28" stop-color="#fff"/>
            <stop offset=".72" stop-color="#f7fbff"/>
            <stop offset="1" stop-color="#dcecff"/>
          </linearGradient>
          <linearGradient id="career-banner-wave" x1="0" y1="250" x2="1600" y2="140" gradientUnits="userSpaceOnUse">
            <stop stop-color="#0754a0"/>
            <stop offset=".55" stop-color="#2f77bf"/>
            <stop offset="1" stop-color="#06477e"/>
          </linearGradient>
          <filter id="career-banner-glow" x="-100%" y="-100%" width="300%" height="300%">
            <feGaussianBlur stdDeviation="8"/>
          </filter>
        </defs>
        <rect width="1600" height="260" fill="url(#career-banner-sky)"/>
        <circle cx="113" cy="42" r="82" fill="#ffd32d" opacity=".25" filter="url(#career-banner-glow)"/>
        <path d="M-40 210C240 135 390 288 710 220S1190 126 1640 196V280H-40Z" fill="#dcecff"/>
        <path d="M-40 229C230 165 460 294 760 225S1220 126 1640 188V280H-40Z" fill="#87b9e8" opacity=".7"/>
        <path d="M-40 244C240 184 510 300 790 244S1230 139 1640 194V280H-40Z" fill="url(#career-banner-wave)"/>
        <path d="M-20 154C340 78 510 252 850 178S1280 70 1620 129" stroke="#1768ae" stroke-width="2" opacity=".65"/>
        <path d="M-20 137C330 63 515 234 850 161S1280 55 1620 113" stroke="#fff" stroke-width="2" opacity=".9"/>
        <g class="home-careers-dots" fill="#0754a0">
          <circle cx="256" cy="102" r="4"/>
          <circle cx="276" cy="112" r="4"/>
          <circle cx="296" cy="122" r="4"/>
          <circle cx="276" cy="92" r="4"/>
          <circle cx="296" cy="102" r="4"/>
          <circle cx="316" cy="112" r="4"/>
        </g>
        <g class="home-careers-spark">
          <circle cx="1450" cy="43" r="5" fill="#fff"/>
          <circle cx="1450" cy="43" r="18" fill="#fff" opacity=".22"/>
        </g>
      </svg>
    </span>
    <span class="home-careers-copy"><small>EGATi CAREERS</small><strong>{!! __web('careers.grow_with_us') !!}</strong><em>{{ __web('careers.build_your_future_in_international') }}</em></span>
    <span class="home-careers-brand" aria-hidden="true"><span class="home-careers-logo-ring"><img src="{{ web_media('careers.logo', 'logo.png') }}" alt=""></span></span>
    <span class="home-careers-action">{{ __web('careers.explore_careers') }} <i aria-hidden="true">→</i></span>
  </a>
</section>
