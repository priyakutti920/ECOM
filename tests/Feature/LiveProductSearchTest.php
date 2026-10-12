<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiveProductSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_live_search_returns_empty_when_query_is_empty(): void
    {
        $response = $this->getJson(route('api.search.live', ['q' => '']));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'count' => 0,
                'total' => 0,
                'products' => [],
            ]);
    }

    public function test_live_search_finds_active_products_matching_query(): void
    {
        $cat = Category::create([
            'name' => 'Streetwear',
            'slug' => 'streetwear',
            'is_active' => true,
        ]);

        $prod1 = Product::create([
            'name' => 'Cyberpunk Acid Wash Hoodie',
            'slug' => 'cyberpunk-acid-wash-hoodie',
            'category_id' => $cat->id,
            'price' => 1499.00,
            'special_price' => 999.00,
            'stock_status' => 'in_stock',
            'is_active' => true,
            'qty' => 15,
        ]);

        $prod2 = Product::create([
            'name' => 'Vintage Cargo Pants',
            'slug' => 'vintage-cargo-pants',
            'category_id' => $cat->id,
            'price' => 1299.00,
            'stock_status' => 'in_stock',
            'is_active' => true,
            'qty' => 5,
        ]);

        $inactiveProd = Product::create([
            'name' => 'Hidden Stealth Hoodie',
            'slug' => 'hidden-stealth-hoodie',
            'category_id' => $cat->id,
            'price' => 1999.00,
            'stock_status' => 'in_stock',
            'is_active' => false,
            'qty' => 10,
        ]);

        $response = $this->getJson(route('api.search.live', ['q' => 'Cyberpunk']));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'count' => 1,
                'total' => 1,
            ]);

        $data = $response->json();
        $this->assertEquals($prod1->id, $data['products'][0]['id']);
        $this->assertEquals('Cyberpunk Acid Wash Hoodie', $data['products'][0]['name']);
        $this->assertTrue($data['products'][0]['is_on_sale']);
        $this->assertTrue($data['products'][0]['in_stock']);
        $this->assertStringContainsString('Cyberpunk', $data['view_all_url']);
    }

    public function test_live_search_filters_by_category(): void
    {
        $cat1 = Category::create(['name' => 'T-Shirts', 'slug' => 't-shirts', 'is_active' => true]);
        $cat2 = Category::create(['name' => 'Jackets', 'slug' => 'jackets', 'is_active' => true]);

        Product::create([
            'name' => 'Graphic Tee Black',
            'slug' => 'graphic-tee-black',
            'category_id' => $cat1->id,
            'price' => 599,
            'is_active' => true,
        ]);

        Product::create([
            'name' => 'Denim Jacket Black',
            'slug' => 'denim-jacket-black',
            'category_id' => $cat2->id,
            'price' => 2499,
            'is_active' => true,
        ]);

        // Search 'Black' filtered by Jackets category
        $response = $this->getJson(route('api.search.live', [
            'q' => 'Black',
            'category' => $cat2->id,
        ]));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'count' => 1,
                'total' => 1,
            ]);

        $this->assertEquals('Denim Jacket Black', $response->json('products.0.name'));
    }

    public function test_search_page_renders_matching_products(): void
    {
        $product = Product::create([
            'name' => 'Oversized Anime Tee',
            'slug' => 'oversized-anime-tee',
            'price' => 799,
            'is_active' => true,
        ]);

        $response = $this->get('/search?q=Anime');

        $response->assertStatus(200)
            ->assertSee('Results for')
            ->assertSee('Anime')
            ->assertSee('Oversized Anime Tee');
    }

    public function test_storefront_layout_renders_backdrop_blur_and_live_search_containers(): void
    {
        $response = $this->get(route('shop.home'));

        $response->assertStatus(200)
            ->assertSee('id="liveSearchBackdrop"', false)
            ->assertSee('class="live-search-backdrop"', false)
            ->assertSee('id="desktopSearchContainer"', false)
            ->assertSee('id="desktopSearchDropdown"', false)
            ->assertDontSee('id="mobileSearchContainer"', false)
            ->assertDontSee('id="mobileSearchDropdown"', false)
            ->assertSee('/api/search/live', false);
    }

    public function test_search_and_live_search_by_category_slug(): void
    {
        $cat = Category::create([
            'name' => 'Ethnic Wear',
            'slug' => 'ethnic-wear',
            'is_active' => true,
        ]);

        $prod = Product::create([
            'name' => 'Silk Kurta',
            'slug' => 'silk-kurta',
            'category_id' => $cat->id,
            'price' => 1999,
            'is_active' => true,
        ]);

        // Search by category slug on /search
        $res = $this->get('/search?category=ethnic-wear');
        $res->assertStatus(200);
        $res->assertSee('Category:');
        $res->assertSee('Ethnic Wear');
        $res->assertSee('Silk Kurta');

        // Live search by category slug
        $liveRes = $this->getJson(route('api.search.live', [
            'q' => 'Silk',
            'category' => 'ethnic-wear',
        ]));
        $liveRes->assertStatus(200);
        $liveRes->assertJsonPath('success', true);
        $liveRes->assertJsonPath('count', 1);
        $this->assertEquals('Silk Kurta', $liveRes->json('products.0.name'));
    }

    public function test_search_sorting_by_price(): void
    {
        Product::create(['name' => 'Cheap Item', 'slug' => 'cheap-item', 'price' => 100, 'is_active' => true]);
        Product::create(['name' => 'Expensive Item', 'slug' => 'expensive-item', 'price' => 1000, 'is_active' => true]);

        $res = $this->get('/search?q=Item&sort=price_asc');
        $res->assertStatus(200);
        $res->assertSeeInOrder(['Cheap Item', 'Expensive Item']);

        $resDesc = $this->get('/search?q=Item&sort=price_desc');
        $resDesc->assertStatus(200);
        $resDesc->assertSeeInOrder(['Expensive Item', 'Cheap Item']);
    }

    public function test_product_detail_page_renders_share_modal_and_actions(): void
    {
        $prod = Product::create([
            'name' => 'Handloom Cotton Sari',
            'slug' => 'handloom-cotton-sari',
            'price' => 2499,
            'is_active' => true,
        ]);

        $res = $this->get('/product/handloom-cotton-sari');
        $res->assertStatus(200);
        $res->assertSee('shareProduct()', false);
        $res->assertSee('id="shareModal"', false);
        $res->assertSee('copyShareLink()', false);
        $res->assertSee("shareOn('whatsapp')", false);
    }
}
