<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Post;
use App\Models\PostTranslation;
use App\Models\Users;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Runs on the real PostgreSQL connection (pdo_sqlite is not available) and rolls back
 * every test. Run with: DB_CONNECTION=pgsql DB_DATABASE=<name> php artisan test
 */
class AdminNewsTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $admin = Users::where('superuser', 1)->firstOrFail();
        // EnsureSingleSession compares this with users._token, as the login form would set it.
        $this->withSession(['session_token' => $admin->_token])->actingAs($admin);
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(array $override = []): array
    {
        return array_replace_recursive([
            'status' => 'published',
            'translations' => [
                'th' => ['title' => 'ข่าวทดสอบ', 'slug' => '', 'excerpt' => '', 'content' => '<p>เนื้อหา</p>'],
                'en' => ['title' => 'Test news item', 'slug' => '', 'excerpt' => '', 'content' => '<p>Body</p>'],
            ],
        ], $override);
    }

    public function test_index_and_create_pages_load(): void
    {
        $this->get(route('admin.news.index'))->assertOk();
        $this->get(route('admin.news.create'))->assertOk();
    }

    public function test_store_creates_post_with_both_languages_and_unique_slugs(): void
    {
        $this->post(route('admin.news.store'), $this->payload())->assertRedirect();
        $this->post(route('admin.news.store'), $this->payload())->assertRedirect();

        $slugs = PostTranslation::where('locale', 'en')->where('slug', 'like', 'test-news-item%')->pluck('slug')->all();
        $this->assertContains('test-news-item', $slugs);
        $this->assertContains('test-news-item-2', $slugs);
    }

    public function test_default_language_title_is_required(): void
    {
        $this->post(route('admin.news.store'), $this->payload(['translations' => ['th' => ['title' => '']]]))
            ->assertSessionHasErrors('translations.th.title');
    }

    public function test_content_is_sanitized_before_saving(): void
    {
        $this->post(route('admin.news.store'), $this->payload([
            'translations' => ['en' => ['title' => 'Xss check', 'content' => '<p onclick="a()">Hi</p><script>alert(1)</script>']],
        ]))->assertRedirect();

        $saved = PostTranslation::where('locale', 'en')->where('slug', 'xss-check')->firstOrFail();
        $this->assertStringNotContainsString('script', $saved->content);
        $this->assertStringNotContainsString('onclick', $saved->content);
    }

    public function test_update_removes_a_language_when_its_title_is_cleared(): void
    {
        $this->post(route('admin.news.store'), $this->payload())->assertRedirect();
        $post = PostTranslation::where('locale', 'en')->where('slug', 'test-news-item')->firstOrFail()->post;

        $this->put(route('admin.news.update', $post), $this->payload(['translations' => ['en' => ['title' => '']]]))->assertRedirect();

        $this->assertNull($post->translations()->where('locale', 'en')->first());
        $this->assertNotNull($post->translations()->where('locale', 'th')->first());
    }

    public function test_typed_slug_that_is_taken_is_rejected(): void
    {
        $this->post(route('admin.news.store'), $this->payload())->assertRedirect();

        $this->post(route('admin.news.store'), $this->payload(['translations' => ['en' => ['slug' => 'test-news-item']]]))
            ->assertSessionHasErrors('translations.en.slug');
    }

    public function test_cover_is_stored_in_the_news_folder(): void
    {
        $this->post(route('admin.news.store'), $this->payload([
            'cover' => UploadedFile::fake()->image('cover.jpg'),
        ]))->assertRedirect();

        $post = PostTranslation::where('locale', 'en')->where('slug', 'test-news-item')->firstOrFail()->post;
        $this->assertStringStartsWith('news/'.now()->format('Y/m'), $post->cover_image);
        Storage::disk('public')->assertExists($post->cover_image);
    }

    public function test_event_photos_are_added_shown_on_the_article_and_removed(): void
    {
        $this->post(route('admin.news.store'), $this->payload([
            'gallery' => [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.png')],
        ]))->assertRedirect()->assertSessionHasNoErrors();

        $post = PostTranslation::where('locale', 'en')->where('slug', 'test-news-item')->firstOrFail()->post;
        $this->assertSame(2, $post->images()->count());
        [$first, $second] = $post->images->all();
        Storage::disk('public')->assertExists([$first->path, $second->path]);
        $this->assertSame([1, 2], $post->images->pluck('sort_order')->all());

        $this->get('/en/news/test-news-item')->assertOk()
            ->assertSee('data-nr-image="0"', false)->assertSee('data-nr-image="1"', false)
            ->assertSee(Storage::disk('public')->url($first->path), false);

        $this->put(route('admin.news.update', $post), $this->payload([
            'remove_images' => [$first->id],
            'gallery' => [UploadedFile::fake()->image('c.webp')],
        ]))->assertSessionHasNoErrors();

        Storage::disk('public')->assertMissing($first->path);
        $this->assertSame(2, $post->images()->count());
        $this->assertSame(3, $post->images()->max('sort_order'));
    }

    public function test_event_photo_validation(): void
    {
        $this->post(route('admin.news.store'), $this->payload(['gallery' => [UploadedFile::fake()->create('a.pdf', 10, 'application/pdf')]]))
            ->assertSessionHasErrors('gallery.0');
    }

    public function test_imported_photos_moved_out_of_the_text_into_the_gallery(): void
    {
        $this->assertSame(0, PostTranslation::where('content', 'like', '%<img%')->count());
        $this->assertGreaterThan(0, Post::has('images')->count());
    }

    public function test_destroy_soft_deletes_and_hides_from_the_website(): void
    {
        $this->post(route('admin.news.store'), $this->payload())->assertRedirect();
        $post = PostTranslation::where('locale', 'en')->where('slug', 'test-news-item')->firstOrFail()->post;

        $this->delete(route('admin.news.destroy', $post))->assertRedirect(route('admin.news.index'));

        $this->assertSoftDeleted('posts', ['id' => $post->id]);
        $this->get('/en/news/test-news-item')->assertNotFound();
        $this->assertSame(0, Post::published()->where('id', $post->id)->count());
    }

    public function test_edit_page_shows_saved_values(): void
    {
        $this->post(route('admin.news.store'), $this->payload())->assertRedirect();
        $post = PostTranslation::where('locale', 'en')->where('slug', 'test-news-item')->firstOrFail()->post;

        $this->get(route('admin.news.edit', $post))->assertOk()->assertSee('Test news item')->assertSee('rich-editor', false);
    }

    public function test_editor_image_upload_returns_a_storage_location(): void
    {
        $response = $this->post(route('admin.editor.image'), [
            'file' => UploadedFile::fake()->image('inline.png'),
            'feature' => 'news',
        ])->assertOk();

        $location = $response->json('location');
        $this->assertStringStartsWith('/storage/news/'.now()->format('Y/m'), $location);
        Storage::disk('public')->assertExists(substr($location, strlen('/storage/')));
    }

    public function test_editor_image_upload_rejects_unknown_feature_and_non_images(): void
    {
        $this->postJson(route('admin.editor.image'), ['file' => UploadedFile::fake()->image('a.png'), 'feature' => 'other'])->assertUnprocessable();
        $this->postJson(route('admin.editor.image'), ['file' => UploadedFile::fake()->create('a.pdf', 10, 'application/pdf'), 'feature' => 'news'])->assertUnprocessable();
    }

    public function test_category_can_be_created_edited_and_deleted_when_unused(): void
    {
        $this->get(route('admin.news.category.index'))->assertOk();
        $this->get(route('admin.news.category.create'))->assertOk();

        $this->post(route('admin.news.category.store'), [
            'names' => ['th' => 'หมวดทดสอบ', 'en' => 'Test category'],
            'accent_color' => '#112233',
            'sort_order' => 5,
        ])->assertRedirect();

        $category = Category::where('code', 'test-category')->firstOrFail();
        $this->assertSame('Test category', $category->nameFor('en'));
        $this->get(route('admin.news.category.edit', $category))->assertOk();

        $this->delete(route('admin.news.category.destroy', $category))->assertRedirect();
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_category_with_news_cannot_be_deleted(): void
    {
        $category = Category::factory()->create();
        $this->post(route('admin.news.store'), $this->payload(['category_id' => $category->id]))->assertRedirect();

        $this->delete(route('admin.news.category.destroy', $category))->assertSessionHasErrors();
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_category_color_must_be_a_hex_value(): void
    {
        $this->post(route('admin.news.category.store'), ['names' => ['th' => 'x'], 'accent_color' => 'red'])
            ->assertSessionHasErrors('accent_color');
    }
}
