<?php

namespace Tests\Feature\Admin;

use App\Helpers\WebStrings;
use App\Models\Language;
use App\Models\Users;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Runs on the real PostgreSQL connection and rolls back every test (see AdminNewsTest).
 * The "local" disk is faked, so no language file on disk is touched.
 */
class AdminLanguageTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
        $admin = Users::where('superuser', 1)->firstOrFail();
        $this->withSession(['session_token' => $admin->_token])->actingAs($admin);

        $default = Language::where('is_default', 'y')->firstOrFail();
        WebStrings::write($default, ['nav.home' => 'Home', 'home.title' => 'A<br>B']);
    }

    /**
     * @param  array<string, mixed>  $override
     * @return array<string, mixed>
     */
    protected function payload(array $override = []): array
    {
        return array_replace([
            'code' => 'zz',
            'name' => 'Test language',
            'native_name' => 'Zed',
            'direction' => 'ltr',
            'sort_order' => 9,
        ], $override);
    }

    /**
     * The code cannot be sent when editing.
     *
     * @param  array<string, mixed>  $override
     * @return array<string, mixed>
     */
    protected function updatePayload(array $override = []): array
    {
        return array_diff_key($this->payload($override), ['code' => 1]);
    }

    protected function jsonUpload(array $strings): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('zz.json', json_encode(['strings' => $strings]));
    }

    public function test_pages_load(): void
    {
        $this->get(route('admin.settings.language.index'))->assertOk()->assertSee('th');
        $this->get(route('admin.settings.language.create'))->assertOk();
    }

    public function test_new_language_starts_inactive_with_the_uploaded_text_file(): void
    {
        $this->post(route('admin.settings.language.store'), $this->payload([
            'json_file' => $this->jsonUpload(['nav.home' => 'Accueil']),
            'flag' => UploadedFile::fake()->image('flag.png'),
        ]))->assertRedirect(route('admin.settings.language.edit', 'zz'));

        $language = Language::findOrFail('zz');
        $this->assertSame('n', $language->is_active);
        $this->assertSame('n', $language->is_default);
        $this->assertStringStartsWith('languages/', $language->flag);
        $this->assertSame(['nav.home' => 'Accueil'], WebStrings::read('zz'));
    }

    public function test_without_an_upload_the_default_language_text_is_copied(): void
    {
        $this->post(route('admin.settings.language.store'), $this->payload())->assertRedirect();

        $this->assertSame(WebStrings::read('th'), WebStrings::read('zz'));
    }

    public function test_bad_text_files_are_rejected_and_nothing_is_created(): void
    {
        foreach (['{not json', '[1,2]', '{"a b":"x"}', '{"nav.home":"<script>alert(1)</script>"}', '{"nav.home":"<a href=\"x\">y</a>"}', '{"nav.home":5}'] as $bad) {
            $this->post(route('admin.settings.language.store'), $this->payload([
                'json_file' => UploadedFile::fake()->createWithContent('zz.json', $bad),
            ]))->assertSessionHasErrors('json');
        }
        $this->assertNull(Language::find('zz'));
    }

    public function test_code_must_be_new_short_lowercase_and_not_reserved(): void
    {
        foreach (['th', 'ABC', 'abcd', 'a1', 'api'] as $code) {
            $this->post(route('admin.settings.language.store'), $this->payload(['code' => $code]))->assertSessionHasErrors('code');
        }
    }

    public function test_activating_a_language_clears_the_locale_cache_and_makes_it_usable(): void
    {
        $this->post(route('admin.settings.language.store'), $this->payload())->assertRedirect();
        Cache::put('web.active_locales', ['th', 'en'], 300);

        $this->put(route('admin.settings.language.update', 'zz'), $this->updatePayload(['is_active' => 1]))->assertRedirect();

        $this->assertSame('y', Language::find('zz')->is_active);
        $this->assertNull(Cache::get('web.active_locales'));
    }

    public function test_language_without_a_text_file_cannot_be_activated(): void
    {
        $this->post(route('admin.settings.language.store'), $this->payload())->assertRedirect();
        Storage::disk('local')->delete(WebStrings::path('zz'));

        $this->put(route('admin.settings.language.update', 'zz'), $this->updatePayload(['is_active' => 1]))->assertSessionHasErrors('is_active');
    }

    public function test_the_default_language_stays_default_and_active(): void
    {
        $default = Language::where('is_default', 'y')->firstOrFail();

        $this->put(route('admin.settings.language.update', $default), $this->updatePayload([
            'name' => $default->name, 'native_name' => $default->native_name,
        ]))->assertRedirect();

        $default->refresh();
        $this->assertSame('y', $default->is_default);
        $this->assertSame('y', $default->is_active);
    }

    public function test_choosing_another_default_moves_the_default_flag(): void
    {
        $this->post(route('admin.settings.language.store'), $this->payload())->assertRedirect();

        $this->put(route('admin.settings.language.update', 'zz'), $this->updatePayload(['is_default' => 1]))->assertRedirect();

        $this->assertSame(['zz'], Language::where('is_default', 'y')->pluck('code')->all());
        $this->assertSame('y', Language::find('zz')->is_active);
    }

    public function test_text_can_be_edited_downloaded_and_replaced_by_upload(): void
    {
        $this->post(route('admin.settings.language.store'), $this->payload())->assertRedirect();
        $this->get(route('admin.settings.language.strings', 'zz'))->assertOk()->assertSee('nav.home');

        $this->put(route('admin.settings.language.strings.save', 'zz'), ['json' => '{"nav.home":"Salut","x.y":"A<br>B"}'])->assertRedirect();
        $this->assertSame(['nav.home' => 'Salut', 'x.y' => 'A<br>B'], WebStrings::read('zz'));

        $this->get(route('admin.settings.language.download', 'zz'))->assertOk()->assertHeader('content-disposition');

        $this->post(route('admin.settings.language.strings.upload', 'zz'), ['json_file' => $this->jsonUpload(['nav.home' => 'Bonjour'])])->assertRedirect();
        $this->assertSame(['nav.home' => 'Bonjour'], WebStrings::read('zz'));
    }

    public function test_a_failed_save_keeps_the_old_file(): void
    {
        $this->post(route('admin.settings.language.store'), $this->payload(['json_file' => $this->jsonUpload(['nav.home' => 'Keep'])]))->assertRedirect();

        $this->put(route('admin.settings.language.strings.save', 'zz'), ['json' => '{"nav.home":"<iframe>"}'])->assertSessionHasErrors('json');

        $this->assertSame(['nav.home' => 'Keep'], WebStrings::read('zz'));
    }

    public function test_the_shipped_th_and_en_files_pass_the_same_checks(): void
    {
        foreach (['th', 'en'] as $code) {
            $contents = file_get_contents(storage_path("app/private/localization/web/{$code}.json"));
            $this->assertNotEmpty(WebStrings::parse($contents));
        }
    }
}
