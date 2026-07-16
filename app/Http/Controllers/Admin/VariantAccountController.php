<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\ProductVariant;
use App\Models\ProductVariantAccount;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VariantAccountController extends Controller
{
    public function __construct(private readonly ActivityLogService $logService) {}

    public function index(ProductVariant $variant): View
    {
        $variant->load(['product.category', 'accounts' => function ($q) {
            $q->orderBy('is_sold')->latest();
        }]);

        return view('admin.variants.accounts', compact('variant'));
    }

    public function store(Request $request, ProductVariant $variant): RedirectResponse
    {
        $request->validate([
            'accounts_data' => 'required|string',
        ]);

        $lines = explode("\n", str_replace("\r", "", $request->input('accounts_data')));
        $added = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            // Split by : or | or space
            $parts = preg_split('/[:|]/', $line, 2);
            if (count($parts) === 2) {
                $username = trim($parts[0]);
                $password = trim($parts[1]);

                ProductVariantAccount::create([
                    'product_variant_id' => $variant->id,
                    'username_email'     => $username,
                    'password'           => $password,
                    'is_sold'            => false,
                ]);
                $added++;
            }
        }

        // Sinkronisasi stok varian
        $variant->update([
            'stock' => $variant->accounts()->where('is_sold', false)->count(),
        ]);

        $this->logService->log(
            'variant.account_restock',
            "Menambahkan {$added} stok akun untuk varian: {$variant->name} (Produk: {$variant->product->name})",
            $variant
        );

        return back()->with('success', "Berhasil menambahkan {$added} data kredensial akun.");
    }

    public function destroy(ProductVariant $variant, ProductVariantAccount $account): RedirectResponse
    {
        if ($account->product_variant_id !== $variant->id) {
            abort(403);
        }

        $username = $account->username_email;
        $account->delete();

        // Sinkronisasi stok varian
        $variant->update([
            'stock' => $variant->accounts()->where('is_sold', false)->count(),
        ]);

        $this->logService->log(
            'variant.account_delete',
            "Menghapus kredensial akun {$username} dari varian: {$variant->name}",
            $variant
        );

        return back()->with('success', "Data kredensial akun '{$username}' berhasil dihapus.");
    }
}
