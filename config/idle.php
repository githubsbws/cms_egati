<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Idle timeout (minutes)
    |--------------------------------------------------------------------------
    |
    | Minutes without any request before a signed-in user is signed out by
    | App\Http\Middleware\CheckIdleTimeout. The default follows FR-AUTH-08 (120).
    | A value above SESSION_LIFETIME has no effect, the session expires first.
    |
    */

    'admin_minutes' => (int) env('IDLE_ADMIN_MINUTES', 120),

    'user_minutes' => (int) env('IDLE_USER_MINUTES', 120),

];
