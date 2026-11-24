<?php

namespace Tests\Feature\Admin;

use App\Models\ProcurementAgency;
use App\Models\ProcurementMethod;
use App\Models\ProcurementRecord;
use App\Models\ProcurementVendor;
use App\Models\Users;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Runs on the real PostgreSQL connection and rolls back every test (see AdminNewsTest).
 */
class AdminProcurementTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $admin = Users::where('superuser', 1)->firstOrFail();
        $this->withSession(['session_token' => $admin->_token])->actingAs($admin);
    }

    public function test_every_list_and_form_page_loads(): void
    {
        foreach (['plan', 'price', 'notice', 'winner_in', 'winner_out'] as $type) {
            $this->get(route("admin.procurement.{$type}.index"))->assertOk();
            $this->get(route("admin.procurement.{$type}.create"))->assertOk();
        }
        foreach (['agency', 'work_type', 'method'] as $kind) {
            $this->get(route("admin.procurement.{$kind}.index"))->assertOk();
        }
    }

    public function test_a_winner_is_saved_with_its_vendor_and_file_and_the_vendor_is_reused(): void
    {
        $payload = [
            'fiscal_year' => 2026, 'title' => 'Test purchase', 'status' => 'published',
            'vendor_name' => '  Test   Vendor Co.,Ltd. ', 'vendor_tax_id' => '0105 5000 1',
            'amount' => '1,250,000.50', 'published_on' => '2026-05-01', 'reference_no' => 'PO 1/2026', 'reason' => '1',
            'files' => ['attachment' => UploadedFile::fake()->create('award.pdf', 50, 'application/pdf')],
        ];

        $this->post(route('admin.procurement.winner_in.store'), $payload)->assertRedirect(route('admin.procurement.winner_in.index'));
        $this->post(route('admin.procurement.winner_in.store'), array_replace($payload, ['title' => 'Second purchase', 'files' => []]))->assertRedirect();

        $vendor = ProcurementVendor::where('name', 'Test Vendor Co.,Ltd.')->sole();
        $this->assertSame('010550001', $vendor->tax_id);
        $record = ProcurementRecord::where('title', 'Test purchase')->sole();
        $this->assertSame($vendor->id, $record->vendor_id);
        $this->assertSame('1250000.50', $record->amount);
        $this->assertSame(2, $vendor->records()->count());
        $this->assertStringStartsWith('procurement/winner_in/', $record->attachments->sole()->file_path);
    }

    public function test_fields_that_a_type_does_not_use_are_refused(): void
    {
        $this->post(route('admin.procurement.plan.store'), ['fiscal_year' => 2026, 'title' => 'Plan', 'status' => 'published', 'vendor_name' => 'X'])
            ->assertSessionHasErrors('vendor_name');
        $this->post(route('admin.procurement.winner_in.store'), ['fiscal_year' => 2026, 'title' => 'W', 'status' => 'published', 'published_on' => '2026-01-01'])
            ->assertSessionHasErrors('vendor_name');
    }

    public function test_a_plan_keeps_one_file_and_one_link_and_they_can_be_replaced_or_removed(): void
    {
        $this->post(route('admin.procurement.plan.store'), [
            'fiscal_year' => 2026, 'title' => 'Plan A', 'status' => 'draft', 'amount' => '6,642,000.00',
            'files' => ['attachment' => UploadedFile::fake()->create('plan.pdf', 20, 'application/pdf')],
            'urls' => ['link' => 'https://drive.example.com/file/1'],
        ])->assertRedirect();
        $record = ProcurementRecord::where('title', 'Plan A')->sole();
        $this->assertSame(2, $record->attachments()->count());
        $this->assertSame('draft', $record->status);

        $this->put(route('admin.procurement.plan.update', $record), [
            'fiscal_year' => 2026, 'title' => 'Plan A', 'status' => 'published',
            'urls' => ['link' => 'https://drive.example.com/file/2'],
            'remove' => ['attachment' => 1],
        ])->assertRedirect();

        $record->refresh()->load('attachments');
        $this->assertSame(1, $record->attachments->count());
        $this->assertSame('https://drive.example.com/file/2', $record->attachments->sole()->external_url);
    }

    public function test_a_notice_takes_three_files_and_an_unsafe_upload_is_refused(): void
    {
        $this->post(route('admin.procurement.notice.store'), [
            'fiscal_year' => 2026, 'title' => 'Notice', 'status' => 'published', 'published_on' => '2026-02-02',
            'files' => ['middle_price' => UploadedFile::fake()->create('a.pdf', 10, 'application/pdf'), 'announcement' => UploadedFile::fake()->create('b.docx', 10)],
        ])->assertRedirect();
        $this->assertSame(2, ProcurementRecord::where('title', 'Notice')->sole()->attachments()->count());

        $this->post(route('admin.procurement.notice.store'), [
            'fiscal_year' => 2026, 'title' => 'Bad', 'status' => 'published', 'published_on' => '2026-02-02',
            'files' => ['announcement' => UploadedFile::fake()->create('evil.php', 1)],
        ])->assertSessionHasErrors('files.announcement');
    }

    public function test_delete_is_soft_and_a_record_cannot_be_opened_under_another_type(): void
    {
        $record = ProcurementRecord::create(['type' => 'price', 'fiscal_year' => 2026, 'title' => 'P', 'status' => 'published', 'published_on' => '2026-01-01']);

        $this->get(route('admin.procurement.plan.edit', $record))->assertNotFound();
        $this->delete(route('admin.procurement.price.destroy', $record))->assertRedirect();
        $this->assertSoftDeleted('procurement_records', ['id' => $record->id]);
    }

    public function test_list_filters_by_year_and_search(): void
    {
        ProcurementRecord::create(['type' => 'price', 'fiscal_year' => 2031, 'title' => 'Unique Marker Alpha', 'status' => 'published', 'published_on' => '2031-01-01']);
        ProcurementRecord::create(['type' => 'price', 'fiscal_year' => 2032, 'title' => 'Unique Marker Beta', 'status' => 'published', 'published_on' => '2032-01-01']);

        $this->get(route('admin.procurement.price.index', ['year' => 2031, 'q' => 'Unique Marker']))
            ->assertOk()->assertSee('Unique Marker Alpha')->assertDontSee('Unique Marker Beta');
    }

    public function test_choices_can_be_added_renamed_switched_off_and_only_unused_ones_deleted(): void
    {
        $this->post(route('admin.procurement.agency.store'), ['name' => 'Agency One'])->assertRedirect(route('admin.procurement.agency.index'));
        $this->post(route('admin.procurement.agency.store'), ['name' => 'Agency One'])->assertSessionHasErrors('name');
        $one = ProcurementAgency::where('name', 'Agency One')->sole();
        $this->assertSame('y', $one->is_active);

        $this->put(route('admin.procurement.agency.update', $one->id), ['name' => 'Agency 1'])->assertRedirect();
        $one->refresh();
        $this->assertSame('Agency 1', $one->name);
        $this->assertSame('n', $one->is_active);

        $record = ProcurementRecord::create(['type' => 'price', 'fiscal_year' => 2026, 'title' => 'P', 'status' => 'published', 'published_on' => '2026-01-01', 'agency_id' => $one->id]);
        $this->delete(route('admin.procurement.agency.destroy', $one->id))->assertSessionHasErrors();
        $this->assertNotNull(ProcurementAgency::find($one->id));

        $spare = ProcurementMethod::create(['name' => 'Spare']);
        $this->delete(route('admin.procurement.method.destroy', $spare->id))->assertRedirect();
        $this->assertNull(ProcurementMethod::find($spare->id));

        // A switched-off choice is hidden from new records but still shown on the record that uses it
        $this->get(route('admin.procurement.price.create'))->assertDontSee('Agency 1');
        $this->get(route('admin.procurement.price.edit', $record))->assertSee('Agency 1');
    }

    public function test_the_same_vendor_name_is_stored_once(): void
    {
        $first = ProcurementVendor::findOrCreateByName('  Same   Co. ', '0105 1');
        $second = ProcurementVendor::findOrCreateByName('same co.', '01051');

        $this->assertSame($first->id, $second->id);
        $this->assertSame('Same Co.', $first->name);
    }
}
