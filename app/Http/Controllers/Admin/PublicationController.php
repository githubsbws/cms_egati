<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\UploadHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SavePublicationRequest;
use App\Models\Publication;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Credit ratings, financial statements and annual reports (admin menu id 13), one menu page each.
 * The kind comes from the route default "type" (credit | finance | annual).
 */
class PublicationController extends Controller
{
    public function index(string $type): View
    {
        $this->guard($type);

        return view('admin.documents.index', [
            'type' => $type,
            'items' => Publication::query()->ofType($type)->get()->sortBy([['year', 'desc'], fn (Publication $a, Publication $b) => ($b->period === 'annual' ? 4 : (int) $b->period) <=> ($a->period === 'annual' ? 4 : (int) $a->period), ['agency', 'asc']])->values(),
        ]);
    }

    public function create(string $type): View
    {
        $this->guard($type);

        return view('admin.documents.form', ['type' => $type, 'item' => new Publication(['type' => $type, 'year' => (int) now()->format('Y'), 'is_published' => true])]);
    }

    public function store(SavePublicationRequest $request, string $type): RedirectResponse
    {
        $this->save($request, new Publication(['type' => $type]));

        return redirect()->route("admin.documents.{$type}.index")->with('success', __('admin/documents.saved'));
    }

    public function edit(Publication $publication, string $type): View
    {
        abort_unless($publication->type === $type, 404);

        return view('admin.documents.form', ['type' => $type, 'item' => $publication]);
    }

    public function update(SavePublicationRequest $request, Publication $publication, string $type): RedirectResponse
    {
        abort_unless($publication->type === $type, 404);
        $this->save($request, $publication);

        return redirect()->route("admin.documents.{$type}.index")->with('success', __('admin/documents.saved'));
    }

    public function destroy(Publication $publication, string $type): RedirectResponse
    {
        abort_unless($publication->type === $type, 404);
        $publication->delete();

        return redirect()->route("admin.documents.{$type}.index")->with('success', __('admin/documents.deleted'));
    }

    protected function guard(string $type): void
    {
        abort_unless(in_array($type, Publication::TYPES, true), 404);
    }

    protected function save(SavePublicationRequest $request, Publication $publication): void
    {
        $data = $request->validated();
        $type = $publication->type;

        $publication->fill([
            'year' => $data['year'],
            'agency' => $type === 'credit' ? $data['agency'] : null,
            'rating' => $type === 'credit' ? trim(strip_tags($data['rating'])) : null,
            'period' => $type === 'finance' ? $data['period'] : null,
            'external_url' => $data['external_url'] ?? null,
            'is_published' => $request->boolean('is_published'),
            'updated_by' => Auth::id(),
        ]);
        if ($type === 'finance') {
            $publication->external_url_en = $data['external_url_en'] ?? null;
        }

        $this->replaceFile($request, $publication, 'file', 'file_path', 'remove_file');
        if ($type === 'finance') {
            $this->replaceFile($request, $publication, 'file_en', 'file_path_en', 'remove_file_en');
        }
        if ($type === 'annual' && $request->hasFile('cover')) {
            UploadHelper::delete($publication->cover_image);
            $publication->cover_image = UploadHelper::store($request->file('cover'), 'publications');
        }

        $publication->save();
    }

    /**
     * A new upload replaces the old file (deleted from disk); the remove box drops the uploaded file.
     */
    protected function replaceFile(SavePublicationRequest $request, Publication $publication, string $input, string $column, string $removeInput): void
    {
        if ($request->hasFile($input)) {
            UploadHelper::delete($publication->{$column});
            $publication->{$column} = UploadHelper::store($request->file($input), 'publications');
        } elseif ($request->boolean($removeInput)) {
            UploadHelper::delete($publication->{$column});
            $publication->{$column} = null;
        }
    }
}
