<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Event;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin|owner');
    }

    public function index(Request $request)
    {
        $products = Product::with(['category', 'prices.country'])
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', '%' . $request->q . '%')
                ->orWhere('sku', 'like', '%' . $request->q . '%')))
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->category_id))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.store.products.index', [
            'products' => $products,
            'categories' => ProductCategory::orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.store.products.form', $this->formData(new Product([
            'is_active' => true, 'track_stock' => true,
        ])));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $images = $this->uploadImages($request, []);

        $product = DB::transaction(function () use ($data, $images) {
            $product = Product::create($data['product'] + ['images' => $images]);
            $this->syncPrices($product, $data['prices']);

            return $product;
        });

        return redirect()->route('admin.products.edit', $product)->with('success', 'Product created.');
    }

    public function edit(Product $product)
    {
        $product->load('prices');

        return view('admin.store.products.form', $this->formData($product));
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validated($request, $product);

        $old = $product->images ?? [];
        $keep = array_values(array_intersect($old, (array) $request->input('keep_images', [])));
        $images = $this->uploadImages($request, $keep);

        DB::transaction(function () use ($product, $data, $images) {
            $product->update($data['product'] + ['images' => $images]);
            $this->syncPrices($product, $data['prices']);
        });

        Product::deleteImageFiles(array_diff($old, $images));

        return redirect()->route('admin.products.edit', $product)->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $images = $product->images ?? [];
        $product->delete(); // past order items keep their own name/price snapshot
        Product::deleteImageFiles($images);

        return redirect()->route('admin.products.index')->with('success', 'Product deleted.');
    }

    protected function formData(Product $product): array
    {
        return [
            'product' => $product,
            'categories' => ProductCategory::orderBy('name')->get(),
            'countries' => Country::orderBy('sort_order')->orderBy('name')->get(),
            'events' => Event::orderBy('title')->get(['id', 'title']),
            'prices' => $product->exists ? $product->prices->keyBy('country_id') : collect(),
        ];
    }

    /** @return array{product: array, prices: array<int, array{currency: string, mrp: float, sale_price: float}>} */
    protected function validated(Request $request, ?Product $product = null): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'slug' => ['nullable', 'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/', 'max:220', Rule::unique('products', 'slug')->ignore($product?->id)],
            'sku' => ['nullable', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($product?->id)],
            'category_id' => 'nullable|exists:product_categories,id',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'stock_qty' => 'nullable|integer|min:0',
            'free_session_event_id' => 'nullable|exists:events,id',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'new_images.*' => 'image|max:10240',
            'prices' => 'nullable|array',
            'prices.*.mrp' => 'nullable|numeric|min:0',
            'prices.*.sale_price' => 'nullable|numeric|min:0',
        ]);

        // A country is "sold" when it has an MRP. The sale price defaults to the MRP and may not exceed it.
        $countries = Country::pluck('currency', 'id');
        $prices = [];
        foreach ((array) ($data['prices'] ?? []) as $countryId => $row) {
            if (! isset($countries[$countryId]) || ($row['mrp'] ?? '') === '') {
                continue;
            }
            $mrp = (float) $row['mrp'];
            $sale = ($row['sale_price'] ?? '') === '' ? $mrp : (float) $row['sale_price'];
            if ($sale > $mrp) {
                throw ValidationException::withMessages(["prices.$countryId.sale_price" => 'Sale price cannot be higher than the MRP.']);
            }
            $prices[$countryId] = ['currency' => $countries[$countryId], 'mrp' => $mrp, 'sale_price' => $sale];
        }

        $productData = collect($data)->only([
            'name', 'sku', 'category_id', 'short_description', 'description', 'free_session_event_id', 'meta_title', 'meta_description',
        ])->all();
        $productData['slug'] = ($data['slug'] ?? null) ?: $this->uniqueSlug($data['name'], $product);
        $productData['sku'] = ($productData['sku'] ?? null) ?: null;
        $productData['stock_qty'] = (int) ($data['stock_qty'] ?? 0);
        $productData['track_stock'] = $request->boolean('track_stock');
        $productData['is_active'] = $request->boolean('is_active');
        $productData['is_featured'] = $request->boolean('is_featured');
        $productData['grants_free_session'] = $request->boolean('grants_free_session');

        return ['product' => $productData, 'prices' => $prices];
    }

    /** Replace the product's country prices with the submitted set. */
    protected function syncPrices(Product $product, array $prices): void
    {
        $product->prices()->whereNotIn('country_id', array_keys($prices))->delete();
        foreach ($prices as $countryId => $row) {
            $product->prices()->updateOrCreate(['country_id' => $countryId], $row);
        }
    }

    protected function uploadImages(Request $request, array $keep): array
    {
        foreach ((array) $request->file('new_images', []) as $file) {
            $keep[] = Storage::disk('public')->url($file->store('products', 'public'));
        }

        return $keep;
    }

    protected function uniqueSlug(string $name, ?Product $ignore): string
    {
        $base = Str::slug($name) ?: 'product';
        $slug = $base;
        for ($i = 2; Product::where('slug', $slug)->where('id', '!=', $ignore?->id)->exists(); $i++) {
            $slug = $base . '-' . $i;
        }

        return $slug;
    }
}
