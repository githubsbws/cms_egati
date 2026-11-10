<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Auto logout ผู้ใช้ที่ login แล้วแต่ไม่มีกิจกรรม (idle) เกินเวลาที่กำหนด
 *
 * คงพฤติกรรมเดิมจากระบบเก่า (เช็ค session 'last_activity' เทียบกับ time())
 * แต่แยกเวลา timeout ระหว่าง User/Admin ตาม path และใช้ invalidate() แทน
 * flush() เพื่อไม่ทำลาย CSRF token ของ tab อื่นที่เปิดอยู่
 */
class CheckIdleTimeout
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {

            $timeoutMinutes = $request->is('admin*')
                ? config('idle.admin_minutes')
                : config('idle.user_minutes');

            $lastActivity = $request->session()->get('last_activity');

            if (! is_null($lastActivity) && (time() - $lastActivity) > $timeoutMinutes * 60) {

                Auth::logout();

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->with('idle_timeout_message', 'ไม่มีการใช้งานนานเกินกำหนด ระบบออกจากระบบให้อัตโนมัติ');
            }

            $request->session()->put('last_activity', time());
        }

        return $next($request);
    }
}
