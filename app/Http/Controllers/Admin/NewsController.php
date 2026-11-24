<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\HtmlSanitizer;
use App\Helpers\UploadHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveNewsRequest;
use App\Models\Category;
use App\Models\Language;
use App\Models\Post;
use App\Models\PostTranslation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class NewsController extends Controller
{
    /**
     * News list with search (title), category and status filters.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'integer'],
            'status' => ['nullable', 'in:draft,published'],
        ]);

        $posts = Post::query()
            ->with(['translations', 'category.translations'])
            ->when($filters['q'] ?? null, function ($query, string $term) {
                $like = '%'.addcslashes($term, '%_\\').'%';
                $query->whereHas('translations', fn ($q) => $q->where('title', 'ilike', $like));
            })
            ->when($filters['category'] ?? null, fn ($query, $id) => $query->where('category_id', $id))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.news.index', [
            'posts' => $posts,
            'categories' => Category::with('translations')->orderBy('sort_order')->get(),
            'defaultLocale' => $this->defaultLocale(),
        ]);
    }

    public function create(): View
    {
        return $this->form(new Post(['status' => 'draft']));
    }

    public function store(SaveNewsRequest $request): RedirectResponse
    {
        $post = DB::transaction(fn (): Post => $this->save($request, new Post));

        return redirect()->route('admin.news.edit', $post)->with('success', __('admin/news.saved'));
    }

    public function edit(Post $post): View
    {
        return $this->form($post->load('translations'));
    }

    public function update(SaveNewsRequest $request, Post $post): RedirectResponse
    {
        DB::transaction(fn (): Post => $this->save($request, $post));

        return redirect()->route('admin.news.edit', $post)->with('success', __('admin/news.saved'));
    }

    /**
     * Soft delete: the post disappears from the website but the row and files stay.
     */
    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        return redirect()->route('admin.news.index')->with('success', __('admin/news.deleted'));
    }

    protected function form(Post $post): View
    {
        return view('admin.news.form', [
            'post' => $post,
            'languages' => Language::active()->orderBy('sort_order')->get(),
            'defaultLocale' => $this->defaultLocale(),
            'categories' => Category::with('translations')->orderBy('sort_order')->get(),
        ]);
    }

    protected function defaultLocale(): ?string
    {
        return Language::active()->where('is_default', 'y')->value('code');
    }

    protected function save(SaveNewsRequest $request, Post $post): Post
    {
        $data = $request->validated();

        $post->fill([
            'category_id' => $data['category_id'] ?? null,
            'status' => $data['status'],
            'published_at' => $data['published_at'] ?? null ?: ($data['status'] === 'published' ? now() : null),
            'updated_by' => $request->user()->id,
        ]);
        if (! $post->exists) {
            $post->created_by = $request->user()->id;
        }
        $post->save();
        // Translations first: a slug error must not leave uploaded files behind.
        $this->saveTranslations($post, $data['translations']);
        $this->saveCover($request, $post);
        $this->saveGallery($request, $post);
        $post->save();

        return $post;
    }

    /**
     * Cover (replace/remove). The old file is deleted from disk. Other images go inside the rich text.
     */
    protected function saveCover(SaveNewsRequest $request, Post $post): void
    {
        if ($request->boolean('remove_cover') || $request->hasFile('cover')) {
            UploadHelper::delete($post->cover_image);
            $post->cover_image = null;
        }
        if ($request->hasFile('cover')) {
            $post->cover_image = UploadHelper::store($request->file('cover'), 'news');
        }
    }

    /**
     * Event photos: the ticked ones are removed (file deleted), new uploads are added after the existing ones.
     */
    protected function saveGallery(SaveNewsRequest $request, Post $post): void
    {
        foreach ($post->images()->whereIn('id', (array) $request->input('remove_images', []))->get() as $image) {
            UploadHelper::delete($image->path);
            $image->delete();
        }

        $order = (int) $post->images()->max('sort_order');
        foreach ((array) $request->file('gallery', []) as $file) {
            $post->images()->create(['path' => UploadHelper::store($file, 'news'), 'sort_order' => ++$order]);
        }
    }

    /**
     * One translation per active language. A language with an empty title is removed.
     *
     * @param  array<string, array<string, mixed>>  $translations
     */
    protected function saveTranslations(Post $post, array $translations): void
    {
        $existing = $post->translations()->get()->keyBy('locale');
        // Thai titles give no ASCII slug, so a language without one reuses the first readable slug of the others.
        $firstSlug = collect($translations)->map(fn (array $row): string => Str::slug((string) ($row['title'] ?? '')))->first(fn (string $slug): bool => $slug !== '');

        foreach (Language::active()->orderBy('sort_order')->pluck('code') as $locale) {
            $row = $translations[$locale] ?? [];
            $title = trim((string) ($row['title'] ?? ''));
            if ($title === '') {
                $existing->get($locale)?->delete();

                continue;
            }
            $content = HtmlSanitizer::clean($row['content'] ?? '');
            $excerpt = trim((string) ($row['excerpt'] ?? '')) ?: Str::limit(trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($content)))), 200);
            $current = $existing->get($locale);
            $slug = $this->resolveSlug($locale, $row['slug'] ?? null, $title, $firstSlug, $current);

            $post->translations()->updateOrCreate(
                ['locale' => $locale],
                ['title' => $title, 'slug' => $slug, 'excerpt' => $excerpt, 'content' => $content],
            );
        }
    }

    /**
     * A typed slug must be free (error if not). A blank one is made from the English title,
     * else reuses the first language's slug, else news-<random>, and gets -2, -3 ... if taken.
     */
    protected function resolveSlug(string $locale, ?string $typed, string $title, ?string $firstSlug, ?PostTranslation $current): string
    {
        $taken = fn (string $slug): bool => PostTranslation::where('locale', $locale)
            ->where('slug', $slug)
            ->when($current, fn ($q) => $q->whereKeyNot($current->id))
            ->exists();

        if ($typed) {
            if ($taken($typed)) {
                throw ValidationException::withMessages(["translations.{$locale}.slug" => __('admin/news.slug_taken')]);
            }

            return $typed;
        }
        if ($current && $current->slug) {
            return $current->slug;
        }

        $base = Str::limit(Str::slug($title), 100, '') ?: Str::limit((string) $firstSlug, 100, '') ?: 'news-'.Str::lower(Str::random(6));
        $slug = $base;
        for ($i = 2; $taken($slug); $i++) {
            $slug = $base.'-'.$i;
        }

        return $slug;
    }
}
