<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Akun TikTok',          'icon' => '🎵', 'sort_order' => 1],
            ['name' => 'Akun Shopee Affiliate', 'icon' => '🛒', 'sort_order' => 2],
            ['name' => 'Premium Apps',          'icon' => '📱', 'sort_order' => 3],
            ['name' => 'Jasa',                  'icon' => '🔧', 'sort_order' => 4],
            ['name' => 'Ebook / Course',        'icon' => '📚', 'sort_order' => 5],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(
                ['slug' => Str::slug($cat['name'])],
                [
                    'name'       => $cat['name'],
                    'slug'       => Str::slug($cat['name']),
                    'icon'       => $cat['icon'],
                    'sort_order' => $cat['sort_order'],
                    'is_active'  => true,
                ]
            );
        }
    }
}
