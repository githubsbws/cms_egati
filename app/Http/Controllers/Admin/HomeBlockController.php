<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\HomeContent;
use App\Helpers\UploadHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveHomeBlockRequest;
use App\Models\HomeBlock;
use App\Models\Language;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Home page blocks of admin menu id 2: hero video, about story (3 slides) and video presentation.
 * $section comes from the route and picks the blocks listed in config/home_content.php.
 */
class HomeBlockController extends Controller
{
    public function edit(string $section): View
    {
        $definition = config("home_content.sections.{$section}") ?? abort(404);

        return view('admin.content.blocks', [
            'section' => $section,
            'menuKey' => $definition['menu_key'],
            'blocks' => $definition['blocks'],
            'saved' => HomeBlock::query()->with('texts')->whereIn('code', array_keys($definition['blocks']))->get()->keyBy('code'),
            'languages' => Language::active()->orderBy('sort_order')->get(),
            'defaultLocale' => Language::active()->where('is_default', 'y')->value('code'),
        ]);
    }

    public function update(SaveHomeBlockRequest $request, string $section): RedirectResponse
    {
        $definition = config("home_content.sections.{$section}") ?? abort(404);
        $locales = Language::active()->pluck('code');

        DB::transaction(function () use ($request, $definition, $locales) {
            foreach ($definition['blocks'] as $code => $block) {
                $this->saveBlock($request, $code, $block, $locales->all());
            }
        });
        HomeContent::flush();

        return redirect()->route("admin.content.{$section}.edit")->with('success', __('admin/home_content.saved'));
    }

    /**
     * @param  array<string, mixed>  $definition
     * @param  list<string>  $locales
     */
    protected function saveBlock(SaveHomeBlockRequest $request, string $code, array $definition, array $locales): void
    {
        $block = HomeBlock::firstOrNew(['code' => $code]);

        foreach (['video', 'poster'] as $kind) {
            if ($request->hasFile("blocks.{$code}.{$kind}")) {
                UploadHelper::delete($block->{$kind.'_path'});
                $block->{$kind.'_path'} = UploadHelper::store($request->file("blocks.{$code}.{$kind}"), 'home');
            }
        }

        $plain = [];
        foreach ($definition['plain'] ?? [] as $field => $meta) {
            if (($meta['admin'] ?? true) === false) {
                continue;
            }
            $value = trim(strip_tags((string) $request->input("blocks.{$code}.plain.{$field}", '')));
            if ($value !== '') {
                $plain[$field] = $value;
            }
        }
        $block->plain = $plain;
        $block->updated_by = Auth::id();
        $block->save();

        foreach ($locales as $locale) {
            $fields = [];
            foreach ($definition['texts'] as $field => $meta) {
                if (($meta['admin'] ?? true) === false) {
                    continue;
                }
                $value = trim((string) $request->input("blocks.{$code}.texts.{$locale}.{$field}", ''));
                $value = trim(strip_tags($value));
                if ($value !== '') {
                    $fields[$field] = $value;
                }
            }
            if ($fields === []) {
                $block->texts()->where('locale', $locale)->delete();
            } else {
                $block->texts()->updateOrCreate(['locale' => $locale], ['fields' => $fields]);
            }
        }
    }
}
