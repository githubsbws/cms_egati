<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\UploadHelper;
use App\Helpers\WebStrings;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveLanguageRequest;
use App\Models\Language;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Website languages (Settings > Language): add/edit a language, its flag and its string file.
 * The back office itself stays fixed at th/en (lang/ folder) and is not managed here.
 */
class LanguageController extends Controller
{
    public function index(): View
    {
        $languages = Language::orderBy('sort_order')->orderBy('code')->get()
            ->each(fn (Language $language) => $language->strings_count = count(WebStrings::read($language->code)));

        return view('admin.settings.language.index', ['languages' => $languages]);
    }

    public function create(): View
    {
        return view('admin.settings.language.form', [
            'language' => new Language(['direction' => 'ltr', 'sort_order' => (Language::max('sort_order') ?? 0) + 1, 'is_active' => 'n', 'is_default' => 'n']),
            'defaultLanguage' => $this->defaultLanguage(),
        ]);
    }

    public function store(SaveLanguageRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Start from the uploaded file, else from a copy of the default language, so the editor has something to edit.
        $strings = $request->hasFile('json_file')
            ? WebStrings::parse((string) file_get_contents($request->file('json_file')->getRealPath()))
            : WebStrings::read((string) $this->defaultLanguage()?->code);

        $language = DB::transaction(function () use ($request, $data, $strings): Language {
            $language = new Language;
            $language->code = $data['code'];
            $this->fillAndSave($request, $language, $data);
            WebStrings::write($language, $strings);

            return $language;
        });

        return redirect()->route('admin.settings.language.edit', $language)->with('success', __('admin/language.saved'));
    }

    public function edit(Language $language): View
    {
        return view('admin.settings.language.form', [
            'language' => $language,
            'defaultLanguage' => $this->defaultLanguage(),
        ]);
    }

    public function update(SaveLanguageRequest $request, Language $language): RedirectResponse
    {
        $data = $request->validated();
        DB::transaction(fn () => $this->fillAndSave($request, $language, $data));

        return redirect()->route('admin.settings.language.edit', $language)->with('success', __('admin/language.saved'));
    }

    /**
     * Edit the string file of a language as JSON text.
     */
    public function strings(Language $language): View
    {
        return view('admin.settings.language.strings', [
            'language' => $language,
            'json' => json_encode(WebStrings::read($language->code), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    /**
     * Save the edited JSON text over the language file.
     */
    public function saveStrings(Request $request, Language $language): RedirectResponse
    {
        $request->validate(['json' => ['required', 'string', 'max:1048576']]);

        WebStrings::write($language, WebStrings::parse($request->input('json')));

        return redirect()->route('admin.settings.language.strings', $language)->with('success', __('admin/language.saved'));
    }

    /**
     * Replace the language file with an uploaded JSON file.
     */
    public function uploadStrings(Request $request, Language $language): RedirectResponse
    {
        $request->validate(['json_file' => ['required', 'file', 'mimetypes:application/json,text/plain', 'max:1024']]);

        WebStrings::write($language, WebStrings::parse((string) file_get_contents($request->file('json_file')->getRealPath())));

        return redirect()->route('admin.settings.language.strings', $language)->with('success', __('admin/language.saved'));
    }

    /**
     * Download the file of a language (the default language's file is the template for new languages).
     */
    public function download(Language $language): StreamedResponse
    {
        $contents = WebStrings::fileContents($language);

        return response()->streamDownload(fn () => print ($contents), "{$language->code}.json", ['Content-Type' => 'application/json']);
    }

    protected function defaultLanguage(): ?Language
    {
        return Language::where('is_default', 'y')->first();
    }

    /**
     * Rules kept here: the default language is always active and cannot be un-defaulted (pick another
     * language as default instead), and a language can only be switched on if it has a string file.
     *
     * @param  array<string, mixed>  $data
     */
    protected function fillAndSave(SaveLanguageRequest $request, Language $language, array $data): void
    {
        $wasDefault = $language->exists && $language->is_default === 'y';
        $makeDefault = $wasDefault || $request->boolean('is_default');
        $active = $makeDefault || $request->boolean('is_active');

        if ($active && $language->exists && ! WebStrings::exists($language->code)) {
            throw ValidationException::withMessages(['is_active' => __('admin/language.needs_strings')]);
        }

        $language->fill([
            'name' => $data['name'],
            'native_name' => $data['native_name'],
            'direction' => $data['direction'],
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $active ? 'y' : 'n',
        ]);

        if ($request->hasFile('flag') || $request->boolean('remove_flag')) {
            UploadHelper::delete($language->flag);
            $language->flag = $request->hasFile('flag') ? UploadHelper::store($request->file('flag'), 'languages') : null;
        }

        if ($makeDefault && ! $wasDefault) {
            Language::where('is_default', 'y')->update(['is_default' => 'n']);
        }
        $language->is_default = $makeDefault ? 'y' : 'n';
        $language->save();

        Cache::forget('web.active_locales');
    }
}
