<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Remember the back office language in the session. The route only accepts th or en.
     */
    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        $request->session()->put('locale', $locale);

        return redirect()->back(fallback: '/admin');
    }
}
