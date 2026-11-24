<?php

namespace Tests\Feature\Admin;

use App\Models\Publication;
use App\Models\Users;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Published documents (admin menu id 13) on the real PostgreSQL connection, rolled back after each test.
 */
class AdminPublicationsTest extends TestCase
{
    use DatabaseTransactions;

    protected Users $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->admin = Users::where('superuser', 1)->firstOrFail();
        $this->withSession(['session_token' => $this->admin->_token])->actingAs($this->admin);
    }

    public function test_user_without_permission_on_menu_13_is_denied(): void
    {
        $user = (new Users)->forceFill(['id' => 999999, 'superuser' => 0, 'group_id' => null, '_token' => $this->admin->_token]);
        $this->actingAs($user);

        $this->get(route('admin.documents.credit.index'))->assertForbidden();
        $this->post(route('admin.documents.finance.store'), [])->assertForbidden();
    }

    public function test_pages_open_and_the_menu_links_to_them(): void
    {
        foreach (['credit', 'finance', 'annual'] as $type) {
            $this->get(route("admin.documents.{$type}.index"))->assertOk()->assertSee(route("admin.documents.{$type}.index"), false);
            $this->get(route("admin.documents.{$type}.create"))->assertOk();
            $this->get(route("admin.documents.{$type}.edit", Publication::ofType($type)->firstOrFail()))->assertOk();
        }
    }

    public function test_an_item_of_the_wrong_type_is_not_found(): void
    {
        $credit = Publication::ofType('credit')->firstOrFail();

        $this->get(route('admin.documents.annual.edit', $credit))->assertNotFound();
    }

    public function test_credit_rating_is_added_and_validated(): void
    {
        $payload = ['year' => 2031, 'agency' => 'TRIS Rating', 'rating' => 'AAA', 'external_url' => 'https://example.com/r.pdf', 'is_published' => 1];

        $this->post(route('admin.documents.credit.store'), $payload)->assertRedirect(route('admin.documents.credit.index'));
        $item = Publication::ofType('credit')->where('year', 2031)->firstOrFail();
        $this->assertSame('https://example.com/r.pdf', $item->documentUrl());

        $this->post(route('admin.documents.credit.store'), $payload)->assertSessionHasErrors('year');
        $this->post(route('admin.documents.credit.store'), array_replace($payload, ['year' => 2574]))->assertSessionHasErrors('year');
        $this->post(route('admin.documents.credit.store'), array_replace($payload, ['year' => 2032, 'agency' => 'Nobody']))->assertSessionHasErrors('agency');
        $this->post(route('admin.documents.credit.store'), array_replace($payload, ['year' => 2032, 'external_url' => '']))->assertSessionHasErrors('file');
        $this->post(route('admin.documents.credit.store'), array_replace($payload, ['year' => 2032, 'external_url' => 'javascript:alert(1)']))->assertSessionHasErrors('external_url');
    }

    public function test_financial_statement_has_a_thai_and_an_english_file_that_can_be_replaced_and_removed(): void
    {
        $this->post(route('admin.documents.finance.store'), [
            'year' => 2031, 'period' => '2',
            'file' => UploadedFile::fake()->create('th.pdf', 200, 'application/pdf'),
            'external_url_en' => 'https://example.com/en.pdf',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $item = Publication::ofType('finance')->where('year', 2031)->firstOrFail();
        Storage::disk('public')->assertExists($item->file_path);
        $this->assertSame('https://example.com/en.pdf', $item->documentUrl('en'));
        $first = $item->file_path;

        $this->put(route('admin.documents.finance.update', $item), [
            'year' => 2031, 'period' => '2',
            'file' => UploadedFile::fake()->create('th2.pdf', 200, 'application/pdf'),
            'external_url_en' => 'https://example.com/en.pdf',
        ])->assertSessionHasNoErrors();
        Storage::disk('public')->assertMissing($first);

        $this->put(route('admin.documents.finance.update', $item), [
            'year' => 2031, 'period' => '2', 'remove_file' => 1, 'external_url' => 'https://example.com/th.pdf',
            'external_url_en' => 'https://example.com/en.pdf',
        ])->assertSessionHasNoErrors();
        $item->refresh();
        $this->assertNull($item->file_path);
        $this->assertSame('https://example.com/th.pdf', $item->documentUrl());

        $this->post(route('admin.documents.finance.store'), ['year' => 2031, 'period' => '2', 'external_url' => 'https://example.com/x.pdf'])->assertSessionHasErrors('year');
        $this->post(route('admin.documents.finance.store'), ['year' => 2031, 'period' => 'x', 'external_url' => 'https://example.com/x.pdf'])->assertSessionHasErrors('period');
    }

    public function test_annual_report_has_a_cover_and_can_be_deleted(): void
    {
        $this->post(route('admin.documents.annual.store'), [
            'year' => 2031,
            'file' => UploadedFile::fake()->create('ar.pdf', 300, 'application/pdf'),
            'cover' => UploadedFile::fake()->image('cover.jpg'),
        ])->assertRedirect()->assertSessionHasNoErrors();

        $item = Publication::ofType('annual')->where('year', 2031)->firstOrFail();
        Storage::disk('public')->assertExists([$item->file_path, $item->cover_image]);

        $this->post(route('admin.documents.annual.store'), ['year' => 2032, 'external_url' => 'https://example.com/a.pdf', 'cover' => UploadedFile::fake()->create('c.gif', 5, 'image/gif')])->assertSessionHasErrors('cover');

        $this->delete(route('admin.documents.annual.destroy', $item))->assertRedirect(route('admin.documents.annual.index'));
        $this->assertSoftDeleted('publications', ['id' => $item->id]);
    }
}
