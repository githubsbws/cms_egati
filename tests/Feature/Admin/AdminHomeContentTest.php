<?php

namespace Tests\Feature\Admin;

use App\Helpers\HomeContent;
use App\Models\HomeAgency;
use App\Models\HomeBlock;
use App\Models\Users;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Home page content (admin menu id 2) on the real PostgreSQL connection, rolled back after each test.
 */
class AdminHomeContentTest extends TestCase
{
    use DatabaseTransactions;

    protected Users $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        HomeBlock::query()->delete(); // the shared database may already hold saved content; this is rolled back
        HomeContent::flush();
        $this->admin = Users::where('superuser', 1)->firstOrFail();
        $this->withSession(['session_token' => $this->admin->_token])->actingAs($this->admin);
    }

    public function test_user_without_permission_on_menu_2_is_denied(): void
    {
        $user = (new Users)->forceFill(['id' => 999999, 'superuser' => 0, 'group_id' => null, '_token' => $this->admin->_token]);
        $this->actingAs($user);

        foreach (['admin.content.hero.edit', 'admin.content.story.edit', 'admin.content.presentation.edit', 'admin.content.agencies.index'] as $route) {
            $this->get(route($route))->assertForbidden();
        }
        $this->put(route('admin.content.hero.update'), [])->assertForbidden();
    }

    public function test_every_page_opens(): void
    {
        foreach (['admin.content.hero.edit', 'admin.content.story.edit', 'admin.content.presentation.edit', 'admin.content.agencies.index', 'admin.content.agencies.create'] as $route) {
            $this->get(route($route))->assertOk();
        }
        $this->get(route('admin.content.story.edit'))->assertSee('blocks[story_3][texts][th][title]', false);
    }

    public function test_hero_text_is_saved_as_plain_text_and_shown_on_the_home_page(): void
    {
        $this->put(route('admin.content.hero.update'), ['blocks' => ['hero' => [
            'texts' => ['th' => ['heading' => "First line\nSecond <b onclick=\"x()\">line</b><script>alert(1)</script>", 'paragraph' => 'Plain & simple']],
            'plain' => ['primary_url' => 'about/governance'],
        ]]])->assertRedirect(route('admin.content.hero.edit'));

        $heading = HomeBlock::findOrFail('hero')->texts()->where('locale', 'th')->firstOrFail()->fields['heading'];
        $this->assertStringNotContainsString('<', $heading);

        $this->get('/th')->assertOk()->assertSee("First line<br />\nSecond linealert(1)", false)->assertSee('Plain &amp; simple', false)->assertSee(web_url('about/governance'));
    }

    public function test_text_saved_earlier_with_markup_is_shown_as_plain_text(): void
    {
        HomeBlock::create(['code' => 'hero'])->texts()->create(['locale' => 'th', 'fields' => ['heading' => 'Old one<br><span>Old two</span>']]);

        $this->get(route('admin.content.hero.edit'))->assertSee("Old one\nOld two", false)->assertDontSee('&lt;span&gt;', false);
        $this->get('/th')->assertSee("Old one<br />\nOld two", false)->assertDontSee('&lt;span&gt;', false);
    }

    public function test_an_empty_text_goes_back_to_the_default(): void
    {
        $this->put(route('admin.content.hero.update'), ['blocks' => ['hero' => ['texts' => ['th' => ['heading' => 'Temp']]]]]);
        $this->get('/th')->assertSee('Temp');

        $this->put(route('admin.content.hero.update'), ['blocks' => ['hero' => ['texts' => ['th' => ['heading' => '']]]]]);
        $this->get('/th')->assertDontSee('Temp')->assertSee('video-hero-copy', false);
    }

    public function test_hero_video_and_poster_are_stored_and_replaced(): void
    {
        $this->put(route('admin.content.hero.update'), ['blocks' => ['hero' => [
            'video' => UploadedFile::fake()->create('a.mp4', 500, 'video/mp4'),
            'poster' => UploadedFile::fake()->image('a.jpg'),
        ]]])->assertSessionHasNoErrors();

        $block = HomeBlock::findOrFail('hero');
        Storage::disk('public')->assertExists([$block->video_path, $block->poster_path]);
        $oldVideo = $block->video_path;
        $this->get('/th')->assertSee(Storage::disk('public')->url($block->video_path), false);

        $this->put(route('admin.content.hero.update'), ['blocks' => ['hero' => ['video' => UploadedFile::fake()->create('b.webm', 500, 'video/webm')]]]);
        Storage::disk('public')->assertMissing($oldVideo);
        Storage::disk('public')->assertExists(HomeBlock::findOrFail('hero')->video_path);
        Storage::disk('public')->assertExists($block->fresh()->poster_path);
    }

    public function test_a_missing_media_file_falls_back_to_the_default(): void
    {
        HomeBlock::create(['code' => 'hero', 'video_path' => 'home/2026/11/gone.mp4']);

        $this->get('/th')->assertOk()->assertSee('web-assets/clean-energy-flood-mitigation.mp4', false);
    }

    public function test_invalid_input_is_rejected(): void
    {
        $this->put(route('admin.content.hero.update'), ['blocks' => ['hero' => [
            'video' => UploadedFile::fake()->create('a.pdf', 10, 'application/pdf'),
            'poster' => UploadedFile::fake()->create('a.gif', 10, 'image/gif'),
            'plain' => ['primary_url' => 'javascript:alert(1)'],
        ]]])->assertSessionHasErrors(['blocks.hero.video', 'blocks.hero.poster', 'blocks.hero.plain.primary_url']);

        $this->put(route('admin.content.presentation.update'), ['blocks' => ['presentation' => ['plain' => ['duration' => '3 minutes']]]])
            ->assertSessionHasErrors('blocks.presentation.plain.duration');
        $this->assertNull(HomeBlock::find('hero'));
    }

    public function test_story_slides_are_saved_separately(): void
    {
        $this->put(route('admin.content.story.update'), ['blocks' => [
            'story_2' => ['texts' => ['en' => ['title' => 'Second title', 'text' => 'Second text']], 'plain' => ['cta_url' => 'https://example.com/x']],
        ]])->assertSessionHasNoErrors();

        $this->get('/en')->assertSee('Second title', false)->assertSee('Second text')->assertSee('https://example.com/x', false);
        $this->assertNull(HomeBlock::where('code', 'story_1')->first()?->texts()->first());
    }

    public function test_presentation_duration_and_text_are_shown(): void
    {
        $this->put(route('admin.content.presentation.update'), ['blocks' => ['presentation' => [
            'texts' => ['th' => ['title' => 'My presentation']],
            'plain' => ['duration' => '1:02:03'],
        ]]])->assertSessionHasNoErrors();

        $this->get('/th')->assertSee('My presentation', false)->assertSee('1:02:03');
    }

    public function test_home_page_shows_every_active_agency(): void
    {
        $response = $this->get('/th')->assertOk();
        $this->assertSame(HomeAgency::where('is_active', true)->count(), substr_count($response->getContent(), 'class="stakeholder-link"'));
    }

    public function test_agency_can_be_added_edited_hidden_ordered_and_deleted(): void
    {
        $count = HomeAgency::count();
        HomeAgency::query()->update(['is_active' => false]);
        $this->post(route('admin.content.agencies.store'), [
            'translations' => ['th' => ['name' => 'Test agency TH'], 'en' => ['name' => 'Test agency EN']],
            'url' => 'https://example.com',
            'logo' => UploadedFile::fake()->image('logo.png'),
            'is_active' => 1,
        ])->assertRedirect(route('admin.content.agencies.index'));

        $agency = HomeAgency::whereHas('translations', fn ($q) => $q->where('name', 'Test agency EN'))->firstOrFail();
        $this->assertSame($count + 1, HomeAgency::count());
        Storage::disk('public')->assertExists($agency->logo_path);
        $this->get('/en')->assertSee('Test agency EN')->assertSee('https://example.com', false);
        $this->get('/th')->assertSee('Test agency TH');

        $logo = $agency->logo_path;
        $this->put(route('admin.content.agencies.update', $agency), [
            'translations' => ['th' => ['name' => 'Renamed TH'], 'en' => ['name' => '']],
            'url' => 'https://example.org',
            'is_active' => 1,
        ])->assertSessionHasNoErrors();
        $agency->refresh()->load('translations');
        $this->assertSame($logo, $agency->logo_path);
        $this->assertNull($agency->translation('en'));
        $this->get('/en')->assertSee('Renamed TH');

        $this->post(route('admin.content.agencies.move', $agency), ['direction' => 'up']);
        $order = HomeAgency::ordered()->pluck('id')->all();
        $this->assertSame($agency->id, $order[count($order) - 2]);
        $this->assertSame(range(1, count($order)), HomeAgency::ordered()->pluck('sort_order')->all());

        $this->patch(route('admin.content.agencies.toggle', $agency));
        $this->get('/th')->assertDontSee('Renamed TH');

        $this->delete(route('admin.content.agencies.destroy', $agency))->assertRedirect(route('admin.content.agencies.index'));
        $this->assertSoftDeleted('home_agencies', ['id' => $agency->id]);
    }

    public function test_agency_validation(): void
    {
        $payload = ['translations' => ['th' => ['name' => 'X']], 'url' => 'https://example.com', 'logo' => UploadedFile::fake()->image('l.png')];

        $this->post(route('admin.content.agencies.store'), array_replace($payload, ['translations' => ['th' => ['name' => '']]]))->assertSessionHasErrors('translations.th.name');
        $this->post(route('admin.content.agencies.store'), array_replace($payload, ['url' => 'javascript:alert(1)']))->assertSessionHasErrors('url');
        $this->post(route('admin.content.agencies.store'), array_diff_key($payload, ['logo' => 1]))->assertSessionHasErrors('logo');
        $this->post(route('admin.content.agencies.store'), array_replace($payload, ['logo' => UploadedFile::fake()->create('l.gif', 5, 'image/gif')]))->assertSessionHasErrors('logo');

        $bad = UploadedFile::fake()->createWithContent('bad.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');
        $this->post(route('admin.content.agencies.store'), array_replace($payload, ['logo' => $bad]))->assertSessionHasErrors('logo');

        $good = UploadedFile::fake()->createWithContent('ok.svg', '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1 1"><rect width="1" height="1"/></svg>');
        $this->post(route('admin.content.agencies.store'), array_replace($payload, ['logo' => $good]))->assertSessionHasNoErrors();
    }
}
