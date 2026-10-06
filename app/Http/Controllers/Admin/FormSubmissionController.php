<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormSubmission;
use Illuminate\Http\Request;

class FormSubmissionController extends Controller
{
    public function index(Request $request)
    {
        $forms = FormSubmission::select('page_section_id', 'form_name')->distinct()->orderBy('form_name')->get();
        $submissions = $this->query($request)->latest()->paginate(25)->withQueryString();

        return view('admin.cms.submissions.index', compact('submissions', 'forms'));
    }

    public function show(FormSubmission $submission)
    {
        $submission->load('page', 'country');
        if (! $submission->read_at) {
            $submission->update(['read_at' => now()]);
        }

        return view('admin.cms.submissions.show', compact('submission'));
    }

    public function destroy(FormSubmission $submission)
    {
        $submission->delete();

        return redirect()->route('admin.submissions.index')
            ->with('alert_type', 'success')->with('alert_message', 'Submission deleted.');
    }

    public function export(Request $request)
    {
        $rows = $this->query($request)->latest()->get();
        $labels = $rows->flatMap(fn ($s) => collect($s->data)->pluck('label'))->unique()->values();

        return response()->streamDownload(function () use ($rows, $labels) {
            $out = fopen('php://output', 'w');
            fputcsv($out, array_merge(['Date', 'Form', 'Country', 'Page'], $labels->all(), ['IP']));
            foreach ($rows as $s) {
                $vals = collect($s->data)->pluck('value', 'label');
                fputcsv($out, array_merge(
                    [$s->created_at->toDateTimeString(), $s->form_name, optional($s->country)->name, optional($s->page)->title],
                    $labels->map(fn ($l) => $vals[$l] ?? '')->all(),
                    [$s->ip]
                ));
            }
            fclose($out);
        }, 'form-submissions-' . now()->format('Ymd-His') . '.csv', ['Content-Type' => 'text/csv']);
    }

    protected function query(Request $request)
    {
        return FormSubmission::with('country', 'page')
            ->when($request->filled('form'), fn ($q) => $q->where('page_section_id', $request->query('form')))
            ->when($request->filled('q'), fn ($q) => $q->where('data', 'like', '%' . $request->query('q') . '%'));
    }
}
