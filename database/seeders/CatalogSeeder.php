<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        // --- Vendor owner ---
        $vendorUser = User::firstOrCreate(
            ['email' => 'vendor@velvoria.test'],
            ['name' => 'Velvoria Atelier', 'password' => Hash::make('password')],
        );

        $vendor = Vendor::firstOrCreate(
            ['slug' => 'velvoria-atelier'],
            [
                'user_id'     => $vendorUser->id,
                'store_name'  => 'Velvoria Atelier',
                'description' => 'Curated luxury & lifestyle goods.',
                'phone'       => '+62811000000',
                'email'       => 'atelier@velvoria.test',
                'city'        => 'Jakarta',
                'province'    => 'DKI Jakarta',
                'postal_code' => '10110',
                'status'      => 'approved',
            ],
        );

        // --- Categories ---
        $categories = [];
        foreach (['Bags', 'Watches', 'Apparel', 'Footwear', 'Accessories'] as $i => $name) {
            $categories[$name] = Category::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'is_active' => true, 'sort_order' => $i],
            );
        }

        // --- Brands ---
        $brands = [];
        foreach (['Aurelia', 'Noir & Co', 'Maison Vela', 'Lumen'] as $name) {
            $brands[$name] = Brand::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'is_active' => true],
            );
        }

        // --- Products ---
        $products = [
            ['name' => 'Aurelia Leather Tote', 'cat' => 'Bags', 'brand' => 'Aurelia', 'price' => 2500000, 'stock' => 25, 'featured' => true],
            ['name' => 'Noir Minimalist Watch', 'cat' => 'Watches', 'brand' => 'Noir & Co', 'price' => 4200000, 'stock' => 15, 'featured' => true],
            ['name' => 'Maison Vela Silk Scarf', 'cat' => 'Accessories', 'brand' => 'Maison Vela', 'price' => 850000, 'stock' => 60, 'featured' => true],
            ['name' => 'Lumen Cashmere Coat', 'cat' => 'Apparel', 'brand' => 'Lumen', 'price' => 5800000, 'stock' => 8, 'featured' => true],
            ['name' => 'Aurelia Suede Loafers', 'cat' => 'Footwear', 'brand' => 'Aurelia', 'price' => 1900000, 'stock' => 30, 'featured' => false],
            ['name' => 'Noir Crossbody Bag', 'cat' => 'Bags', 'brand' => 'Noir & Co', 'price' => 3100000, 'stock' => 18, 'featured' => false],
            ['name' => 'Lumen Wool Sweater', 'cat' => 'Apparel', 'brand' => 'Lumen', 'price' => 1450000, 'stock' => 40, 'featured' => false],
            ['name' => 'Maison Vela Sunglasses', 'cat' => 'Accessories', 'brand' => 'Maison Vela', 'price' => 1200000, 'stock' => 50, 'featured' => true],
        ];

        foreach ($products as $i => $p) {
            $product = Product::firstOrCreate(
                ['slug' => Str::slug($p['name'])],
                [
                    'vendor_id'         => $vendor->id,
                    'category_id'       => $categories[$p['cat']]->id,
                    'brand_id'          => $brands[$p['brand']]->id,
                    'name'              => $p['name'],
                    'short_description' => $p['name'] . ' — crafted from premium materials.',
                    'description'       => 'A signature piece from the Velvoria collection. ' . $p['name'] . ' blends timeless design with modern craftsmanship.',
                    'price'             => $p['price'],
                    'compare_price'     => (int) round($p['price'] * 1.25),
                    'sku'               => 'VLV-' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                    'stock'             => $p['stock'],
                    'track_stock'       => true,
                    'status'            => 'active',
                    'is_featured'       => $p['featured'],
                    'rating'            => 4.5,
                    'total_reviews'     => 12,
                    'total_sold'        => 30,
                    'tags'              => [$p['cat'], $p['brand'], 'luxury'],
                ],
            );

            if ($product->images()->count() === 0) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_url'  => 'https://picsum.photos/seed/velvoria' . ($i + 1) . '/800/800',
                    'alt_text'   => $p['name'],
                    'is_primary' => true,
                    'sort_order' => 0,
                ]);
            }
        }
    }
}
