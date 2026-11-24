<?php

namespace Tests\Feature\Web;

use App\Models\CareerCategory;
use App\Models\CareerJob;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Public careers pages on the real PostgreSQL connection (extra rows are rolled back).
 */
class WebCareersTest extends TestCase
{
    use DatabaseTransactions;

    protected function makeJob(array $attributes = [], string $title = 'Zebra tester'): CareerJob
    {
        $job = CareerJob::create(array_merge([
            'slug' => 'zebra-'.uniqid(),
            'category_id' => CareerCategory::firstOrFail()->id,
            'vacancies' => 1,
            'start_on' => today()->subDay(),
            'deadline_on' => today()->addWeek(),
            'updated_on' => today(),
            'stage' => 'application',
            'is_published' => true,
        ], $attributes));
        $job->translations()->create(['locale' => 'th', 'title' => $title, 'department' => 'Dept']);
        $job->translations()->create(['locale' => 'en', 'title' => $title.' EN', 'department' => 'Dept EN']);

        return $job;
    }

    public function test_status_follows_the_dates_in_thailand_time(): void
    {
        $job = new CareerJob(['stage' => 'application', 'start_on' => '2026-11-10', 'deadline_on' => '2026-11-20']);

        $this->assertSame('upcoming', $job->status(Carbon::parse('2026-11-10 07:59:00', 'Asia/Bangkok')));
        $this->assertSame('open', $job->status(Carbon::parse('2026-11-10 08:00:00', 'Asia/Bangkok')));
        $this->assertSame('open', $job->status(Carbon::parse('2026-11-20 17:00:00', 'Asia/Bangkok')));
        $this->assertSame('closed', $job->status(Carbon::parse('2026-11-20 17:00:01', 'Asia/Bangkok')));
        $this->assertSame('results', (new CareerJob(['stage' => 'results']))->status());
    }

    public function test_list_opens_in_both_languages_and_shows_published_positions(): void
    {
        $published = CareerJob::published()->count();

        foreach (['th', 'en'] as $locale) {
            $response = $this->get("/{$locale}/careers")->assertOk()->assertSee('cr-hero', false);
            $this->assertSame($published, substr_count($response->getContent(), 'class="cr-job-card"'));
        }
    }

    public function test_search_category_and_status_filters(): void
    {
        $open = $this->makeJob();
        $closed = $this->makeJob(['start_on' => '2020-01-01', 'deadline_on' => '2020-02-01'], 'Zebra closed');
        $draft = $this->makeJob(['is_published' => false], 'Zebra hidden');

        $this->get('/th/careers?q=zebra')->assertOk()->assertSee('Zebra tester')->assertSee('Zebra closed')->assertDontSee('Zebra hidden');
        $this->get('/th/careers?q=zebra&status=open')->assertSee('Zebra tester')->assertDontSee('Zebra closed');
        $this->get('/th/careers?q=zebra&status=closed')->assertSee('Zebra closed')->assertDontSee('Zebra tester');
        $this->get('/en/careers?q=zebra')->assertSee('Zebra tester EN');
        $this->get('/th/careers?q=zebra&category='.$open->category_id)->assertSee('Zebra tester');
        $this->get('/th/careers?q=zebra&category=999999')->assertSee('cr-empty', false);
        $this->get('/th/careers?q=nothing-matches-this-qwerty')->assertSee('cr-empty', false);
    }

    public function test_invalid_filters_are_ignored(): void
    {
        $this->get('/th/careers?status=bogus&category=abc&q[]=x')->assertOk();
    }

    public function test_position_page_shows_documents_and_the_apply_state(): void
    {
        $open = $this->makeJob();
        $open->documents()->create(['labels' => ['th' => 'Doc TH', 'en' => 'Doc EN'], 'external_url' => 'https://example.com/a.pdf', 'is_notice' => true, 'sort_order' => 1]);
        $closed = $this->makeJob(['start_on' => '2020-01-01', 'deadline_on' => '2020-02-01'], 'Zebra closed');

        $this->get("/en/careers/{$open->slug}")->assertOk()->assertSee('Doc EN')->assertSee('https://example.com/a.pdf', false)->assertSee('mailto:egati-hr@egati.co.th', false);
        $this->get("/th/careers/{$closed->slug}")->assertOk()->assertDontSee('mailto:egati-hr@egati.co.th?subject', false);
    }

    public function test_position_page_for_seeded_notices_and_unknown_slugs(): void
    {
        $job = CareerJob::published()->has('documents')->firstOrFail();

        $this->get("/th/careers/{$job->slug}")->assertOk()->assertSee('cr-document-link', false);
        $this->get('/th/careers/does-not-exist')->assertNotFound();

        $hidden = $this->makeJob(['is_published' => false]);
        $this->get("/th/careers/{$hidden->slug}")->assertNotFound();
    }
}
