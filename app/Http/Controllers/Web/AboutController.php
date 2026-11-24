<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\OrgChart;
use App\Models\OrgGroup;
use App\Models\OrgMember;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * About pages that come from the back office organization menu: board + committees,
 * management team and the company structure chart.
 */
class AboutController extends Controller
{
    public function board(Request $request, string $locale): View
    {
        $groups = $this->activeGroups('board');
        $current = $groups->firstWhere('code', $request->query('committee')) ?? $groups->first();
        abort_unless($current, 404);

        return view('web.about.board', [
            'groups' => $groups,
            'current' => $current,
            'people' => $this->people($current, $groups->where('id', '!=', $current->id), $locale),
        ]);
    }

    public function management(string $locale): View
    {
        $group = OrgGroup::forSection('management')->where('is_active', true)->with('translations')->orderBy('sort_order')->first();
        abort_unless($group, 404);

        return view('web.about.management', [
            'group' => $group,
            'people' => $this->people($group, $this->activeGroups('board'), $locale),
        ]);
    }

    public function structure(string $locale): View
    {
        return view('web.about.structure', ['chart' => OrgChart::imageFor($locale)]);
    }

    /**
     * @return Collection<int, OrgGroup>
     */
    protected function activeGroups(string $section): Collection
    {
        return OrgGroup::forSection($section)->where('is_active', true)->with('translations')->orderBy('sort_order')->orderBy('id')->get();
    }

    /**
     * People of a group in display order. Each person also lists the other groups they sit in
     * ("Audit Committee - Member"). The same person is matched by the default language name.
     *
     * @param  Collection<int, OrgGroup>  $otherGroups
     * @return list<array{name: string, role: string, photo: ?string, assignments: list<string>}>
     */
    protected function people(OrgGroup $group, Collection $otherGroups, string $locale): array
    {
        $default = (string) Language::where('is_default', 'y')->value('code');
        $pick = fn (OrgMember $member) => $member->translation($locale) ?? $member->translation($default) ?? $member->translations->first();

        $others = OrgMember::whereIn('group_id', $otherGroups->pluck('id'))->where('is_active', true)->with('translations')->get()
            ->groupBy(fn (OrgMember $member): string => (string) $member->translation($default)?->name);

        return $group->members()->where('is_active', true)->with('translations')->get()
            ->map(function (OrgMember $member) use ($pick, $others, $otherGroups, $default, $locale): array {
                $text = $pick($member);
                $assignments = $others->get((string) $member->translation($default)?->name, collect())
                    ->map(fn (OrgMember $other): string => $otherGroups->firstWhere('id', $other->group_id)->nameFor($locale).' - '.($pick($other)?->role ?? ''))
                    ->all();

                return [
                    'name' => (string) $text?->name,
                    'role' => (string) $text?->role,
                    'photo' => $member->photo_url,
                    'assignments' => $assignments,
                ];
            })->all();
    }
}
