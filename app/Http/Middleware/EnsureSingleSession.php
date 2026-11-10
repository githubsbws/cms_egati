<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * บังคับให้ login ได้ทีละเครื่อง (single session) — ถ้า user คนเดียวกัน
 * ไป login จากเครื่องอื่นอีกครั้ง เครื่องเก่าจะถูก auto logout ทันทีที่มี
 * request ครั้งถัดไป (ตรวจผ่าน users._token เทียบกับ token ที่เก็บไว้ใน
 * session ตอน login — ไม่ตรงกัน = ถูกแทนที่โดยการ login ใหม่จากที่อื่น)
 *
 * หมายเหตุ: วิธีนี้ไม่ป้องกันการขโมย session โดยตรง (ถ้า session ID ถูก
 * ขโมยไปใช้ ผู้ขโมยจะมี token เดียวกับเจ้าของอยู่ดี เพราะไม่ได้ login ใหม่)
 * แต่ป้องกันกรณี username/password เดียวกันถูกใช้ login พร้อมกันหลายเครื่อง
 */
class EnsureSingleSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {

            $user = Auth::user();
            $sessionToken = $request->session()->get('session_token');

            if ($sessionToken !== $user->_token) {

                Auth::logout();

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->with('idle_timeout_message', __('auth.session_replaced'));
            }
        }

        return $next($request);
    }
}
