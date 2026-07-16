<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Models\Product;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VariantController extends Controller
{
    public function __construct(private readonly ActivityLogService $logService) {}

    public function index(): View
    {
        $variants = ProductVariant::with('product.category')->latest()->paginate(20);
        $products = Product::active()->ordered()->get();
        return view('admin.variants.index', compact('variants', 'products'));
    }

    public function create(): View
    {
        $products = Product::with('category')->active()->ordered()->get();
        return view('admin.variants.create', compact('products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'name'       => 'required|string|max:150',
            'price'      => 'required|numeric|min:0',
            'stock'      => 'integer|min:-1',
            'is_active'  => 'boolean',
        ]);

        $variant = ProductVariant::create($validated);
        $this->logService->log('variant.create', "Membuat varian: {$variant->name}", $variant);

        return redirect()->route('admin.variants.index')
            ->with('success', "Varian '{$variant->name}' berhasil dibuat.");
    }

    public function show(ProductVariant $variant): View
    {
        $variant->load(['product.category', 'digitalFile']);
        return view('admin.variants.show', compact('variant'));
    }

    public function edit(ProductVariant $variant): View
    {
        $products = Product::with('category')->active()->ordered()->get();
        return view('admin.variants.edit', compact('variant', 'products'));
    }

    public function update(Request $request, ProductVariant $variant): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'name'       => 'required|string|max:150',
            'price'      => 'required|numeric|min:0',
            'stock'      => 'integer|min:-1',
            'is_active'  => 'boolean',
        ]);

        $variant->update($validated);
        $this->logService->log('variant.update', "Memperbarui varian: {$variant->name}", $variant);

        return redirect()->route('admin.variants.index')
            ->with('success', "Varian '{$variant->name}' berhasil diperbarui.");
    }

    public function destroy(ProductVariant $variant): RedirectResponse
    {
        $name = $variant->name;
        $variant->delete();
        $this->logService->log('variant.delete', "Menghapus varian: {$name}", $variant);

        return redirect()->route('admin.variants.index')
            ->with('success', "Varian '{$name}' berhasil dihapus.");
    }
}
