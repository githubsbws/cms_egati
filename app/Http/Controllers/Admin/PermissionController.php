<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminGroup;
use App\Models\AdminMenu;
use App\Models\Permission;
use App\Models\Users;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
     * List admin users: users that belong to an admin group.
     * The data goes straight into the view for Tabulator, no JSON endpoint needed.
     */
    public function adminUser(): View
    {
        $adminUsers = Users::with(['profile', 'adminGroup'])
            ->where('del_status', 0)
            ->whereNotNull('group_id')
            ->orderBy('id')
            ->get()
            ->map(fn (Users $user) => [
                'id' => $user->id,
                'fullname' => trim(($user->profile->firstname ?? '').' '.($user->profile->lastname ?? '')) ?: null,
                'email' => $user->email,
                'group_name' => $user->adminGroup?->group_name,
            ]);

        return view('admin.permission.permission-user', compact('adminUsers'));
    }

    /**
     * Remove admin access from a user without deleting the user:
     * superuser = 0 and group_id = null.
     */
    public function cancelAdminUserPermission(Users $user): RedirectResponse
    {
        $user->update([
            'superuser' => 0,
            'group_id' => null,
        ]);

        return redirect()
            ->route('admin.permission.user')
            ->with('success', __('admin/common.save'));
    }
}
