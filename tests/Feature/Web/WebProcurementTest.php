<?php

namespace Tests\Feature\Web;

use App\Models\ProcurementMethod;
use App\Models\ProcurementRecord;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Reads the imported procurement data from the real PostgreSQL connection (drafts are created inside a rolled back transaction).
 */
class WebProcurementTest extends TestCase
{
    use DatabaseTransactions;

    public function test_page_renders_in_both_languages_with_tiles_and_detail_dialogs(): void
    {
        $this->get('/th/procurement')->assertOk()->assertSee('proc-category-tile', false)->assertSee('proc-detail-dialog', false);
        $this->get('/en/procurement?category=winner_in')->assertOk()->assertSee('proc-type-winnerIn', false);
        $this->get('/en/procurement?category=winner_in&q=abc&year=2026')->assertOk()
            ->assertSee('/en/procurement?q=abc&amp;year=2026&amp;category=notice#proc-results-top', false);
    }

    public function test_every_category_and_sort_renders(): void
    {
        foreach (['all', ...ProcurementRecord::TYPES] as $category) {
            foreach (['new', 'old', 'title', 'high', 'low'] as $sort) {
                $this->get("/th/procurement?category={$category}&sort={$sort}&files=1")->assertOk();
            }
        }
    }

    public function test_filters_narrow_the_results(): void
    {
        $record = ProcurementRecord::published()->where('type', 'winner_in')->whereNotNull('method_id')->whereNotNull('vendor_id')->firstOrFail();
        $response = $this->get('/th/procurement?'.http_build_query(['category' => 'winner_in', 'year' => $record->fiscal_year, 'method' => $record->method_id, 'q' => $record->vendor->name]))->assertOk();
        $this->assertGreaterThan(0, substr_count($response->getContent(), 'class="proc-announcement"'));
        $this->get('/th/procurement?q=zzzz-no-such-record-zzzz')->assertOk()->assertSee('proc-empty', false);
    }

    public function test_pagination_and_invalid_query_values(): void
    {
        $this->get('/th/procurement?category=winner_in&size=10&page=2')->assertOk()->assertSee('aria-current="page"', false);
        $this->get('/th/procurement?category=bogus&sort=x&year=abc&size=999&method[]=1')->assertOk();
    }

    public function test_drafts_are_hidden(): void
    {
        $method = ProcurementMethod::firstOrFail();
        ProcurementRecord::create(['type' => 'plan', 'fiscal_year' => 2026, 'title' => 'Hidden draft plan 7731', 'method_id' => $method->id, 'status' => 'draft']);
        ProcurementRecord::create(['type' => 'plan', 'fiscal_year' => 2026, 'title' => 'Visible plan 7731', 'status' => 'published']);

        $this->get('/th/procurement?q=7731')->assertOk()->assertSee('Visible plan 7731')->assertDontSee('Hidden draft plan 7731');
    }
}
