<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\UploadHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveOrgMemberRequest;
use App\Models\Language;
use App\Models\OrgGroup;
use App\Models\OrgMember;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

/**
 * People of the board + committees ("board" page) and of the management team ("management" page).
 * $section comes from the route: it limits which groups the page can touch.
 */
class OrgMemberController extends Controller
{
    public function index(string $section): View
    {
        $groups = OrgGroup::forSection($section)->with('translations')->withCount('members')->orderBy('sort_order')->orderBy('id')->get();
        $current = $groups->firstWhere('code', request('group')) ?? $groups->first();

        return view('admin.organization.members', [
            'section' => $section,
            'groups' => $groups,
            'current' => $current,
            'members' => $current ? $current->members()->with('translations')->get() : collect(),
            'defaultLocale' => $this->defaultLocale(),
        ]);
    }

    public function create(string $section): View
    {
        $groups = $this->groups($section);
        $selected = $groups->firstWhere('code', request('group')) ?? $groups->first();

        return $this->form($section, new OrgMember(['group_id' => $selected?->id, 'is_active' => true]), $groups);
    }

    public function store(SaveOrgMemberRequest $request, string $section): RedirectResponse
    {
        $group = $this->groups($section)->firstWhere('id', (int) $request->input('group_id')) ?? abort(404);
        DB::transaction(fn () => $this->save($request, new OrgMember));

        return redirect()->route("admin.organization.{$section}.index", ['group' => $group->code])->with('success', __('admin/organization.saved'));
    }

    public function edit(OrgMember $member, string $section): View
    {
        $groups = $this->groups($section);
        abort_unless($groups->contains('id', $member->group_id), 404);

        return $this->form($section, $member->load('translations'), $groups);
    }

    public function update(SaveOrgMemberRequest $request, OrgMember $member, string $section): RedirectResponse
    {
        $groups = $this->groups($section);
        abort_unless($groups->contains('id', $member->group_id), 404);
        $group = $groups->firstWhere('id', (int) $request->input('group_id')) ?? abort(404);
        DB::transaction(fn () => $this->save($request, $member));

        return redirect()->route("admin.organization.{$section}.index", ['group' => $group->code])->with('success', __('admin/organization.saved'));
    }

    public function destroy(OrgMember $member, string $section): RedirectResponse
    {
        $groups = $this->groups($section);
        abort_unless($groups->contains('id', $member->group_id), 404);
        $code = $groups->firstWhere('id', $member->group_id)->code;
        $member->delete();

        return redirect()->route("admin.organization.{$section}.index", ['group' => $code])->with('success', __('admin/organization.deleted'));
    }

    /**
     * @return Collection<int, OrgGroup>
     */
    protected function groups(string $section)
    {
        return OrgGroup::forSection($section)->with('translations')->orderBy('sort_order')->orderBy('id')->get();
    }

    protected function defaultLocale(): ?string
    {
        return Language::active()->where('is_default', 'y')->value('code');
    }

    /**
     * @param  Collection<int, OrgGroup>  $groups
     */
    protected function form(string $section, OrgMember $member, $groups): View
    {
        return view('admin.organization.member-form', [
            'section' => $section,
            'member' => $member,
            'groups' => $groups,
            'languages' => Language::active()->orderBy('sort_order')->get(),
            'defaultLocale' => $this->defaultLocale(),
        ]);
    }

    protected function save(SaveOrgMemberRequest $request, OrgMember $member): OrgMember
    {
        $data = $request->validated();
        $member->fill([
            'group_id' => $data['group_id'],
            'is_active' => $request->boolean('is_active'),
        ]);

        if ($request->boolean('remove_photo') || $request->hasFile('photo')) {
            UploadHelper::delete($member->photo_path);
            $member->photo_path = $request->hasFile('photo') ? UploadHelper::store($request->file('photo'), 'organization') : null;
        }
        $member->save();

        foreach (Language::active()->pluck('code') as $locale) {
            $row = $data['translations'][$locale] ?? [];
            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                $member->translations()->where('locale', $locale)->delete();

                continue;
            }
            $member->translations()->updateOrCreate(['locale' => $locale], ['name' => $name, 'role' => trim((string) ($row['role'] ?? '')) ?: null]);
        }

        return $member;
    }
}
