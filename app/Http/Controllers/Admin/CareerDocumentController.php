<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\UploadHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveCareerDocumentRequest;
use App\Models\CareerJob;
use App\Models\CareerJobDocument;
use App\Models\Language;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Files of a recruitment notice: add, delete and reorder (shown on the position edit page).
 */
class CareerDocumentController extends Controller
{
    public function store(SaveCareerDocumentRequest $request, CareerJob $job): RedirectResponse
    {
        $data = $request->validated();
        $labels = [];
        foreach (Language::active()->pluck('code') as $locale) {
            $label = trim(strip_tags((string) ($data['labels'][$locale] ?? '')));
            if ($label !== '') {
                $labels[$locale] = $label;
            }
        }

        $document = new CareerJobDocument([
            'labels' => $labels,
            'external_url' => $request->hasFile('file') ? null : ($data['external_url'] ?? null),
            'is_notice' => $request->boolean('is_notice'),
            'sort_order' => (int) $job->documents()->max('sort_order') + 1,
        ]);
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $document->file_path = UploadHelper::store($file, 'careers');
            $document->size_bytes = $file->getSize();
        }
        $job->documents()->save($document);

        return $this->back($job, 'saved');
    }

    public function destroy(CareerJobDocument $document): RedirectResponse
    {
        $job = $document->job;
        UploadHelper::delete($document->file_path);
        $document->delete();

        return $this->back($job, 'deleted');
    }

    /**
     * Swap with the neighbour above or below, then keep the order as 1..n.
     */
    public function move(Request $request, CareerJobDocument $document): RedirectResponse
    {
        $direction = $request->validate(['direction' => ['required', 'in:up,down']])['direction'];
        $job = $document->job;
        $ids = $job->documents()->pluck('id')->all();
        $position = array_search($document->id, $ids, true);
        $target = $direction === 'up' ? $position - 1 : $position + 1;

        if ($position !== false && isset($ids[$target])) {
            [$ids[$position], $ids[$target]] = [$ids[$target], $ids[$position]];
            DB::transaction(function () use ($ids) {
                foreach ($ids as $index => $id) {
                    CareerJobDocument::whereKey($id)->update(['sort_order' => $index + 1]);
                }
            });
        }

        return redirect()->to(route('admin.careers.jobs.edit', $job).'#documents');
    }

    protected function back(CareerJob $job, string $message): RedirectResponse
    {
        return redirect()->to(route('admin.careers.jobs.edit', $job).'#documents')->with('success', __("admin/careers.{$message}"));
    }
}
