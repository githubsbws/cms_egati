{{-- Links to the other two publication categories --}}
<div class="pub-crosslinks">
    <span class="pub-eyebrow">{{ __web('documents.more_publications') }}</span>
    <div>
        @foreach (config('publications.types') as $key => $meta)
            @continue($key === $type)
            <a href="{{ web_url('documents/'.$key) }}" style="--pub-accent:{{ $meta['color'] }}">
                <span>@include('web.documents.icon', ['name' => $key])</span>
                <div><small>{{ $meta['tag'] }}</small><h2>{{ __web($meta['title']) }}</h2></div>
                <b aria-hidden="true">↗</b>
            </a>
        @endforeach
    </div>
</div>
