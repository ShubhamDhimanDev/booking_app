<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;

class NavigationController extends Controller
{
    /**
     * Replace a country's header + footer menus with the submitted (already
     * drag-sorted) rows. Array order = sort order.
     */
    public function update(Request $request, Country $country)
    {
        $request->validate([
            'header' => 'nullable|array',
            'footer' => 'nullable|array',
        ]);

        $pageIds = $country->pages()->pluck('id')->all();

        $country->navItems()->delete();

        foreach (['header', 'footer'] as $location) {
            $order = 0;
            foreach ((array) $request->input($location, []) as $row) {
                $label = trim((string) ($row['label'] ?? ''));
                if ($label === '') {
                    continue;
                }
                $pageId = ($row['type'] ?? 'url') === 'page' && in_array((int) ($row['page_id'] ?? 0), $pageIds, true)
                    ? (int) $row['page_id'] : null;
                $url = $pageId ? null : trim((string) ($row['url'] ?? ''));
                if (! $pageId && $url === '') {
                    continue;
                }

                $country->navItems()->create([
                    'location' => $location,
                    'label' => $label,
                    'page_id' => $pageId,
                    'url' => $url,
                    'opens_new_tab' => ! empty($row['opens_new_tab']),
                    'sort_order' => $order++,
                ]);
            }
        }

        return back()->with(['alert_type' => 'success', 'alert_message' => 'Menus saved.']);
    }
}
