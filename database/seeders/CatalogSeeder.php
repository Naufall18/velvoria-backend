<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Wishlist;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    /** Foto produk mewah nyata (Unsplash) per kategori. */
    private function imagesFor(string $cat): array
    {
        $u = fn (string $id) => "https://images.unsplash.com/photo-$id?auto=format&fit=crop&w=900&q=80";

        return match ($cat) {
            'Bags'        => [$u('1584917865442-de89df76afd3'), $u('1548036328-c9fa89d128fa'), $u('1566150905458-1bf1fc15a7a0')],
            'Watches'     => [$u('1523275335684-37898b6baf30'), $u('1524805444758-089113d48a6d'), $u('1547996160-81dfa63595aa')],
            'Apparel'     => [$u('1539109136881-3be0616acf4b'), $u('1591047139829-d91aecb6caea'), $u('1434389677669-e08b4cac3105')],
            'Footwear'    => [$u('1543163521-1bf539c55dd2'), $u('1560769629-975ec94e6a86'), $u('1549298916-b41d501d3772')],
            'Accessories' => [$u('1601924994987-69e26d50dc26'), $u('1511499767150-a48a237f0083'), $u('1517254797898-ee1bd9b0c5f1')],
            'Jewelry'     => [$u('1515562141207-7a88fb7ce338'), $u('1605100804763-247f67b3557e'), $u('1599643478518-a784e5dc4c8f')],
            default       => [$u('1441986300917-64674bd600d8')],
        };
    }

    public function run(): void
    {
        // ── Vendor ──
        $vendorUser = User::firstOrCreate(
            ['email' => 'vendor@velvoria.test'],
            ['name' => 'Velvoria Atelier', 'password' => Hash::make('password')],
        );
        $vendor = Vendor::firstOrCreate(
            ['slug' => 'velvoria-atelier'],
            [
                'user_id' => $vendorUser->id, 'store_name' => 'Velvoria Atelier',
                'description' => 'Kurator barang mewah & lifestyle pilihan.',
                'phone' => '+62811000000', 'email' => 'atelier@velvoria.test',
                'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'postal_code' => '10110',
                'status' => 'approved',
            ],
        );

        // ── Kategori ──
        $categories = [];
        foreach (['Bags', 'Watches', 'Apparel', 'Footwear', 'Accessories', 'Jewelry'] as $i => $name) {
            $categories[$name] = Category::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'is_active' => true, 'sort_order' => $i],
            );
        }

        // ── Brand ──
        $brands = [];
        foreach (['Aurelia', 'Noir & Co', 'Maison Vela', 'Lumen', 'Velour', 'Étoile'] as $name) {
            $brands[$name] = Brand::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'is_active' => true],
            );
        }

        // ── Produk (nama, kategori, brand, harga Rupiah, stok, unggulan) ──
        $products = [
            ['Aurelia Leather Tote', 'Bags', 'Aurelia', 2500000, 25, true],
            ['Noir Crossbody Bag', 'Bags', 'Noir & Co', 3100000, 18, true],
            ['Velour Quilted Shoulder Bag', 'Bags', 'Velour', 4200000, 12, false],
            ['Maison Vela Mini Top Handle', 'Bags', 'Maison Vela', 3650000, 20, true],
            ['Étoile Weekender Duffle', 'Bags', 'Étoile', 5400000, 8, false],

            ['Noir Minimalist Watch', 'Watches', 'Noir & Co', 4200000, 15, true],
            ['Lumen Automatic Chronograph', 'Watches', 'Lumen', 8900000, 6, true],
            ['Aurelia Rose Gold Dress Watch', 'Watches', 'Aurelia', 6300000, 10, false],
            ['Velour Skeleton Tourbillon', 'Watches', 'Velour', 15500000, 4, true],
            ['Étoile Diver Professional', 'Watches', 'Étoile', 7100000, 9, false],

            ['Lumen Cashmere Coat', 'Apparel', 'Lumen', 5800000, 8, true],
            ['Lumen Wool Sweater', 'Apparel', 'Lumen', 1450000, 40, false],
            ['Maison Vela Silk Blouse', 'Apparel', 'Maison Vela', 1850000, 30, false],
            ['Velour Tailored Blazer', 'Apparel', 'Velour', 3200000, 16, true],
            ['Aurelia Trench Coat', 'Apparel', 'Aurelia', 4600000, 11, false],

            ['Aurelia Suede Loafers', 'Footwear', 'Aurelia', 1900000, 30, false],
            ['Noir Leather Derby', 'Footwear', 'Noir & Co', 2400000, 22, true],
            ['Velour Stiletto Pumps', 'Footwear', 'Velour', 2750000, 18, false],
            ['Étoile Chelsea Boots', 'Footwear', 'Étoile', 3300000, 14, true],
            ['Lumen Running Sneakers', 'Footwear', 'Lumen', 2100000, 35, false],

            ['Maison Vela Silk Scarf', 'Accessories', 'Maison Vela', 850000, 60, true],
            ['Maison Vela Sunglasses', 'Accessories', 'Maison Vela', 1200000, 50, true],
            ['Aurelia Leather Belt', 'Accessories', 'Aurelia', 750000, 70, false],
            ['Noir Wool Beanie', 'Accessories', 'Noir & Co', 480000, 80, false],
            ['Velour Leather Gloves', 'Accessories', 'Velour', 920000, 45, false],

            ['Étoile Diamond Studs', 'Jewelry', 'Étoile', 9800000, 6, true],
            ['Aurelia Gold Necklace', 'Jewelry', 'Aurelia', 6500000, 9, true],
            ['Velour Pearl Bracelet', 'Jewelry', 'Velour', 3200000, 15, false],
            ['Maison Vela Sapphire Ring', 'Jewelry', 'Maison Vela', 7400000, 7, true],
            ['Noir Signet Ring', 'Jewelry', 'Noir & Co', 2900000, 20, false],
        ];

        $created = [];
        foreach ($products as $i => [$name, $cat, $brand, $price, $stock, $featured]) {
            $product = Product::firstOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'vendor_id' => $vendor->id,
                    'category_id' => $categories[$cat]->id,
                    'brand_id' => $brands[$brand]->id,
                    'name' => $name,
                    'short_description' => "$name — dibuat dari material premium pilihan.",
                    'description' => "Sebuah mahakarya dari koleksi Velvoria. $name memadukan desain abadi dengan kerajinan tangan modern, menghadirkan kemewahan yang tahan zaman untuk Anda yang menghargai detail.",
                    'price' => $price,
                    'compare_price' => (int) round($price * 1.25),
                    'sku' => 'VLV-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                    'stock' => $stock,
                    'track_stock' => true,
                    'status' => 'active',
                    'is_featured' => $featured,
                    'rating' => round(4.3 + ($i % 7) * 0.1, 1),
                    'total_reviews' => 5 + ($i % 20),
                    'total_sold' => 10 + ($i * 3) % 90,
                    'tags' => [$cat, $brand, 'luxury'],
                ],
            );

            // Gambar
            if ($product->images()->count() === 0) {
                foreach ($this->imagesFor($cat) as $idx => $url) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_url' => $url,
                        'alt_text' => $name,
                        'is_primary' => $idx === 0,
                        'sort_order' => $idx,
                    ]);
                }
            }

            // Varian (ukuran/warna ringkas)
            if ($product->variants()->count() === 0) {
                foreach (['Standard', 'Edisi Khusus'] as $vi => $vName) {
                    ProductVariant::create([
                        'product_id' => $product->id,
                        'name' => $vName,
                        'sku' => $product->sku.'-'.($vi + 1),
                        'price' => $vi === 0 ? $price : (int) round($price * 1.1),
                        'stock' => max(1, (int) ($stock / 2)),
                        'attributes' => ['edition' => $vName],
                        'is_active' => true,
                    ]);
                }
            }

            $created[] = $product;
        }

        // ── Pembeli contoh + alamat ──
        $buyers = [];
        foreach ([
            ['Aisha Pramudita', 'aisha@velvoria.test', 'Jakarta', 'DKI Jakarta', '10220'],
            ['Bima Santoso', 'bima@velvoria.test', 'Bandung', 'Jawa Barat', '40115'],
            ['Citra Lestari', 'citra@velvoria.test', 'Surabaya', 'Jawa Timur', '60271'],
        ] as [$name, $email, $city, $prov, $pos]) {
            $buyer = User::firstOrCreate(['email' => $email], ['name' => $name, 'password' => Hash::make('password')]);
            Address::firstOrCreate(
                ['user_id' => $buyer->id, 'label' => 'Rumah'],
                [
                    'recipient_name' => $name, 'phone' => '+62812'.random_int(1000000, 9999999),
                    'address' => 'Jl. Merdeka No. '.random_int(1, 99), 'city' => $city,
                    'province' => $prov, 'postal_code' => $pos, 'is_default' => true,
                ],
            );
            $buyers[] = $buyer;
        }

        // ── Pesanan lintas status + ulasan ──
        $statuses = ['pending', 'processing', 'shipped', 'delivered', 'delivered'];
        $orderNo = 1;
        foreach ($buyers as $bi => $buyer) {
            foreach (array_slice($statuses, 0, 3 + $bi) as $si => $status) {
                $picks = [$created[($bi * 5 + $si) % count($created)], $created[($bi * 3 + $si + 7) % count($created)]];
                $subtotal = array_sum(array_map(fn ($p) => (int) $p->price, $picks));
                $shipping = $subtotal >= 2000000 ? 0 : 35000;

                if (Order::where('order_number', 'VLV-'.str_pad((string) $orderNo, 5, '0', STR_PAD_LEFT))->exists()) {
                    $orderNo++;
                    continue;
                }

                $order = Order::create([
                    'order_number' => 'VLV-'.str_pad((string) $orderNo, 5, '0', STR_PAD_LEFT),
                    'user_id' => $buyer->id, 'vendor_id' => $vendor->id, 'status' => $status,
                    'subtotal' => $subtotal, 'shipping_cost' => $shipping, 'tax' => 0,
                    'discount' => 0, 'total' => $subtotal + $shipping,
                    'shipping_name' => $buyer->name, 'shipping_phone' => '+6281200000'.$orderNo,
                    'shipping_address' => 'Jl. Merdeka No. '.random_int(1, 99),
                    'shipping_city' => 'Jakarta', 'shipping_province' => 'DKI Jakarta',
                    'shipping_postal_code' => '10220', 'notes' => null,
                ]);

                foreach ($picks as $p) {
                    OrderItem::create([
                        'order_id' => $order->id, 'product_id' => $p->id, 'product_variant_id' => null,
                        'product_name' => $p->name, 'price' => $p->price, 'quantity' => 1, 'subtotal' => $p->price,
                    ]);

                    if ($status === 'delivered') {
                        Review::firstOrCreate(
                            ['user_id' => $buyer->id, 'product_id' => $p->id, 'order_id' => $order->id],
                            [
                                'rating' => random_int(4, 5),
                                'comment' => 'Kualitas luar biasa dan pengiriman cepat. Sangat puas dengan '.$p->name.'.',
                                'images' => null, 'is_verified' => true,
                            ],
                        );
                    }
                }

                $orderNo++;
            }
        }

        // ── Wishlist (agar tab Wishlist berisi saat testing) ──
        foreach ($buyers as $bi => $buyer) {
            for ($k = 0; $k < 4; $k++) {
                $p = $created[($bi * 4 + $k + 2) % count($created)];
                Wishlist::firstOrCreate(['user_id' => $buyer->id, 'product_id' => $p->id]);
            }
        }
    }
}
