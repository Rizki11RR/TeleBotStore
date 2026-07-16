<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private readonly ActivityLogService $logService) {}

    public function index(): View
    {
        $products = Product::with('category')->withCount('variants')->latest()->paginate(15);
        $categories = Category::active()->ordered()->get();
        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::active()->ordered()->get();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'type'        => 'required|in:ebook,account',
            'name'        => 'required|string|max:150|unique:products,name',
            'description' => 'nullable|string',
            'sort_order'  => 'integer|min:0',
            'is_active'   => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $product = Product::create($validated);

        $this->logService->log('product.create', "Membuat produk: {$product->name}", $product);

        return redirect()->route('admin.products.index')
            ->with('success', "Produk '{$product->name}' berhasil dibuat.");
    }

    public function show(Product $product): View
    {
        $product->load(['category', 'variants.digitalFile', 'variants.accounts']);
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        $categories = Category::active()->ordered()->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'type'        => 'required|in:ebook,account',
            'name'        => "required|string|max:150|unique:products,name,{$product->id}",
            'description' => 'nullable|string',
            'sort_order'  => 'integer|min:0',
            'is_active'   => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $product->update($validated);

        $this->logService->log('product.update', "Memperbarui produk: {$product->name}", $product);

        return redirect()->route('admin.products.index')
            ->with('success', "Produk '{$product->name}' berhasil diperbarui.");
    }

    public function destroy(Product $product): RedirectResponse
    {
        $name = $product->name;
        $product->delete();

        $this->logService->log('product.delete', "Menghapus produk: {$name}", $product);

        return redirect()->route('admin.products.index')
            ->with('success', "Produk '{$name}' berhasil dihapus.");
    }

    public function reorder(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer|exists:products,id',
        ]);

        $ids = $request->input('ids');
        foreach ($ids as $index => $id) {
            Product::withTrashed()->where('id', $id)->update(['sort_order' => $index + 1]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Urutan produk berhasil diperbarui.',
        ]);
    }
}
