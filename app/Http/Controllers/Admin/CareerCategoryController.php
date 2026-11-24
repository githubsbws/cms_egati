<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CareerCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Job families used by the recruitment notices. One that positions use is switched off instead of deleted.
 */
class CareerCategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.careers.categories', [
            'items' => CareerCategory::query()->withCount('jobs')->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('career_categories', 'name')],
            'name_en' => ['nullable', 'string', 'max:255'],
        ]);

        CareerCategory::create([
            'name' => $this->clean($data['name']),
            'name_en' => $this->clean($data['name_en'] ?? '') ?: null,
            'sort_order' => (int) CareerCategory::max('sort_order') + 1,
            'is_active' => true,
        ]);

        return redirect()->route('admin.careers.categories.index')->with('success', __('admin/careers.saved'));
    }

    public function update(Request $request, CareerCategory $category): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('career_categories', 'name')->ignore($category->id)],
            'name_en' => ['nullable', 'string', 'max:255'],
        ]);

        $category->update([
            'name' => $this->clean($data['name']),
            'name_en' => $this->clean($data['name_en'] ?? '') ?: null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.careers.categories.index')->with('success', __('admin/careers.saved'));
    }

    public function destroy(CareerCategory $category): RedirectResponse
    {
        if ($category->jobs()->withTrashed()->exists()) {
            return redirect()->route('admin.careers.categories.index')->withErrors(__('admin/careers.lookup_in_use'));
        }
        $category->delete();

        return redirect()->route('admin.careers.categories.index')->with('success', __('admin/careers.deleted'));
    }

    private function clean(string $value): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', strip_tags($value)));
    }
}
