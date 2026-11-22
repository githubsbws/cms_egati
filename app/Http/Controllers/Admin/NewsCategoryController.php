<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveNewsCategoryRequest;
use App\Models\Category;
use App\Models\Language;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NewsCategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.news.category.index', [
            'categories' => Category::with('translations')->withCount('posts')->orderBy('sort_order')->orderBy('id')->get(),
            'defaultLocale' => $this->defaultLocale(),
        ]);
    }

    public function create(): View
    {
        return $this->form(new Category(['accent_color' => '#0756a0', 'sort_order' => 0]));
    }

    public function store(SaveNewsCategoryRequest $request): RedirectResponse
    {
        $category = DB::transaction(fn (): Category => $this->save($request, new Category));

        return redirect()->route('admin.news.category.edit', $category)->with('success', __('admin/news.saved'));
    }

    public function edit(Category $category): View
    {
        return $this->form($category->load('translations'));
    }

    public function update(SaveNewsCategoryRequest $request, Category $category): RedirectResponse
    {
        DB::transaction(fn (): Category => $this->save($request, $category));

        return redirect()->route('admin.news.category.edit', $category)->with('success', __('admin/news.saved'));
    }

    /**
     * A category that still has news cannot be deleted.
     */
    public function destroy(Category $category): RedirectResponse
    {
        if ($category->posts()->exists()) {
            return redirect()->route('admin.news.category.index')->withErrors(__('admin/news.category_has_posts'));
        }
        $category->delete();

        return redirect()->route('admin.news.category.index')->with('success', __('admin/news.deleted'));
    }

    protected function form(Category $category): View
    {
        return view('admin.news.category.form', [
            'category' => $category,
            'languages' => Language::active()->orderBy('sort_order')->get(),
            'defaultLocale' => $this->defaultLocale(),
        ]);
    }

    protected function defaultLocale(): ?string
    {
        return Language::active()->where('is_default', 'y')->value('code');
    }

    /**
     * The code (used in /news?category=code) is made once from an English-readable name and never changes.
     */
    protected function save(SaveNewsCategoryRequest $request, Category $category): Category
    {
        $data = $request->validated();
        $names = array_filter(array_map('trim', $data['names']), fn (string $name): bool => $name !== '');

        $category->fill([
            'accent_color' => $data['accent_color'],
            'sort_order' => $data['sort_order'] ?? 0,
        ]);
        if (! $category->exists) {
            $category->code = $this->makeCode($names);
        }
        $category->save();

        foreach (Language::active()->pluck('code') as $locale) {
            if (isset($names[$locale])) {
                $category->translations()->updateOrCreate(['locale' => $locale], ['name' => $names[$locale]]);
            } else {
                $category->translations()->where('locale', $locale)->delete();
            }
        }

        return $category;
    }

    /**
     * @param  array<string, string>  $names
     */
    protected function makeCode(array $names): string
    {
        $base = collect($names)->map(fn (string $name): string => Str::slug($name))->first(fn (string $slug): bool => $slug !== '')
            ?: 'category-'.Str::lower(Str::random(5));
        $base = Str::limit($base, 40, '');
        $code = $base;
        for ($i = 2; Category::where('code', $code)->exists(); $i++) {
            $code = $base.'-'.$i;
        }

        return $code;
    }
}
