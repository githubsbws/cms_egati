<?php

namespace Tests\Feature\Web;

use App\Models\Publication;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Public publications pages on the real PostgreSQL connection (extra rows are rolled back).
 */
class WebDocumentsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_every_page_opens_in_both_languages(): void
    {
        foreach (['th', 'en'] as $locale) {
            $this->get("/{$locale}/documents")->assertOk()->assertSee('pub-category-card', false);
            foreach (['credit', 'finance', 'annual'] as $type) {
                $this->get("/{$locale}/documents/{$type}")->assertOk()->assertSee('pub-header', false);
            }
        }
        $this->get('/th/documents/nope')->assertNotFound();
    }

    public function test_tables_show_published_documents_with_links_that_open_in_a_new_tab(): void
    {
        $count = Publication::published()->ofType('credit')->count();
        $response = $this->get('/en/documents/credit')->assertOk();

        $this->assertSame(min($count, 10), substr_count($response->getContent(), 'class="pub-rating-badge'));
        $this->assertStringContainsString('target="_blank" rel="noopener"', $response->getContent());
    }

    public function test_thai_pages_show_the_buddhist_year(): void
    {
        $item = Publication::create(['type' => 'annual', 'year' => 2031, 'external_url' => 'https://example.com/a.pdf', 'is_published' => true]);

        $this->get('/th/documents/annual?year=2031')->assertSee('2574');
        $this->get('/en/documents/annual?year=2031')->assertSee('Annual Report 2031');
        $this->assertNotNull($item->id);
    }

    public function test_filters_sort_and_pagination(): void
    {
        $this->get('/th/documents/finance')->assertSee('pub-pagination', false);
        $this->get('/th/documents/finance?page=2')->assertOk()->assertSee('aria-current="page"', false);

        $this->get('/en/documents/credit?kind=TRIS%20Rating')->assertOk()->assertDontSee('pub-agency-dot"></span>S&amp;P', false);
        $this->get('/en/documents/finance?kind=annual&year=2025')->assertOk()->assertSee('Full year 2025');
        $this->get('/en/documents/credit?q=zzzz-no-such-record')->assertSee('pub-empty', false);

        $newest = Publication::published()->ofType('annual')->max('year');
        $oldest = Publication::published()->ofType('annual')->min('year');
        $asc = $this->get('/en/documents/annual?sort=asc')->getContent();
        $this->assertLessThan(strpos($asc, '<h3>Annual Report '.$newest), strpos($asc, '<h3>Annual Report '.$oldest));
    }

    public function test_invalid_filters_are_ignored_and_drafts_are_hidden(): void
    {
        $this->get('/th/documents/credit?year=abc&sort=x&kind[]=1')->assertOk();

        Publication::create(['type' => 'credit', 'year' => 2040, 'agency' => 'TRIS Rating', 'rating' => 'ZZZ9', 'external_url' => 'https://example.com/z.pdf', 'is_published' => false]);
        $this->get('/th/documents/credit?q=ZZZ9')->assertDontSee('>ZZZ9<', false);
    }
}
