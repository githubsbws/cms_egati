<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminGroup;
use App\Models\AdminMenu;
use App\Models\Permission;
use App\Models\Profiles;
use App\Models\Users;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class PermissionController extends Controller
{
    /**
     * List active admin groups (admin_group).
     */
    public function adminGroup(): View
    {
        $adminGroups = AdminGroup::where('active', 'y')->orderBy('id')->get();

        return view('admin.permission.permission-group', compact('adminGroups'));
    }

    /**
     * Store a new admin group.
     */
    public function storeAdminGroup(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'group_name' => ['required', 'string', 'max:255'],
        ]);

        AdminGroup::create([
            'group_name' => $validated['group_name'],
            'active' => 'y',
            'is_superuser' => false,
        ]);

        return redirect()
            ->route('admin.permission.group')
            ->with('success', __('admin/common.save'));
    }

    /**
     * Show the menu permission checkboxes for an admin group.
     * Menus already allowed (permission.active = 'y') are pre-checked.
     */
    public function editAdminGroup(AdminGroup $adminGroup): View
    {
        $adminMenus = AdminMenu::where('active', 'y')->orderBy('sort_order')->get();

        $allowedMenuIds = Permission::where('group_id', $adminGroup->id)
            ->where('active', 'y')
            ->pluck('admin_menu_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return view('admin.permission.permission-group-edit', compact('adminGroup', 'adminMenus', 'allowedMenuIds'));
    }

    /**
     * Save the menu permissions of an admin group from the checked boxes.
     */
    public function updateAdminGroupPermission(Request $request, AdminGroup $adminGroup): RedirectResponse
    {
        $validated = $request->validate([
            'admin_menu_ids' => ['array'],
            'admin_menu_ids.*' => ['integer', 'exists:admin_menus,id'],
        ]);

        $checkedMenuIds = array_map('intval', $validated['admin_menu_ids'] ?? []);
        $existingPermissions = Permission::where('group_id', $adminGroup->id)->get();

        foreach ($existingPermissions as $permission) {
            $isChecked = in_array((int) $permission->admin_menu_id, $checkedMenuIds, true);

            $permission->update(['active' => $isChecked ? 'y' : 'n']);
        }
        $existingMenuIds = $existingPermissions->pluck('admin_menu_id')->map('intval')->all();

        foreach (array_diff($checkedMenuIds, $existingMenuIds) as $menuId) {
            Permission::create([
                'group_id' => $adminGroup->id,
                'admin_menu_id' => $menuId,
                'active' => 'y',
            ]);
        }

        return redirect()
            ->route('admin.permission.group')
            ->with('success', __('admin/common.save'));
    }

    public function updateAdminGroup(Request $request, AdminGroup $adminGroup): RedirectResponse
    {
        $validated = $request->validate([
            'group_name' => ['required', 'string', 'max:255'],
        ]);

        $adminGroup->update(['group_name' => $validated['group_name']]);

        return redirect()
            ->route('admin.permission.group')
            ->with('success', __('admin/common.save'));
    }

    /**
     * Soft delete: set active to n instead of removing the row, so users and
     * permission rows that still point at this group keep a valid reference.
     */
    public function destroyAdminGroup(AdminGroup $adminGroup): RedirectResponse
    {
        $adminGroup->update(['active' => 'n']);

        return redirect()
            ->route('admin.permission.group')
            ->with('success', __('admin/common.save'));
    }

    /**
     * List admin users (users that belong to an admin group), 10 per page.
     * Filters: q = full name (first + last), group = admin_group id.
     */
    public function adminUser(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'group' => ['nullable', 'integer'],
        ]);

        $adminUsers = Users::with(['profile', 'adminGroup'])
            ->where('del_status', 0)
            ->whereNotNull('group_id')
            ->when($filters['q'] ?? null, function ($query, $keyword) {
                $like = '%'.addcslashes(trim($keyword), '%_\\').'%';

                $query->whereHas('profile', fn ($profile) => $profile->whereRaw("(firstname || ' ' || lastname) ILIKE ?", [$like]));
            })
            ->when($filters['group'] ?? null, fn ($query, $groupId) => $query->where('group_id', $groupId))
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        $adminGroups = AdminGroup::where('active', 'y')->orderBy('id')->get();

        return view('admin.permission.permission-user', compact('adminUsers', 'adminGroups'));
    }

    /**
     * Create an admin user: the account, its profile and the permission group in one transaction.
     */
    public function storeAdminUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'group_id' => ['required', 'integer', Rule::exists('admin_group', 'id')->where('active', 'y')],
        ]);

        DB::transaction(function () use ($validated) {
            $user = Users::create([
                'username' => $validated['username'],
                'email' => Str::lower($validated['email']),
                'password' => $validated['password'],
                'group_id' => $validated['group_id'],
                'superuser' => 0,
                'del_status' => 0,
            ]);

            Profiles::create([
                'user_id' => $user->id,
                'firstname' => $validated['firstname'],
                'lastname' => $validated['lastname'],
                'phone' => $validated['phone'] ?? null,
            ]);
        });

        return redirect()
            ->route('admin.permission.user')
            ->with('success', __('admin/common.save'));
    }

    public function editAdminUser(Users $user): View
    {
        $user->load('profile');

        $adminGroups = AdminGroup::where('active', 'y')->orderBy('id')->get();

        return view('admin.permission.permission-user-edit', [
            'user' => $user,
            'profile' => $user->profile,
            'adminGroups' => $adminGroups,
        ]);
    }

    /**
     * Update an admin user. The password only changes when a new one is typed.
     */
    public function updateAdminUser(Request $request, Users $user): RedirectResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::min(12)],
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'group_id' => ['required', 'integer', Rule::exists('admin_group', 'id')->where('active', 'y')],
        ]);

        DB::transaction(function () use ($validated, $user) {
            $account = [
                'username' => $validated['username'],
                'group_id' => $validated['group_id'],
            ];

            if (! empty($validated['password'])) {
                $account['password'] = $validated['password'];
            }

            $user->update($account);

            Profiles::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'firstname' => $validated['firstname'],
                    'lastname' => $validated['lastname'],
                    'phone' => $validated['phone'] ?? null,
                ]
            );
        });

        return redirect()
            ->route('admin.permission.user')
            ->with('success', __('admin/common.save'));
    }

    /**
     * Remove admin access and disable the account: superuser = 0, group_id = null, del_status = 1.
     * Nobody can do this to their own account, that would lock them out.
     */
    public function cancelAdminUserPermission(Users $user): RedirectResponse
    {
        if ($user->is(auth()->user())) {
            return redirect()
                ->route('admin.permission.user')
                ->withErrors(['cancel' => __('admin/permission.cannot_cancel_self')]);
        }

        $user->update([
            'superuser' => 0,
            'group_id' => null,
            'del_status' => 1,
        ]);

        return redirect()
            ->route('admin.permission.user')
            ->with('success', __('admin/common.save'));
    }
}
