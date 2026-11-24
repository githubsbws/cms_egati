@extends('web.layouts.mainlayout')

@php
    $title = __web('about.page_board');
    $locale = app()->getLocale();
@endphp

@section('title', $current->nameFor($locale).' | '.__web('site.title'))
@section('body_class', 'about-document')

@section('content')
    <div class="about-page ac-page">
        @include('web.about.header', ['title' => $title])

        <section class="ac-board-section ac-board-section--committee">
            <div class="ac-board-layout">
                {{-- Selected board / committee --}}
                <div id="ac-committee-panel" role="tabpanel" tabindex="0" aria-labelledby="ac-tab-{{ $current->code }}">
                    <div class="ac-panel-heading">
                        <div>
                            <span class="ac-kicker">EGATi · {{ $current->type === 'board' ? 'BOARD OF DIRECTORS' : 'COMMITTEE' }}</span>
                            <h2>{{ $current->nameFor($locale) }}</h2>
                        </div>
                    </div>

                    @include('web.about.people-grid', ['people' => $people, 'label' => __web('about.board_members_in_listed_order')])

                    @if ($current->document_path)
                        @include('web.about.document', ['group' => $current])
                    @endif
                </div>

                {{-- Committee tabs: a GET form, so each tab is a normal link-like page load --}}
                <aside class="ac-board-tabs-rail">
                    <div class="ac-selection-label">
                        <span class="ac-kicker">BOARD & COMMITTEES</span>
                        <p>{{ __web('about.choose_a_board_or_committee') }}</p>
                    </div>
                    <form method="get" action="{{ web_url('about/board') }}#ac-committee-panel" class="ac-committee-tabs" role="tablist" aria-label="{{ __web('about.board_and_committee_categories') }}">
                        @foreach ($groups as $group)
                            <button type="submit" name="committee" value="{{ $group->code }}" role="tab" id="ac-tab-{{ $group->code }}" aria-controls="ac-committee-panel" aria-selected="{{ $group->id === $current->id ? 'true' : 'false' }}">
                                <span class="ac-tab-number">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <strong>{{ $group->nameFor($locale) }}</strong>
                                <span class="ac-tab-arrow" aria-hidden="true">↗</span>
                            </button>
                        @endforeach
                    </form>
                </aside>
            </div>
        </section>
    </div>
@endsection