<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\UploadHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveHomeAgencyRequest;
use App\Models\HomeAgency;
use App\Models\Language;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Related agencies (logo grid at the bottom of the home page), admin menu id 2.
 */
class HomeAgencyController extends Controller
{
    public function index(): View
    {
        return view('admin.content.agencies', [
            'agencies' => HomeAgency::query()->ordered()->with('translations')->get(),
            'defaultLocale' => $this->defaultLocale(),
        ]);
    }

    public function create(): View
    {
        return $this->form(new HomeAgency(['is_active' => true]));
    }

    public function store(SaveHomeAgencyRequest $request): RedirectResponse
    {
        DB::transaction(fn () => $this->save($request, new HomeAgency(['sort_order' => (int) HomeAgency::max('sort_order') + 1])));

        return redirect()->route('admin.content.agencies.index')->with('success', __('admin/home_content.saved'));
    }

    public function edit(HomeAgency $agency): View
    {
        return $this->form($agency->load('translations'));
    }

    public function update(SaveHomeAgencyRequest $request, HomeAgency $agency): RedirectResponse
    {
        DB::transaction(fn () => $this->save($request, $agency));

        return redirect()->route('admin.content.agencies.index')->with('success', __('admin/home_content.saved'));
    }

    public function destroy(HomeAgency $agency): RedirectResponse
    {
        $agency->delete();
        $this->renumber();

        return redirect()->route('admin.content.agencies.index')->with('success', __('admin/home_content.deleted'));
    }

    public function toggle(HomeAgency $agency): RedirectResponse
    {
        $agency->update(['is_active' => ! $agency->is_active]);

        return redirect()->route('admin.content.agencies.index')->with('success', __('admin/home_content.saved'));
    }

    /**
     * Swap with the neighbour above or below, then keep the order as 1..n.
     */
    public function move(Request $request, HomeAgency $agency): RedirectResponse
    {
        $direction = $request->validate(['direction' => ['required', 'in:up,down']])['direction'];
        $ids = HomeAgency::query()->ordered()->pluck('id')->all();
        $position = array_search($agency->id, $ids, true);
        $target = $direction === 'up' ? $position - 1 : $position + 1;

        if ($position !== false && isset($ids[$target])) {
            [$ids[$position], $ids[$target]] = [$ids[$target], $ids[$position]];
            foreach ($ids as $index => $id) {
                HomeAgency::whereKey($id)->update(['sort_order' => $index + 1]);
            }
        }

        return redirect()->route('admin.content.agencies.index');
    }

    protected function defaultLocale(): ?string
    {
        return Language::active()->where('is_default', 'y')->value('code');
    }

    protected function form(HomeAgency $agency): View
    {
        return view('admin.content.agency-form', [
            'agency' => $agency,
            'languages' => Language::active()->orderBy('sort_order')->get(),
            'defaultLocale' => $this->defaultLocale(),
        ]);
    }

    protected function save(SaveHomeAgencyRequest $request, HomeAgency $agency): void
    {
        $data = $request->validated();
        $agency->fill(['url' => $data['url'], 'is_active' => $request->boolean('is_active')]);

        if ($request->hasFile('logo')) {
            UploadHelper::delete($agency->logo_path);
            $agency->logo_path = UploadHelper::store($request->file('logo'), 'home/agencies');
        }
        $agency->save();

        foreach (Language::active()->pluck('code') as $locale) {
            $name = trim(strip_tags((string) ($data['translations'][$locale]['name'] ?? '')));
            if ($name === '') {
                $agency->translations()->where('locale', $locale)->delete();

                continue;
            }
            $agency->translations()->updateOrCreate(['locale' => $locale], ['name' => $name]);
        }
    }

    protected function renumber(): void
    {
        foreach (HomeAgency::query()->ordered()->pluck('id') as $index => $id) {
            HomeAgency::whereKey($id)->update(['sort_order' => $index + 1]);
        }
    }
}
