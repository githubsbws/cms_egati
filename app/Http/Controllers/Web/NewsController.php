<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Language;
use App\Models\Post;
use App\Models\PostTranslation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    private const PAGE_SIZE = 9;

    /**
     * Corporate video page: plays the same video as the home page presentation.
     */
    public function videos(): View
    {
        return view('web.news.videos');
    }

    public function index(Request $request, string $locale): View
    {
        // Invalid filter values are ignored (public page, no redirect back).
        $filters = validator($request->query(), [
            'category' => ['nullable', 'string', 'max:50'],
            'year' => ['nullable', 'integer', 'min:1990', 'max:2200'],
            'q' => ['nullable', 'string', 'max:100'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:200'],
        ])->valid();
        $limit = max(self::PAGE_SIZE, (int) ($filters['limit'] ?? self::PAGE_SIZE));

        $base = fn () => Post::published()->translatedIn($locale)->with(['translations', 'category.translations']);
        $featured = $base()->orderByDesc('published_at')->limit(5)->get();

        $results = $base()->orderByDesc('published_at');
        if (! empty($filters['category'])) {
            $results->whereHas('category', fn ($q) => $q->where('code', $filters['category']));
        }
        if (! empty($filters['year'])) {
            $results->whereYear('published_at', (int) $filters['year']);
        }
        if (! empty($filters['q'])) {
            $term = '%'.addcslashes($filters['q'], '%_\\').'%';
            $results->whereHas('translations', fn ($q) => $q->where('locale', $locale)
                ->where(fn ($w) => $w->where('title', 'ilike', $term)->orWhere('content', 'ilike', $term)));
        }
        $total = (clone $results)->count();

        return view('web.news.index', [
            'featured' => $featured,
            'posts' => $results->limit($limit)->get(),
            'total' => $total,
            'limit' => $limit,
            'pageSize' => self::PAGE_SIZE,
            'filters' => $filters,
            'categories' => Category::with('translations')->orderBy('sort_order')->get(),
            'years' => Post::published()->translatedIn($locale)->selectRaw('extract(year from published_at)::int as year')->distinct()->orderByDesc('year')->pluck('year'),
        ]);
    }

    public function show(string $locale, string $slug): View
    {
        $translation = PostTranslation::where('locale', $locale)->where('slug', $slug)->firstOrFail();
        $post = Post::published()->with(['translations', 'category.translations', 'images'])->findOrFail($translation->post_id);

        $related = Post::published()
            ->translatedIn($locale)
            ->whereKeyNot($post->id)
            ->with(['translations', 'category.translations'])
            ->orderByDesc('published_at')
            ->limit(2)
            ->get();

        // Language switcher: go to the same article when it is translated, else to the news list.
        $alternateUrls = Language::active()->pluck('code')->mapWithKeys(function (string $code) use ($post): array {
            $other = $post->translation($code);

            return [$code => $other ? web_url('news/'.$other->slug, $code) : web_url('news', $code)];
        })->all();
        view()->share('alternateUrls', $alternateUrls);

        return view('web.news.show', [
            'post' => $post,
            'translation' => $translation,
            'related' => $related,
            'alternateUrls' => $alternateUrls,
        ]);
    }
}
