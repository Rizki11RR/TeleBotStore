<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(private readonly ActivityLogService $logService) {}

    public function index(): View
    {
        $categories = Category::withTrashed()->withCount('products')->ordered()->paginate(15);
        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:100|unique:categories,name',
            'icon'       => 'nullable|string|max:50',
            'description'=> 'nullable|string',
            'sort_order' => 'integer|min:0',
            'is_active'  => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $category = Category::create($validated);

        $this->logService->log('category.create', "Membuat kategori: {$category->name}", $category);

        return redirect()->route('admin.categories.index')
            ->with('success', "Kategori '{$category->name}' berhasil dibuat.");
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => "required|string|max:100|unique:categories,name,{$category->id}",
            'icon'        => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'sort_order'  => 'integer|min:0',
            'is_active'   => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $category->update($validated);

        $this->logService->log('category.update', "Memperbarui kategori: {$category->name}", $category);

        return redirect()->route('admin.categories.index')
            ->with('success', "Kategori '{$category->name}' berhasil diperbarui.");
    }

    public function destroy(Category $category): RedirectResponse
    {
        $name = $category->name;
        $category->delete();

        $this->logService->log('category.delete', "Menghapus kategori: {$name}", $category);

        return redirect()->route('admin.categories.index')
            ->with('success', "Kategori '{$name}' berhasil dihapus.");
    }
}
