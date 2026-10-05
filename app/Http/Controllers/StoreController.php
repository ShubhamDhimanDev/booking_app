<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesStoreCountry;
use App\Models\Country;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    use ResolvesStoreCountry;

    /** `/{country}/store` */
    public function index(Request $request, Country $cmsCountry)
    {
        $country = $this->storeCountry($request, $cmsCountry);

        $categories = ProductCategory::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
        $category = $request->filled('category') ? $categories->firstWhere('slug', $request->category) : null;

        $query = Product::soldIn($country)
            ->with(['prices' => fn ($q) => $q->where('country_id', $country->id)])
            ->join('product_prices as pp', function ($join) use ($country) {
                $join->on('pp.product_id', '=', 'products.id')->where('pp.country_id', '=', $country->id);
            })
            ->select('products.*')
            ->when($category, function ($q) use ($category, $categories) {
                $ids = $categories->where('parent_id', $category->id)->pluck('id')->push($category->id);
                $q->whereIn('products.category_id', $ids);
            })
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w
                ->where('products.name', 'like', '%' . $request->q . '%')
                ->orWhere('products.short_description', 'like', '%' . $request->q . '%')));

        $sort = $request->input('sort');
        match ($sort) {
            'price_asc' => $query->orderBy('pp.sale_price'),
            'price_desc' => $query->orderByDesc('pp.sale_price'),
            default => $query->orderByDesc('products.is_featured')->orderByDesc('products.id'),
        };

        return view('store.index', [
            'country' => $country,
            'products' => $query->paginate(12)->withQueryString(),
            'categories' => $categories,
            'category' => $category,
            'sort' => $sort,
        ]);
    }

    /** `/{country}/store/p/{slug}` */
    public function show(Request $request, Country $cmsCountry, string $slug)
    {
        $country = $this->storeCountry($request, $cmsCountry);

        $product = Product::soldIn($country)
            ->with(['category', 'prices' => fn ($q) => $q->where('country_id', $country->id)])
            ->where('slug', $slug)
            ->firstOrFail();

        $hasFreeSession = $product->grants_free_session
            && ($product->free_session_event_id || $country->free_session_event_id);

        return view('store.show', [
            'country' => $country,
            'product' => $product,
            'price' => $product->priceFor($country),
            'hasFreeSession' => (bool) $hasFreeSession,
        ]);
    }
}
