<?php

namespace Database\Seeders;

use App\Models\OrgChart;
use App\Models\OrgGroup;
use App\Models\OrgMember;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Loads the board, committees, management team and the structure chart from the design draft.
 * Safe to run again: a group that already has people is left alone, and the chart is only set if missing.
 */
class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $data = json_decode(file_get_contents(database_path('seeders/data/organization.json')), true, 512, JSON_THROW_ON_ERROR);

        DB::transaction(function () use ($data): void {
            $groups = [];
            foreach ($data['groups'] as $row) {
                $group = OrgGroup::firstOrCreate(
                    ['code' => $row['code']],
                    ['type' => $row['type'], 'sort_order' => $row['sort'], 'document_path' => $row['doc'], 'is_active' => true],
                );
                $group->translations()->updateOrCreate(['locale' => 'th'], ['name' => $row['th']]);
                $group->translations()->updateOrCreate(['locale' => 'en'], ['name' => $row['en']]);
                $groups[$row['code']] = $group;
            }

            // Groups that already have people are left as they are (so running this again never duplicates)
            $skip = collect($groups)->filter(fn (OrgGroup $group): bool => $group->members()->exists())->keys()->all();

            foreach ($data['members'] as $row) {
                if (in_array($row['group'], $skip, true)) {
                    continue;
                }
                $group = $groups[$row['group']];
                $member = OrgMember::create([
                    'group_id' => $group->id,
                    'photo_path' => $row['photo'],
                    'is_active' => true,
                ]);
                $this->addTranslations($member, $row);
            }

            OrgChart::firstOrCreate(['locale' => 'th'], ['image_path' => $data['chart']]);
        });
    }

    /**
     * A language gets a text row when the source has a name or a position for it
     * (a person can have a position but no name, shown as "name not listed").
     *
     * @param  array<string, mixed>  $row
     */
    protected function addTranslations(OrgMember $member, array $row): void
    {
        foreach (['th', 'en'] as $locale) {
            $name = (string) ($row[$locale]['name'] ?? '');
            $role = $row[$locale]['role'] ?? null;
            if ($name !== '' || ! empty($role)) {
                $member->translations()->create(['locale' => $locale, 'name' => $name, 'role' => $role]);
            }
        }
    }
}
