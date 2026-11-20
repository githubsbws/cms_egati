<?php

namespace App\Helpers;

use App\Models\Permission;
use Illuminate\Support\Facades\Auth;

/**
 * เช็คว่า user ที่ login อยู่เห็นเมนู admin รายการนั้นได้ไหม
 *
 * กฎ:
 *   1. ไม่ได้ login → false เสมอ
 *   2. superuser (users.superuser == 1) → true เสมอ เห็นทุกเมนู
 *   3. ไม่ใช่ superuser → เช็คว่า admin_menu_id นั้นถูกเปิดสิทธิ์ให้
 *      group_id ของ user ไว้หรือไม่ (ตาราง permission, active = 1)
 *
 * ใช้ผ่าน Blade directive @canmenu($menuId) ... @endcanmenu
 * หรือเรียกตรงด้วย PermissionHelper::canMenu($menuId)
 */
class PermissionHelper
{
    /**
     * cache รายการ admin_menu_id ที่ user คนปัจจุบันมีสิทธิ์เห็น
     * (query ครั้งเดียวต่อ request แม้เรียกเช็คหลายเมนูในหน้าเดียว)
     *
     * @var array<int, int>|null
     */
    protected static ?array $allowedMenuIds = null;

    public static function canMenu(int|string $menuId): bool
    {
        if (! Auth::check()) {
            return false;
        }

        $user = Auth::user();

        if ((int) $user->superuser === 1) {
            return true;
        }

        return in_array((int) $menuId, self::allowedMenuIds($user->group_id), true);
    }

    /**
     * ดึงรายการ admin_menu_id ที่ group_id นี้มีสิทธิ์เห็น (cache ไว้ใน request เดียว)
     *
     * @return array<int, int>
     */
    protected static function allowedMenuIds(?int $groupId): array
    {
        if (is_null($groupId)) {
            return [];
        }

        if (is_null(self::$allowedMenuIds)) {
            self::$allowedMenuIds = Permission::where('group_id', $groupId)
                ->where('active', 'y')
                ->pluck('admin_menu_id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        return self::$allowedMenuIds;
    }
}
