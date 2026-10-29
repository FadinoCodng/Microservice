<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    /**
     * Skenario 1: GET /health harus mengembalikan status ok (200).
     */
    public function test_health_check_returns_ok(): void
    {
        $response = $this->getJson('/health');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
                'service' => 'order-service',
            ]);
    }

    /**
     * Skenario 2: POST /orders gagal validasi saat user_id kosong (422).
     */
    public function test_post_orders_fails_when_user_id_missing(): void
    {
        $response = $this->postJson('/orders', []);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['user_id'],
            ]);
    }

    /**
     * Skenario 3: POST /orders gagal saat keranjang user kosong (409).
     */
    public function test_post_orders_fails_when_cart_is_empty(): void
    {
        Http::fake([
            '*/carts/7' => Http::response([
                'message' => 'Berhasil mengambil keranjang',
                'data' => [
                    'user_id' => 7,
                    'items' => [],
                    'total_quantity' => 0,
                ],
            ], 200),
        ]);

        $response = $this->postJson('/orders', ['user_id' => 7]);

        $response->assertStatus(409)
            ->assertJson([
                'message' => 'Keranjang belanja kosong',
            ]);
    }

    /**
     * Skenario 4: POST /orders gagal saat stok tidak mencukupi (409).
     */
    public function test_post_orders_fails_when_stock_insufficient(): void
    {
        Http::fake([
            '*/carts/7' => Http::response([
                'message' => 'Berhasil mengambil keranjang',
                'data' => [
                    'user_id' => 7,
                    'items' => [
                        ['id' => 1, 'user_id' => 7, 'product_id' => 3, 'quantity' => 11],
                    ],
                    'total_quantity' => 11,
                ],
            ], 200),
            '*/products/3' => Http::response([
                'message' => 'Berhasil mengambil produk',
                'data' => [
                    'id' => 3,
                    'name' => 'Monitor 24 Inch',
                    'price' => '1800000.00',
                    'stock' => 10,
                ],
            ], 200),
        ]);

        $response = $this->postJson('/orders', ['user_id' => 7]);

        $response->assertStatus(409)
            ->assertJson([
                'message' => 'Stok Monitor 24 Inch tidak mencukupi: diminta 11, tersedia 10',
            ]);
    }

    /**
     * Skenario 5: POST /orders gagal saat product-service mati/unreachable (503).
     */
    public function test_post_orders_fails_when_product_service_down(): void
    {
        Http::fake([
            '*/carts/7' => Http::response([
                'message' => 'Berhasil mengambil keranjang',
                'data' => [
                    'user_id' => 7,
                    'items' => [
                        ['id' => 1, 'user_id' => 7, 'product_id' => 1, 'quantity' => 2],
                    ],
                ],
            ], 200),
            '*/products/1' => Http::response(null, 500),
        ]);

        $response = $this->postJson('/orders', ['user_id' => 7]);

        $response->assertStatus(503)
            ->assertJson([
                'message' => 'Product service tidak dapat dihubungi, coba lagi nanti',
            ]);
    }

    /**
     * Skenario 6: GET /orders/{id} 404 jika pesanan tidak ditemukan.
     */
    public function test_get_order_returns_404_when_not_found(): void
    {
        $response = $this->getJson('/orders/9999');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Pesanan dengan id 9999 tidak ditemukan',
            ]);
    }

    /**
     * Skenario 7: GET /orders/user/{userId} gagal jika status tidak valid (422).
     */
    public function test_get_user_orders_fails_with_invalid_status(): void
    {
        $response = $this->getJson('/orders/user/7?status=SHIPPED');

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Data yang dikirim tidak valid',
                'errors' => [
                    'status' => ['Status harus PENDING atau PAID'],
                ],
            ]);
    }
}
