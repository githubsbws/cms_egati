<?php

namespace Tests\Feature\Admin;

use App\Models\CareerCategory;
use App\Models\CareerJob;
use App\Models\CareerJobDocument;
use App\Models\Users;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Recruitment notices (admin menu id 14) on the real PostgreSQL connection, rolled back after each test.
 */
class AdminCareersTest extends TestCase
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

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function jobPayload(array $overrides = []): array
    {
        return array_replace_recursive([
            'translations' => [
                'th' => ['title' => 'Test position TH', 'department' => 'Dept TH', 'qualifications' => "First\n\nSecond <b>line</b>"],
                'en' => ['title' => 'Test position EN', 'department' => 'Dept EN', 'qualifications' => ''],
            ],
            'category_id' => CareerCategory::where('is_active', true)->firstOrFail()->id,
            'vacancies' => 2,
            'start_on' => '2026-11-01',
            'deadline_on' => '2026-12-01',
            'updated_on' => '2026-11-01',
            'stage' => 'application',
            'is_published' => 1,
        ], $overrides);
    }

    public function test_user_without_permission_on_menu_14_is_denied(): void
    {
        $user = (new Users)->forceFill(['id' => 999999, 'superuser' => 0, 'group_id' => null, '_token' => $this->admin->_token]);
        $this->actingAs($user);

        $this->get(route('admin.careers.jobs.index'))->assertForbidden();
        $this->get(route('admin.careers.categories.index'))->assertForbidden();
        $this->post(route('admin.careers.jobs.store'), [])->assertForbidden();
    }

    public function test_pages_open(): void
    {
        $job = CareerJob::firstOrFail();

        $this->get(route('admin.careers.jobs.index'))->assertOk()
            ->assertSee(route('admin.careers.jobs.index'), false)->assertSee(route('admin.careers.categories.index'), false);
        $this->get(route('admin.careers.jobs.create'))->assertOk()->assertSee('translations[th][title]', false);
        $this->get(route('admin.careers.jobs.edit', $job))->assertOk()->assertSee('id="documents"', false);
        $this->get(route('admin.careers.categories.index'))->assertOk();
    }

    public function test_position_is_created_updated_and_deleted(): void
    {
        $response = $this->post(route('admin.careers.jobs.store'), $this->jobPayload());
        $job = CareerJob::with('translations')->where('vacancies', 2)->latest('id')->firstOrFail();
        $response->assertRedirect(route('admin.careers.jobs.edit', $job));

        $this->assertMatchesRegularExpression('/^test-position-en-[a-z0-9]{4}$/', $job->slug);
        $this->assertSame(['First', 'Second line'], $job->translation('th')->qualifications);
        $this->assertSame('Test position EN', $job->translation('en')->title);

        $this->put(route('admin.careers.jobs.update', $job), $this->jobPayload(['translations' => ['en' => ['title' => ''], 'th' => ['title' => 'Renamed']], 'stage' => 'results']))
            ->assertSessionHasNoErrors();
        $job->refresh()->load('translations');
        $this->assertSame('Renamed', $job->translation('th')->title);
        $this->assertNull($job->translation('en'));
        $this->assertSame('results', $job->stage);

        $this->delete(route('admin.careers.jobs.destroy', $job))->assertRedirect(route('admin.careers.jobs.index'));
        $this->assertSoftDeleted('career_jobs', ['id' => $job->id]);
    }

    public function test_position_validation(): void
    {
        $this->post(route('admin.careers.jobs.store'), $this->jobPayload(['translations' => ['th' => ['title' => '']]]))->assertSessionHasErrors('translations.th.title');
        $this->post(route('admin.careers.jobs.store'), $this->jobPayload(['deadline_on' => '2026-10-01']))->assertSessionHasErrors('deadline_on');
        $this->post(route('admin.careers.jobs.store'), $this->jobPayload(['stage' => 'nope']))->assertSessionHasErrors('stage');
        $this->post(route('admin.careers.jobs.store'), $this->jobPayload(['category_id' => 0]))->assertSessionHasErrors('category_id');

        $inactive = CareerCategory::create(['name' => 'Off family', 'sort_order' => 99, 'is_active' => false]);
        $this->post(route('admin.careers.jobs.store'), $this->jobPayload(['category_id' => $inactive->id]))->assertSessionHasErrors('category_id');
    }

    public function test_documents_can_be_uploaded_linked_ordered_and_deleted(): void
    {
        $job = CareerJob::create(['slug' => 'doc-test', 'category_id' => CareerCategory::firstOrFail()->id, 'start_on' => '2026-11-01', 'deadline_on' => '2026-12-01', 'updated_on' => '2026-11-01']);

        $this->post(route('admin.careers.jobs.documents.store', $job), [
            'labels' => ['th' => 'Notice TH', 'en' => 'Notice EN'],
            'file' => UploadedFile::fake()->create('notice.pdf', 300, 'application/pdf'),
            'is_notice' => 1,
        ])->assertSessionHasNoErrors();
        $this->post(route('admin.careers.jobs.documents.store', $job), [
            'labels' => ['th' => 'Form TH'],
            'external_url' => 'https://example.com/form.pdf',
        ])->assertSessionHasNoErrors();

        [$notice, $form] = $job->documents()->get()->all();
        Storage::disk('public')->assertExists($notice->file_path);
        $this->assertGreaterThan(0, $notice->size_bytes);
        $this->assertTrue($notice->is_notice);
        $this->assertSame('https://example.com/form.pdf', $form->url);
        $this->assertSame('Form TH', $form->labelFor('en', 'th'));

        $this->post(route('admin.careers.documents.move', $form), ['direction' => 'up']);
        $this->assertSame([$form->id, $notice->id], $job->documents()->pluck('id')->all());

        $this->delete(route('admin.careers.documents.destroy', $notice));
        Storage::disk('public')->assertMissing($notice->file_path);
        $this->assertSame(1, CareerJobDocument::where('job_id', $job->id)->count());
    }

    public function test_document_validation(): void
    {
        $job = CareerJob::firstOrFail();

        $this->post(route('admin.careers.jobs.documents.store', $job), ['labels' => ['th' => 'No file']])->assertSessionHasErrors('file');
        $this->post(route('admin.careers.jobs.documents.store', $job), ['labels' => ['th' => ''], 'external_url' => 'https://example.com/a.pdf'])->assertSessionHasErrors('labels.th');
        $this->post(route('admin.careers.jobs.documents.store', $job), ['labels' => ['th' => 'X'], 'file' => UploadedFile::fake()->create('a.exe', 10, 'application/octet-stream')])->assertSessionHasErrors('file');
        $this->post(route('admin.careers.jobs.documents.store', $job), ['labels' => ['th' => 'X'], 'external_url' => 'javascript:alert(1)'])->assertSessionHasErrors('external_url');
    }

    public function test_job_families_are_added_edited_and_protected_when_used(): void
    {
        $this->post(route('admin.careers.categories.store'), ['name' => 'Family TH', 'name_en' => 'Family EN'])->assertRedirect(route('admin.careers.categories.index'));
        $category = CareerCategory::where('name', 'Family TH')->firstOrFail();
        $this->assertTrue($category->is_active);

        $this->post(route('admin.careers.categories.store'), ['name' => 'Family TH'])->assertSessionHasErrors('name');

        $this->put(route('admin.careers.categories.update', $category), ['name' => 'Family TH 2', 'name_en' => ''])->assertSessionHasNoErrors();
        $category->refresh();
        $this->assertSame('Family TH 2', $category->name);
        $this->assertFalse($category->is_active);

        $this->delete(route('admin.careers.categories.destroy', $category));
        $this->assertDatabaseMissing('career_categories', ['id' => $category->id]);

        $used = CareerJob::firstOrFail()->category;
        $this->delete(route('admin.careers.categories.destroy', $used))->assertSessionHasErrors();
        $this->assertDatabaseHas('career_categories', ['id' => $used->id]);
    }
}
