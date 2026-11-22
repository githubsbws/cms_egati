{{-- Home: publications hub --}}
<section class="knowledge knowledge-hub" id="home-documents" aria-labelledby="documents-title">
  <div class="knowledge-intro">
    <span class="knowledge-accent" aria-hidden="true"></span>
    <h2 id="documents-title">{!! __web('documents.shared_information_growing_confidence') !!}</h2>
    <p>{!! __web('documents.key_company_publications_information_to') !!}</p>
    <div class="knowledge-visual" aria-hidden="true">
      <svg viewBox="0 0 360 190" fill="none" focusable="false">
        <ellipse cx="172" cy="168" rx="128" ry="13" fill="#dce8f2" opacity=".65"/>
        <path d="M22 94c17-50 73-77 144-64s152 23 170 77" stroke="#b6d1e5" stroke-dasharray="3 7"/>
        <g class="knowledge-paper-back">
          <rect x="100" y="24" width="123" height="139" rx="12" transform="rotate(-11 100 24)" fill="#e5f0f8" stroke="#a9c9df"/>
          <path d="m110 49 67-13m-63 31 77-15m-73 34 51-10" stroke="#8fb5d0" stroke-width="3" stroke-linecap="round"/>
        </g>
        <g class="knowledge-paper">
          <rect x="128" y="18" width="130" height="149" rx="12" fill="white" stroke="#aac7dc"/>
          <path d="M140 18h106a12 12 0 0 1 12 12v11H128V30a12 12 0 0 1 12-12Z" fill="#064b85"/>
          <rect x="144" y="55" width="42" height="7" rx="3.5" fill="#07549a"/>
          <path d="M144 77h96M144 89h75M144 101h84" stroke="#c5d9e8" stroke-width="4" stroke-linecap="round"/>
          <rect x="145" y="129" width="15" height="20" rx="3" fill="#96bdd9"/>
          <rect x="168" y="119" width="15" height="30" rx="3" fill="#1468a4"/>
          <rect x="191" y="111" width="15" height="38" rx="3" fill="#ffd32d"/>
          <path d="M218 145h22" stroke="#b8d0e1" stroke-width="4" stroke-linecap="round"/>
        </g>
        <g class="knowledge-seal">
          <circle cx="265" cy="113" r="27" fill="#ffd32d"/>
          <circle cx="265" cy="113" r="19" stroke="#a07a00" stroke-opacity=".35"/>
          <path d="m254 113 8 8 14-16" stroke="#063968" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
        </g>
        <circle cx="55" cy="112" r="6" fill="#ffcf00"/>
        <circle cx="309" cy="47" r="5" fill="#0e629d"/>
        <path d="M73 44v12m-6-6h12" stroke="#176aa5" stroke-width="2" stroke-linecap="round"/>
      </svg>
    </div>
  </div>
  <div class="document-list knowledge-documents">
    <a class="document-card" href="{{ web_url('documents/annual') }}" aria-labelledby="document-title-0">
      <span class="document-icon" aria-hidden="true"><svg viewBox="0 0 34 34" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" focusable="false"><path d="M7 3h13l5 5v23H7zM20 3v7h7M12 24v-5M17 24v-9M22 24v-6"/></svg></span>
      <div class="document-copy">
        <h3 id="document-title-0">{{ __web('documents.annual_reports') }}</h3>
        <span class="document-description">{{ __web('documents.explore_the_company_s_annual') }}</span>
        <span class="document-action">{{ __web('documents.explore_documents') }}</span>
      </div>
      <span class="document-arrow" aria-hidden="true">↗</span>
    </a>
    <a class="document-card" href="{{ web_url('documents/finance') }}" aria-labelledby="document-title-1">
      <span class="document-icon" aria-hidden="true"><svg viewBox="0 0 34 34" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" focusable="false"><rect x="5" y="3" width="23" height="28" rx="4"/><path d="M10 10h13M10 16h3M20 16h3M10 22h3M20 22h3M10 27h3M20 27h3"/></svg></span>
      <div class="document-copy">
        <h3 id="document-title-1">{{ __web('documents.financial_statements') }}</h3>
        <span class="document-description">{{ __web('documents.view_the_company_s_financial') }}</span>
        <span class="document-action">{{ __web('documents.explore_documents') }}</span>
      </div>
      <span class="document-arrow" aria-hidden="true">↗</span>
    </a>
    <a class="document-card" href="{{ web_url('documents/credit') }}" aria-labelledby="document-title-2">
      <span class="document-icon" aria-hidden="true"><svg viewBox="0 0 34 34" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" focusable="false"><path d="m16 3 11 4v9c0 7-7 12-11 15C12 28 5 23 5 16V7zM11 16l4 4 7-8"/></svg></span>
      <div class="document-copy">
        <h3 id="document-title-2">{{ __web('documents.credit_ratings') }}</h3>
        <span class="document-description">{{ __web('documents.find_the_company_s_published') }}</span>
        <span class="document-action">{{ __web('documents.explore_documents') }}</span>
      </div>
      <span class="document-arrow" aria-hidden="true">↗</span>
    </a>
  </div>
</section>
