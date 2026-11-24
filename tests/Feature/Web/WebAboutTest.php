<?php

namespace Tests\Feature\Web;

use App\Models\OrgGroup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Reads the imported organization data from the real PostgreSQL connection (nothing is written).
 */
class WebAboutTest extends TestCase
{
    use DatabaseTransactions;

    public function test_board_page_lists_the_board_and_a_committee_tab_shows_its_people(): void
    {
        $this->get('/th/about/board')->assertOk()->assertSee('ac-director-item', false)->assertSee('name="committee" value="audit"', false);

        $audit = $this->get('/en/about/board?committee=audit')->assertOk();
        $this->assertSame(OrgGroup::where('code', 'audit')->firstOrFail()->members()->where('is_active', true)->count(), substr_count($audit->getContent(), 'ac-director-item'));
    }

    public function test_an_unknown_committee_falls_back_to_the_board(): void
    {
        $this->get('/th/about/board?committee=nope')->assertOk()->assertSee('BOARD OF DIRECTORS');
    }

    public function test_people_without_a_name_show_the_not_listed_text(): void
    {
        $this->get('/th/about/board?committee=audit')->assertOk()->assertSee('ac-name-unlisted', false);
    }

    public function test_management_and_structure_pages_load_in_both_languages(): void
    {
        foreach (['th', 'en'] as $locale) {
            $this->get("/{$locale}/about/management")->assertOk()->assertSee('ac-management-grid', false);
            $this->get("/{$locale}/about/structure")->assertOk()->assertSee('ac-organization-image', false);
        }
    }

    public function test_inactive_people_and_committees_are_hidden(): void
    {
        $audit = OrgGroup::where('code', 'audit')->firstOrFail();
        $audit->members()->first()->update(['is_active' => false]);
        $this->assertSame($audit->members()->where('is_active', true)->count(), substr_count($this->get('/th/about/board?committee=audit')->getContent(), 'ac-director-item'));

        $audit->update(['is_active' => false]);
        $this->get('/th/about/board?committee=audit')->assertOk()->assertDontSee('value="audit"', false);
    }
}
