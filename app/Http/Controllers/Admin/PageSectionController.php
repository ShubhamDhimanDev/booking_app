<?php

namespace App\Http\Controllers\Admin;

use App\Cms\SectionTypes;
use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PageSectionController extends Controller
{
    public function store(Request $request, Page $page)
    {
        $request->validate(['type' => 'required|in:' . implode(',', SectionTypes::keys())]);

        $page->sections()->create([
            'type' => $request->input('type'),
            'content' => SectionTypes::clean($request->input('type'), []),
            'sort_order' => (int) $page->sections()->max('sort_order') + 1,
            'is_visible' => true,
        ]);

        return redirect()->route('admin.pages.edit', $page)->withFragment('sections')->with(['alert_type' => 'success', 'alert_message' => 'Section added.']);
    }

    public function update(Request $request, PageSection $section)
    {
        $section->update([
            'content' => SectionTypes::clean($section->type, (array) $request->input('content', [])),
            'is_visible' => $request->boolean('is_visible'),
        ]);

        return redirect()->route('admin.pages.edit', $section->page_id)
            ->withFragment('section-' . $section->id)
            ->with(['alert_type' => 'success', 'alert_message' => 'Section saved.', 'open_section' => $section->id]);
    }

    public function destroy(PageSection $section)
    {
        $pageId = $section->page_id;
        $section->delete();

        return redirect()->route('admin.pages.edit', $pageId)->withFragment('sections')->with(['alert_type' => 'success', 'alert_message' => 'Section deleted.']);
    }

    /** Drag-and-drop persistence: body = { order: [sectionId, ...] }. */
    public function reorder(Request $request, Page $page)
    {
        $ids = array_map('intval', (array) $request->input('order', []));
        $owned = $page->sections()->pluck('id')->all();

        foreach (array_values(array_filter($ids, fn ($id) => in_array($id, $owned, true))) as $position => $id) {
            PageSection::where('id', $id)->update(['sort_order' => $position]);
        }

        return response()->json(['ok' => true]);
    }

    /** Image upload for image-type fields. Returns the public URL. */
    public function upload(Request $request)
    {
        $request->validate(['file' => 'required|image|max:10240']);
        $path = $request->file('file')->store('cms', 'public');

        return response()->json(['url' => Storage::disk('public')->url($path)]);
    }
}
