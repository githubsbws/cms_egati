{{-- Careers: HR contact band (shared by the list and the position page) --}}
<section class="cr-contact" id="cr-contact">
    <div class="cr-contact-intro">
        <span class="cr-kicker">WE’RE HERE TO HELP</span>
        <h2>{!! __web('careers.let_s_take_the_next_step') !!}</h2>
        <p>{!! __web('careers.questions_about_applying_please_contact_hr') !!}</p>
        <a href="mailto:{{ config('careers.hr_email') }}" class="cr-contact-email">@include('web.careers.icon', ['name' => 'mail']){{ config('careers.hr_email') }} ↗</a>
    </div>
    <div class="cr-contact-people">
        @foreach (config('careers.hr_people') as $person)
            <article>
                <span>{{ __web($person['key']) }}</span>
                <h3>{{ __web($person['name']) }}</h3>
                <div>
                    @foreach ($person['phones'] as $phone)
                        <a href="tel:{{ preg_replace('/\D/', '', $phone) }}">{{ $phone }}</a>
                    @endforeach
                </div>
            </article>
        @endforeach
    </div>
</section>
