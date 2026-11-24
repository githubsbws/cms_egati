{{-- People of a group as the numbered portrait grid. Params: $people (list of name/role/photo/assignments), $label, optional $class --}}
<div class="ac-director-grid {{ $class ?? '' }}" aria-label="{{ $label }}">
    @foreach ($people as $person)
        <div class="ac-director-item" style="--director-order:{{ $loop->index }}">
            <span class="ac-director-index" aria-hidden="true">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
            <figure class="ac-person">
                <div class="ac-portrait">
                    @if ($person['photo'])
                        <img src="{{ $person['photo'] }}" alt="{{ $person['name'] !== '' ? $person['name'] : __web('about.portrait_shown_on_the_source') }}" loading="lazy">
                    @endif
                </div>
                <figcaption>
                    @if ($person['name'] !== '')
                        <h3>{{ $person['name'] }}</h3>
                    @else
                        <h3 class="ac-name-unlisted">{{ __web('about.name_not_listed_on_source') }}</h3>
                    @endif
                    <p>{{ $person['role'] }}</p>
                    @foreach ($person['assignments'] as $assignment)
                        <p>{{ $assignment }}</p>
                    @endforeach
                </figcaption>
            </figure>
        </div>
    @endforeach
</div>