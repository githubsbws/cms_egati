<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\UploadHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveOrgGroupRequest;
use App\Models\Language;
use App\Models\OrgGroup;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Committees under the board (add / rename / appointment document / remove) and the names of the
 * main groups. The main groups (board, management) can be renamed but not removed.
 */
class OrgGroupController extends Controller
{
    public function create(string $section): View
    {
        abort_unless($section === 'board', 404);

        return $this->form($section, new OrgGroup(['type' => 'committee', 'is_active' => true, 'sort_order' => (OrgGroup::max('sort_order') ?? 0) + 1]));
    }

    public function store(SaveOrgGroupRequest $request, string $section): RedirectResponse
    {
        abort_unless($section === 'board', 404);
        $group = DB::transaction(function () use ($request): OrgGroup {
            $group = new OrgGroup(['type' => 'committee']);
            $group->code = $this->makeCode($request->validated()['names']);

            return $this->save($request, $group);
        });

        return redirect()->route("admin.organization.{$section}.index", ['group' => $group->code])->with('success', __('admin/organization.saved'));
    }

    public function edit(OrgGroup $group, string $section): View
    {
        $this->ensureInSection($section, $group);

        return $this->form($section, $group->load('translations'));
    }

    public function update(SaveOrgGroupRequest $request, OrgGroup $group, string $section): RedirectResponse
    {
        $this->ensureInSection($section, $group);
        DB::transaction(fn () => $this->save($request, $group));

        return redirect()->route("admin.organization.{$section}.index", ['group' => $group->code])->with('success', __('admin/organization.saved'));
    }

    public function destroy(OrgGroup $group, string $section): RedirectResponse
    {
        $this->ensureInSection($section, $group);
        if ($group->type !== 'committee') {
            return back()->withErrors(__('admin/organization.cannot_delete_main'));
        }
        if ($group->members()->exists()) {
            return back()->withErrors(__('admin/organization.group_has_members'));
        }
        UploadHelper::delete($group->document_path);
        $group->delete();

        return redirect()->route("admin.organization.{$section}.index")->with('success', __('admin/organization.deleted'));
    }

    protected function ensureInSection(string $section, OrgGroup $group): void
    {
        abort_unless(OrgGroup::forSection($section)->whereKey($group->id)->exists(), 404);
    }

    protected function form(string $section, OrgGroup $group): View
    {
        return view('admin.organization.group-form', [
            'section' => $section,
            'group' => $group,
            'languages' => Language::active()->orderBy('sort_order')->get(),
            'defaultLocale' => Language::active()->where('is_default', 'y')->value('code'),
        ]);
    }

    protected function save(SaveOrgGroupRequest $request, OrgGroup $group): OrgGroup
    {
        $data = $request->validated();
        $group->fill([
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $group->type === 'committee' ? $request->boolean('is_active') : true,
        ]);

        if ($request->boolean('remove_document') || $request->hasFile('document')) {
            UploadHelper::delete($group->document_path);
            $group->document_path = $request->hasFile('document') ? UploadHelper::store($request->file('document'), 'organization/documents') : null;
        }
        $group->save();

        foreach (Language::active()->pluck('code') as $locale) {
            $name = trim((string) ($data['names'][$locale] ?? ''));
            if ($name === '') {
                $group->translations()->where('locale', $locale)->delete();
            } else {
                $group->translations()->updateOrCreate(['locale' => $locale], ['name' => $name]);
            }
        }

        return $group;
    }

    /**
     * @param  array<string, string|null>  $names
     */
    protected function makeCode(array $names): string
    {
        $base = collect($names)->map(fn (?string $name): string => Str::slug((string) $name))->first(fn (string $slug): bool => $slug !== '')
            ?: 'committee-'.Str::lower(Str::random(5));
        $base = Str::limit($base, 40, '');
        $code = $base;
        for ($i = 2; OrgGroup::where('code', $code)->exists(); $i++) {
            $code = $base.'-'.$i;
        }

        return $code;
    }
}
