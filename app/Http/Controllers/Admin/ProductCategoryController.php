<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin|owner');
    }

    public function index()
    {
        $categories = ProductCategory::withCount('products')->with('parent')->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.store.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.store.categories.form', [
            'category' => new ProductCategory(['is_active' => true]),
            'parents' => ProductCategory::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        ProductCategory::create($this->validated($request));

        return redirect()->route('admin.product-categories.index')->with('success', 'Category created.');
    }

    public function edit(ProductCategory $productCategory)
    {
        return view('admin.store.categories.form', [
            'category' => $productCategory,
            'parents' => ProductCategory::where('id', '!=', $productCategory->id)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, ProductCategory $productCategory)
    {
        $productCategory->update($this->validated($request, $productCategory));

        return redirect()->route('admin.product-categories.index')->with('success', 'Category updated.');
    }

    public function destroy(ProductCategory $productCategory)
    {
        $productCategory->delete(); // products become uncategorised, child categories become top level

        return redirect()->route('admin.product-categories.index')->with('success', 'Category deleted.');
    }

    protected function validated(Request $request, ?ProductCategory $category = null): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'slug' => ['nullable', 'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/', 'max:140', Rule::unique('product_categories', 'slug')->ignore($category?->id)],
            'parent_id' => ['nullable', 'exists:product_categories,id', Rule::notIn([$category?->id])],
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data['slug'] = $data['slug'] ?: $this->uniqueSlug($data['name'], $category);
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    protected function uniqueSlug(string $name, ?ProductCategory $ignore): string
    {
        $base = Str::slug($name) ?: 'category';
        $slug = $base;
        for ($i = 2; ProductCategory::where('slug', $slug)->where('id', '!=', $ignore?->id)->exists(); $i++) {
            $slug = $base . '-' . $i;
        }

        return $slug;
    }
}
