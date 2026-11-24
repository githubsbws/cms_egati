<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\UploadHelper;
use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\OrgChart;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Company structure chart: one image per language. A language without its own image uses the default language's.
 */
class OrgChartController extends Controller
{
    public function index(): View
    {
        return view('admin.organization.chart', [
            'languages' => Language::active()->orderBy('sort_order')->get(),
            'charts' => OrgChart::all()->keyBy('locale'),
            'defaultLocale' => Language::active()->where('is_default', 'y')->value('code'),
        ]);
    }

    public function store(Request $request, Language $language): RedirectResponse
    {
        abort_unless($language->is_active === 'y', 404);
        $request->validate(['image' => ['required', 'image', 'mimes:png,jpg,jpeg,webp', 'max:8192']]);

        $chart = OrgChart::find($language->code) ?? new OrgChart(['locale' => $language->code]);
        UploadHelper::delete($chart->image_path);
        $chart->image_path = UploadHelper::store($request->file('image'), 'organization');
        $chart->save();

        return redirect()->route('admin.organization.chart.index')->with('success', __('admin/organization.saved'));
    }

    public function destroy(Language $language): RedirectResponse
    {
        $chart = OrgChart::find($language->code);
        if ($chart) {
            UploadHelper::delete($chart->image_path);
            $chart->delete();
        }

        return redirect()->route('admin.organization.chart.index')->with('success', __('admin/organization.deleted'));
    }
}
