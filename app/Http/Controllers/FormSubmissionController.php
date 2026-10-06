<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\FormSubmission;
use App\Models\PageSection;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FormSubmissionController extends Controller
{
    /** Public POST for any `form` page section. Fields are validated against the section's own definition. */
    public function store(Request $request, Country $cmsCountry, PageSection $section)
    {
        abort_unless($section->type === 'form' && $section->is_visible, 404);
        $page = $section->page;
        abort_unless($page && $page->country_id === $cmsCountry->id && $page->status === 'published', 404);

        // Honeypot: bots fill it, humans never see it. Pretend success.
        if ($request->filled('website')) {
            return $this->done($section);
        }

        $fields = array_values($section->content['fields'] ?? []);
        $rules = [];
        $names = [];
        foreach ($fields as $i => $f) {
            $key = "f{$i}";
            $names[$key] = $f['label'] ?? 'Field ' . ($i + 1);
            $required = ($f['required'] ?? '') === '1';
            $rule = [$required ? 'required' : 'nullable'];
            $rules[$key] = array_merge($rule, match ($f['type'] ?? 'text') {
                'email' => ['email', 'max:255'],
                'number' => ['numeric'],
                'date' => ['date'],
                'textarea' => ['string', 'max:5000'],
                'checkbox' => ['boolean'],
                'select' => [Rule::in($this->options($f))],
                default => ['string', 'max:500'],
            });
        }

        $valid = $request->validate($rules, [], $names);

        $data = [];
        foreach ($fields as $i => $f) {
            $value = $valid["f{$i}"] ?? null;
            if (($f['type'] ?? '') === 'checkbox') {
                $value = $value ? 'Yes' : 'No';
            }
            $data[] = ['label' => $names["f{$i}"], 'value' => (string) $value];
        }

        FormSubmission::create([
            'page_section_id' => $section->id,
            'page_id' => $page->id,
            'country_id' => $cmsCountry->id,
            'form_name' => ($section->content['name'] ?? '') ?: $page->title,
            'data' => $data,
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);

        return $this->done($section);
    }

    protected function done(PageSection $section)
    {
        $msg = ($section->content['success_message'] ?? '') ?: 'Thank you! Your response has been received.';

        return redirect(url()->previous() . '#form-' . $section->id)->with("form_success_{$section->id}", $msg);
    }

    protected function options(array $f): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\r?\n/', (string) ($f['options'] ?? '')))));
    }
}
