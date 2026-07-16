<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedTikTokProducts();
        $this->seedShopeeAffiliateProducts();
        $this->seedPremiumAppsProducts();
        $this->seedJasaProducts();
        $this->seedEbookProducts();
    }

    private function seedTikTokProducts(): void
    {
        $category = Category::where('slug', 'akun-tiktok')->first();
        if (! $category) {
            return;
        }

        // Akun TikTok Indonesia
        $product = Product::firstOrCreate(
            ['slug' => 'akun-tiktok-indonesia'],
            [
                'category_id' => $category->id,
                'name'        => 'Akun TikTok Indonesia',
                'slug'        => 'akun-tiktok-indonesia',
                'description' => "• Akun Fresh\n• Hasil FYP\n• Followers Organik\n• Tidak Suntik\n• Affiliate ON\n• Live ON\n• Showcase ON",
                'sort_order'  => 1,
                'is_active'   => true,
            ]
        );

        $variants = [
            ['name' => '0 Followers',    'price' => 70000,  'stock' => -1],
            ['name' => '600 Followers',  'price' => 110000, 'stock' => -1],
            ['name' => '700 Followers',  'price' => 225000, 'stock' => -1],
            ['name' => '800 Followers',  'price' => 130000, 'stock' => -1],
            ['name' => '900 Followers',  'price' => 145000, 'stock' => -1],
            ['name' => '1000 Followers', 'price' => 150000, 'stock' => -1],
        ];

        foreach ($variants as $variant) {
            ProductVariant::firstOrCreate(
                ['product_id' => $product->id, 'name' => $variant['name']],
                array_merge($variant, ['is_active' => true])
            );
        }

        // Akun TikTok Malaysia
        $productMY = Product::firstOrCreate(
            ['slug' => 'akun-tiktok-malaysia'],
            [
                'category_id' => $category->id,
                'name'        => 'Akun TikTok Malaysia',
                'slug'        => 'akun-tiktok-malaysia',
                'description' => "• Akun Fresh Malaysia\n• Hasil FYP\n• Followers Organik",
                'sort_order'  => 2,
                'is_active'   => true,
            ]
        );

        $variantsMY = [
            ['name' => '1000 Followers', 'price' => 650000, 'stock' => -1],
            ['name' => '1100 Followers', 'price' => 675000, 'stock' => -1],
            ['name' => '1200 Followers', 'price' => 700000, 'stock' => -1],
        ];

        foreach ($variantsMY as $variant) {
            ProductVariant::firstOrCreate(
                ['product_id' => $productMY->id, 'name' => $variant['name']],
                array_merge($variant, ['is_active' => true])
            );
        }
    }

    private function seedShopeeAffiliateProducts(): void
    {
        $category = Category::where('slug', 'akun-shopee-affiliate')->first();
        if (! $category) {
            return;
        }

        $products = [
            ['name' => 'Akun Shopee Affiliate Indonesia', 'slug' => 'akun-shopee-affiliate-indonesia', 'price' => 250000],
            ['name' => 'Akun Shopee Affiliate Singapore',  'slug' => 'akun-shopee-affiliate-singapore',  'price' => 350000],
            ['name' => 'Akun Shopee Affiliate Malaysia',   'slug' => 'akun-shopee-affiliate-malaysia',   'price' => 300000],
        ];

        foreach ($products as $index => $p) {
            $product = Product::firstOrCreate(
                ['slug' => $p['slug']],
                [
                    'category_id' => $category->id,
                    'name'        => $p['name'],
                    'slug'        => $p['slug'],
                    'sort_order'  => $index + 1,
                    'is_active'   => true,
                ]
            );

            ProductVariant::firstOrCreate(
                ['product_id' => $product->id, 'name' => 'Standard'],
                ['price' => $p['price'], 'stock' => -1, 'is_active' => true]
            );
        }
    }

    private function seedPremiumAppsProducts(): void
    {
        $category = Category::where('slug', 'premium-apps')->first();
        if (! $category) {
            return;
        }

        $product = Product::firstOrCreate(
            ['slug' => 'capcut-pro'],
            [
                'category_id' => $category->id,
                'name'        => 'CapCut Pro',
                'slug'        => 'capcut-pro',
                'description' => "• Garansi Incorrect Password\n• Garansi Backfree\n• Limit Login tidak termasuk garansi\n• Maksimal 3 Device",
                'sort_order'  => 1,
                'is_active'   => true,
            ]
        );

        ProductVariant::firstOrCreate(
            ['product_id' => $product->id, 'name' => '35 Hari'],
            ['price' => 55000, 'stock' => -1, 'is_active' => true]
        );
    }

    private function seedJasaProducts(): void
    {
        $category = Category::where('slug', 'jasa')->first();
        if (! $category) {
            return;
        }

        $products = [
            ['name' => 'Jasa NPWP',                     'slug' => 'jasa-npwp',                     'price' => 100000],
            ['name' => 'Jasa Verifikasi Akun Shopee',   'slug' => 'jasa-verifikasi-akun-shopee',   'price' => 50000],
            ['name' => 'Jasa Verifikasi Akun TikTok',   'slug' => 'jasa-verifikasi-akun-tiktok',   'price' => 50000],
        ];

        foreach ($products as $index => $p) {
            $product = Product::firstOrCreate(
                ['slug' => $p['slug']],
                [
                    'category_id' => $category->id,
                    'name'        => $p['name'],
                    'slug'        => $p['slug'],
                    'sort_order'  => $index + 1,
                    'is_active'   => true,
                ]
            );

            ProductVariant::firstOrCreate(
                ['product_id' => $product->id, 'name' => 'Standard'],
                ['price' => $p['price'], 'stock' => -1, 'is_active' => true]
            );
        }
    }

    private function seedEbookProducts(): void
    {
        $category = Category::where('slug', 'ebook-course')->first();
        if (! $category) {
            return;
        }

        $products = [
            [
                'name'        => 'Motion Control Unlimited',
                'slug'        => 'motion-control-unlimited',
                'price'       => 149000,
                'description' => "• Tanpa Langganan\n• Tanpa Email\n• Tanpa Script\n• Tanpa Bot Telegram\n• Tanpa API Key\n• Bisa lewat HP",
            ],
            [
                'name'  => 'Trik Affiliate Shopee Luar Negeri',
                'slug'  => 'trik-affiliate-shopee-luar-negeri',
                'price' => 1499000,
            ],
            [
                'name'  => 'Trik TikTok Luar Negeri',
                'slug'  => 'trik-tiktok-luar-negeri',
                'price' => 1499000,
            ],
            [
                'name'        => 'Trik Bikin CapCut Pro',
                'slug'        => 'trik-bikin-capcut-pro',
                'price'       => 1999000,
                'description' => 'Cocok untuk Reseller',
            ],
        ];

        foreach ($products as $index => $p) {
            $product = Product::firstOrCreate(
                ['slug' => $p['slug']],
                [
                    'category_id' => $category->id,
                    'name'        => $p['name'],
                    'slug'        => $p['slug'],
                    'description' => $p['description'] ?? null,
                    'sort_order'  => $index + 1,
                    'is_active'   => true,
                ]
            );

            ProductVariant::firstOrCreate(
                ['product_id' => $product->id, 'name' => 'Standard'],
                ['price' => $p['price'], 'stock' => -1, 'is_active' => true]
            );
        }
    }
}
