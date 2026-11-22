<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\UploadHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Image upload for the rich text editor (TinyMCE). Returns {"location": "/storage/..."}.
 * The "feature" decides the folder (news/2026/11/...), so each module keeps its own images.
 */
class EditorImageController extends Controller
{
    /**
     * Features allowed to upload through the editor. Add a name here when a new module uses TinyMCE.
     */
    private const FEATURES = ['news'];

    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'file' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'feature' => ['required', 'in:'.implode(',', self::FEATURES)],
        ]);

        $path = UploadHelper::store($data['file'], $data['feature']);

        return response()->json(['location' => '/storage/'.$path]);
    }
}
