<?php

namespace App\Http\Middleware;

use App\Helpers\PermissionHelper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Block a route unless the signed-in user can see the given admin menu.
 * Usage: ->middleware('menu.permission:10') where 10 is admin_menus.id.
 * Hiding a link with @canmenu is not enough, the URL itself must be checked too.
 */
class EnsureMenuPermission
{
    public function handle(Request $request, Closure $next, string $menuId): Response
    {
        abort_unless(PermissionHelper::canMenu($menuId), 403);

        return $next($request);
    }
}
