<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Post;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class HomeController extends Controller
{
    public function index(string $locale): View
    {
        $latestNews = Post::published()
            ->translatedIn($locale)
            ->with(['translations', 'category.translations'])
            ->orderByDesc('published_at')
            ->limit(6)
            ->get();

        return view('web.home', ['latestNews' => $latestNews]);
    }

    public function redirectToDefault(): RedirectResponse
    {
        $code = Language::active()->where('is_default', 'y')->value('code');

        return redirect('/'.$code);
    }
}
