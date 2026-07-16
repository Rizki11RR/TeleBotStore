<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DeliveryType;
use App\Http\Controllers\Controller;
use App\Models\DigitalFile;
use App\Models\ProductVariant;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DigitalFileController extends Controller
{
    public function __construct(private readonly ActivityLogService $logService) {}

    public function index(): View
    {
        $digitalFiles = DigitalFile::with('productVariant.product')->latest()->paginate(20);
        return view('admin.digital-files.index', compact('digitalFiles'));
    }

    public function create(): View
    {
        $variants = ProductVariant::with('product')->active()->get();
        $deliveryTypes = DeliveryType::cases();
        return view('admin.digital-files.create', compact('variants', 'deliveryTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'delivery_type'      => 'required|in:file,text,manual',
            'content'            => 'required_if:delivery_type,text|nullable|string',
            'file'               => 'required_if:delivery_type,file|nullable|file|max:51200',
            'notes'              => 'required_if:delivery_type,manual|nullable|string',
        ]);

        if ($request->hasFile('file')) {
            $validated['file_path'] = $request->file('file')->store('digital-files', 'private');
            $validated['file_name'] = $request->file('file')->getClientOriginalName();
        }

        unset($validated['file']);
        $digitalFile = DigitalFile::create($validated);

        $this->logService->log('digital_file.create', "Membuat digital file untuk varian ID: {$validated['product_variant_id']}", $digitalFile);

        return redirect()->route('admin.digital-files.index')
            ->with('success', 'Digital file berhasil dibuat.');
    }

    public function show(DigitalFile $digitalFile): View
    {
        $digitalFile->load('productVariant.product');
        return view('admin.digital-files.show', compact('digitalFile'));
    }

    public function edit(DigitalFile $digitalFile): View
    {
        $variants      = ProductVariant::with('product')->active()->get();
        $deliveryTypes = DeliveryType::cases();
        return view('admin.digital-files.edit', compact('digitalFile', 'variants', 'deliveryTypes'));
    }

    public function update(Request $request, DigitalFile $digitalFile): RedirectResponse
    {
        $validated = $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'delivery_type'      => 'required|in:file,text,manual',
            'content'            => 'required_if:delivery_type,text|nullable|string',
            'file'               => 'nullable|file|max:51200',
            'notes'              => 'required_if:delivery_type,manual|nullable|string',
        ]);

        if ($request->hasFile('file')) {
            $validated['file_path'] = $request->file('file')->store('digital-files', 'private');
            $validated['file_name'] = $request->file('file')->getClientOriginalName();
        }

        unset($validated['file']);
        $digitalFile->update($validated);

        $this->logService->log('digital_file.update', "Memperbarui digital file ID: {$digitalFile->id}", $digitalFile);

        return redirect()->route('admin.digital-files.index')
            ->with('success', 'Digital file berhasil diperbarui.');
    }

    public function destroy(DigitalFile $digitalFile): RedirectResponse
    {
        $digitalFile->delete();
        $this->logService->log('digital_file.delete', "Menghapus digital file ID: {$digitalFile->id}");

        return redirect()->route('admin.digital-files.index')
            ->with('success', 'Digital file berhasil dihapus.');
    }
}
