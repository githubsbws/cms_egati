<?php

namespace Tests\Feature\Admin;

use App\Models\OrgChart;
use App\Models\OrgGroup;
use App\Models\OrgMember;
use App\Models\Users;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Runs on the real PostgreSQL connection and rolls back every test (see AdminNewsTest).
 */
class AdminOrganizationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $admin = Users::where('superuser', 1)->firstOrFail();
        $this->withSession(['session_token' => $admin->_token])->actingAs($admin);
    }

    /**
     * @param  array<string, mixed>  $override
     * @return array<string, mixed>
     */
    protected function memberPayload(int $groupId, array $override = []): array
    {
        return array_replace_recursive([
            'group_id' => $groupId,
            'is_active' => 1,
            'translations' => [
                'th' => ['name' => 'Test Person TH', 'role' => 'Director TH'],
                'en' => ['name' => 'Test Person', 'role' => 'Director'],
            ],
        ], $override);
    }

    public function test_pages_load(): void
    {
        foreach (['board', 'management'] as $section) {
            $this->get(route("admin.organization.{$section}.index"))->assertOk();
            $this->get(route("admin.organization.{$section}.members.create"))->assertOk();
        }
        $this->get(route('admin.organization.board.groups.create'))->assertOk();
        $this->get(route('admin.organization.chart.index'))->assertOk();
    }

    public function test_imported_board_has_people_and_committees_in_tabs(): void
    {
        $this->get(route('admin.organization.board.index'))->assertOk()->assertSee('?group=audit', false);
        $this->get(route('admin.organization.board.index', ['group' => 'audit']))->assertOk();
    }

    public function test_member_can_be_added_edited_and_deleted_with_a_photo(): void
    {
        $group = OrgGroup::where('code', 'management')->firstOrFail();

        $this->post(route('admin.organization.management.members.store'), $this->memberPayload($group->id, [
            'photo' => UploadedFile::fake()->image('p.png'),
        ]))->assertRedirect(route('admin.organization.management.index', ['group' => 'management']));

        $member = OrgMember::where('group_id', $group->id)->latest('id')->firstOrFail();
        $this->assertSame('Test Person', $member->translation('en')->name);
        $this->assertStringStartsWith('organization/', $member->photo_path);

        $this->get(route('admin.organization.management.members.edit', $member))->assertOk()->assertSee('Test Person');

        $this->put(route('admin.organization.management.members.update', $member), $this->memberPayload($group->id, [
            'translations' => ['en' => ['name' => '']],
            'remove_photo' => 1,
        ]))->assertRedirect();
        $member->refresh()->load('translations');
        $this->assertNull($member->translation('en'));
        $this->assertNull($member->photo_path);

        $this->delete(route('admin.organization.management.members.destroy', $member))->assertRedirect();
        $this->assertSoftDeleted('org_members', ['id' => $member->id]);
    }

    public function test_default_language_name_and_role_are_required(): void
    {
        $group = OrgGroup::where('code', 'management')->firstOrFail();

        $this->post(route('admin.organization.management.members.store'), $this->memberPayload($group->id, ['translations' => ['th' => ['name' => '', 'role' => '']]]))
            ->assertSessionHasErrors(['translations.th.name', 'translations.th.role']);
    }

    public function test_a_page_cannot_touch_groups_of_the_other_page(): void
    {
        $board = OrgGroup::where('code', 'board')->firstOrFail();
        $managementMember = OrgMember::where('group_id', OrgGroup::where('code', 'management')->value('id'))->firstOrFail();

        $this->post(route('admin.organization.management.members.store'), $this->memberPayload($board->id))->assertNotFound();
        $this->get(route('admin.organization.board.members.edit', $managementMember))->assertNotFound();
        $this->get(route('admin.organization.management.groups.create'))->assertNotFound();
    }

    public function test_committee_can_be_created_with_a_document_and_deleted_when_empty(): void
    {
        $this->post(route('admin.organization.board.groups.store'), [
            'names' => ['th' => 'Committee TH', 'en' => 'Test Committee'],
            'sort_order' => 9,
            'is_active' => 1,
            'document' => UploadedFile::fake()->create('order.pdf', 20, 'application/pdf'),
        ])->assertRedirect();

        $group = OrgGroup::where('type', 'committee')->where('sort_order', 9)->latest('id')->firstOrFail();
        $this->assertSame('committee', $group->type);
        $this->assertStringStartsWith('organization/documents/', $group->document_path);

        $this->delete(route('admin.organization.board.groups.destroy', $group))->assertRedirect(route('admin.organization.board.index'));
        $this->assertDatabaseMissing('org_groups', ['id' => $group->id]);
    }

    public function test_main_groups_and_committees_with_people_cannot_be_deleted(): void
    {
        $board = OrgGroup::where('code', 'board')->firstOrFail();
        $audit = OrgGroup::where('code', 'audit')->firstOrFail();

        $this->delete(route('admin.organization.board.groups.destroy', $board))->assertSessionHasErrors();
        $this->delete(route('admin.organization.board.groups.destroy', $audit))->assertSessionHasErrors();
        $this->assertDatabaseHas('org_groups', ['id' => $audit->id]);
    }

    public function test_chart_can_be_uploaded_per_language_and_falls_back_to_the_default(): void
    {
        $this->post(route('admin.organization.chart.store', 'en'), ['image' => UploadedFile::fake()->image('chart.png')])->assertRedirect();
        $this->assertStringStartsWith('organization/', OrgChart::find('en')->image_path);
        $this->assertNotNull(OrgChart::imageFor('en'));

        $this->delete(route('admin.organization.chart.destroy', 'en'))->assertRedirect();
        $this->assertNull(OrgChart::find('en'));
        $this->assertSame(OrgChart::imageFor('th'), OrgChart::imageFor('en'));
    }

    public function test_chart_rejects_non_images(): void
    {
        $this->post(route('admin.organization.chart.store', 'th'), ['image' => UploadedFile::fake()->create('x.pdf', 10, 'application/pdf')])
            ->assertSessionHasErrors('image');
    }
}
