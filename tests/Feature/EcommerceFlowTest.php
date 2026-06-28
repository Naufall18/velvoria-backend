<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EcommerceFlowTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(int $stock = 10, float $price = 100000): Product
    {
        $owner = User::factory()->create();

        $vendor = Vendor::create([
            'user_id'    => $owner->id,
            'store_name' => 'Test Vendor',
            'slug'       => 'test-vendor-' . $owner->id,
            'status'     => 'approved',
        ]);

        $category = Category::create([
            'name' => 'Cat ' . $owner->id,
            'slug' => 'cat-' . $owner->id,
        ]);

        $brand = Brand::create([
            'name' => 'Brand ' . $owner->id,
            'slug' => 'brand-' . $owner->id,
        ]);

        return Product::create([
            'vendor_id'   => $vendor->id,
            'category_id' => $category->id,
            'brand_id'    => $brand->id,
            'name'        => 'Test Product',
            'slug'        => 'test-product-' . $owner->id,
            'price'       => $price,
            'stock'       => $stock,
            'track_stock' => true,
            'status'      => 'active',
        ]);
    }

    public function test_user_can_register_and_receive_token(): void
    {
        $response = $this->postJson('/api/register', [
            'name'                  => 'Jane Doe',
            'email'                 => 'jane@example.com',
            'password'              => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['data' => ['user', 'token']]);

        $this->assertDatabaseHas('users', ['email' => 'jane@example.com']);
    }

    public function test_products_endpoint_is_public(): void
    {
        $this->makeProduct();

        $this->getJson('/api/products')
            ->assertOk()
            ->assertJsonPath('status', 'success');
    }

    public function test_authenticated_user_can_create_cod_order_and_stock_decrements(): void
    {
        $product = $this->makeProduct(stock: 10, price: 100000);
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity'   => 3,
        ])->assertCreated();

        $response = $this->postJson('/api/orders', [
            'payment_method'       => 'cod',
            'shipping_name'        => 'Buyer',
            'shipping_phone'       => '0811',
            'shipping_address'     => 'Jl Test',
            'shipping_city'        => 'Jakarta',
            'shipping_province'    => 'DKI',
            'shipping_postal_code' => '10110',
        ]);

        $response->assertCreated()
            ->assertJsonPath('orders.0.status', 'processing')
            ->assertJsonPath('orders.0.payment.payment_method', 'cod');

        $this->assertEquals(7, $product->fresh()->stock);
        $this->assertDatabaseHas('payments', ['payment_method' => 'cod', 'status' => 'pending']);
        $this->assertDatabaseCount('carts', 0);
    }

    public function test_order_requires_payment_method(): void
    {
        $product = $this->makeProduct();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/cart', ['product_id' => $product->id, 'quantity' => 1])->assertCreated();

        $this->postJson('/api/orders', [
            'shipping_name'        => 'Buyer',
            'shipping_phone'       => '0811',
            'shipping_address'     => 'Jl Test',
            'shipping_city'        => 'Jakarta',
            'shipping_province'    => 'DKI',
            'shipping_postal_code' => '10110',
        ])->assertStatus(422);
    }

    public function test_order_fails_when_stock_insufficient(): void
    {
        $product = $this->makeProduct(stock: 1);
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/cart', ['product_id' => $product->id, 'quantity' => 5])->assertCreated();

        $this->postJson('/api/orders', [
            'payment_method'       => 'cod',
            'shipping_name'        => 'Buyer',
            'shipping_phone'       => '0811',
            'shipping_address'     => 'Jl Test',
            'shipping_city'        => 'Jakarta',
            'shipping_province'    => 'DKI',
            'shipping_postal_code' => '10110',
        ])->assertStatus(422);

        // Stock must remain untouched on a failed order.
        $this->assertEquals(1, $product->fresh()->stock);
    }

    public function test_payment_webhook_is_public_but_rejects_invalid_signature(): void
    {
        // No auth token: a 403 (invalid signature) proves the route is public
        // (an auth-protected route would return 401 instead).
        $this->postJson('/api/payments/notification', [
            'order_id'     => 'VLV-x',
            'status_code'  => '200',
            'gross_amount' => '1000',
        ])->assertStatus(403);
    }

    public function test_user_can_add_to_wishlist(): void
    {
        $product = $this->makeProduct();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/wishlist', ['product_id' => $product->id])->assertCreated();
        // Adding the same product again is idempotent (200, no duplicate).
        $this->postJson('/api/wishlist', ['product_id' => $product->id])->assertOk();

        $this->assertDatabaseCount('wishlists', 1);
    }
}
