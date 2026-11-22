<div class="nav-about-item">
  <a class="nav-about-link " href="{{ web_url('about') }}">{{ __web('nav.about_us') }}</a>
  <button class="nav-about-trigger" type="button" aria-label="{{ __web('nav.open_about_submenu') }}" aria-expanded="false" aria-controls="nav-about-menu">
    <span aria-hidden="true">⌄</span>
  </button>
  <div class="nav-about-menu" id="nav-about-menu" aria-label="{{ __web('nav.about_us') }}">
    <div class="nav-about-groups">
      <div class="nav-about-group">
        <a href="{{ web_url('about') }}" class="nav-about-group-title" >{{ __web('nav.about_egati') }}</a>
      </div>
      <div class="nav-about-group">
        <a href="{{ web_url('about/board') }}" class="nav-about-group-title" >{{ __web('nav.board_of_directors') }}</a>
      </div>
      <div class="nav-about-group">
        <a href="{{ web_url('about/management') }}" class="nav-about-group-title" >{{ __web('nav.management_team') }}</a>
      </div>
      <div class="nav-about-group">
        <a href="{{ web_url('about/structure') }}" class="nav-about-group-title" >{{ __web('nav.company_structure') }}</a>
      </div>
      <div class="nav-about-group">
        <a href="{{ web_url('about/csr') }}" class="nav-about-group-title" >{{ __web('nav.social_responsibility') }}</a>
      </div>
      <div class="nav-about-group">
        <a href="{{ web_url('about/governance') }}" class="nav-about-group-title" >{{ __web('nav.corporate_governance') }}</a>
      </div>
    </div>
  </div>
</div>
<a href="{{ web_url('business') }}" class="">{{ __web('nav.our_business') }}</a>
<div class="nr-nav-item">
  <a class="" href="{{ web_url('news') }}">{{ __web('nav.news') }}</a>
  <button class="nr-nav-trigger" type="button" aria-label="{{ __web('nav.open_news_submenu') }}" aria-expanded="false" aria-controls="nr-nav-menu">⌄</button>
  <div class="nr-nav-menu" id="nr-nav-menu">
    <span>EGATi NEWSROOM</span>
    <a href="{{ web_url('news') }}">
      <i aria-hidden="true">▤</i>
      <div>
        <b>{{ __web('nav.news_activities') }}</b>
        <small>{{ __web('nav.updates_from_across_egati') }}</small>
      </div>
      <em aria-hidden="true">↗</em>
    </a>
    <a href="{{ web_url('news/videos') }}">
      <i aria-hidden="true">▷</i>
      <div>
        <b>{{ __web('nav.corporate_video') }}</b>
        <small>{{ __web('nav.watch_our_story_unfold') }}</small>
      </div>
      <em aria-hidden="true">↗</em>
    </a>
  </div>
</div>
<div class="pub-nav-item">
  <a href="{{ web_url('documents') }}" class="">{{ __web('nav.publications') }}</a>
  <button class="pub-nav-trigger" type="button" aria-expanded="false" aria-controls="pub-nav-menu" aria-label="{{ __web('nav.open_publications_submenu') }}">⌄</button>
  <div class="pub-nav-menu" id="pub-nav-menu">
    <span>EGATi PUBLICATIONS</span>
    <a href="{{ web_url('documents/credit') }}" >
      <i style="color:#0754a0">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <path d="m12 3 8 3v5c0 5-5 9-8 11-3-2-8-6-8-11V6z"/>
          <path d="m8 12 3 3 5-6"/>
        </svg>
      </i>
      <div>
        <b>{{ __web('nav.credit_ratings') }}</b>
        <small>CREDIT RATINGS</small>
      </div>
      <em aria-hidden="true">↗</em>
    </a>
    <a href="{{ web_url('documents/finance') }}" >
      <i style="color:#007e81">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <rect x="4" y="3" width="16" height="19" rx="2"/>
          <path d="M8 7h8M8 11h2m4 0h2M8 15h2m4 0h2M8 19h2m4 0h2"/>
        </svg>
      </i>
      <div>
        <b>{{ __web('nav.financial_statements') }}</b>
        <small>FINANCIAL STATEMENTS</small>
      </div>
      <em aria-hidden="true">↗</em>
    </a>
    <a href="{{ web_url('documents/annual') }}" >
      <i style="color:#90512f">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <path d="M12 6c-3-2-6-3-10-2v15c4-1 7 0 10 2 3-2 6-3 10-2V4c-4-1-7 0-10 2v15"/>
        </svg>
      </i>
      <div>
        <b>{{ __web('nav.annual_reports') }}</b>
        <small>ANNUAL REPORTS</small>
      </div>
      <em aria-hidden="true">↗</em>
    </a>
  </div>
</div>
<a href="{{ web_url('procurement') }}" class="">{{ __web('nav.procurement') }}</a>
<a href="{{ web_url('careers') }}" class="">{{ __web('nav.careers') }}</a>
<a href="{{ web_url('contact') }}" class="">{{ __web('nav.contact') }}</a>
