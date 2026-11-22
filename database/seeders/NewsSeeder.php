<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\PostTranslation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Loads the news from the design draft (th + en). Safe to run again: a post is
 * skipped when its slug already exists.
 */
class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $data = json_decode(file_get_contents(database_path('seeders/data/news.json')), true, 512, JSON_THROW_ON_ERROR);

        DB::transaction(function () use ($data): void {
            $categories = [];
            foreach ($data['categories'] as $row) {
                $category = Category::updateOrCreate(
                    ['code' => $row['code']],
                    ['accent_color' => $row['color'], 'sort_order' => $row['sort']],
                );
                $category->translations()->updateOrCreate(['locale' => 'th'], ['name' => $row['th']]);
                $category->translations()->updateOrCreate(['locale' => 'en'], ['name' => $row['en']]);
                $categories[$row['code']] = $category->id;
            }

            foreach ($data['items'] as $item) {
                $slug = Str::limit(Str::slug($item['titleEn'] ?? ''), 80, '') ?: 'news-'.$item['id'];
                if (PostTranslation::where('slug', $slug)->exists()) {
                    continue;
                }
                $this->createPost($item, $slug, $categories[$item['category']] ?? null);
            }
        });
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function createPost(array $item, string $slug, ?int $categoryId): void
    {
        $post = Post::create([
            'category_id' => $categoryId,
            'cover_image' => $item['images'][0] ?? null,
            'status' => 'published',
            'published_at' => $item['date'].' 09:00:00',
        ]);

        $post->translations()->create([
            'locale' => 'th',
            'title' => $item['title'],
            'slug' => $slug,
            'excerpt' => $item['body'][0] ?? null,
            'content' => $this->toHtml($item['body']).$this->imagesHtml($item),
        ]);
        $post->translations()->create([
            'locale' => 'en',
            'title' => $item['titleEn'],
            'slug' => $slug,
            'excerpt' => $item['summaryEn'] ?? null,
            'content' => $this->toHtml($item['bodyEn'] ?? []).$this->imagesHtml($item),
        ]);
    }

    /**
     * @param  array<int, string>  $paragraphs
     */
    protected function toHtml(array $paragraphs): string
    {
        return collect($paragraphs)->map(fn (string $p): string => '<p>'.e($p).'</p>')->implode("\n");
    }

    /**
     * Photos after the first (the cover) go inside the article body.
     *
     * @param  array<string, mixed>  $item
     */
    protected function imagesHtml(array $item): string
    {
        return collect(array_slice($item['images'], 1))
            ->map(fn (string $path): string => '<p><img src="/'.e($path).'" alt=""></p>')
            ->implode("\n");
    }
}
