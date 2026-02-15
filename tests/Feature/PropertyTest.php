<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use ApsDreamHome\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PropertyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test property listing page loads successfully.
     *
     * @return void
     */
    public function test_property_listing_page_loads()
    {
        $response = $this->get('/properties');
        
        $response->assertStatus(200);
        $response->assertSee('Properties');
        $response->assertViewIs('properties.index');
    }

    /**
     * Test property search functionality.
     *
     * @return void
     */
    public function test_property_search_works()
    {
        // Create test properties
        $property1 = Property::factory()->create([
            'title' => 'Luxury Villa in Mumbai',
            'price' => 5000000,
            'status' => 'available'
        ]);

        $property2 = Property::factory()->create([
            'title' => 'Apartment in Delhi',
            'price' => 2500000,
            'status' => 'sold'
        ]);

        // Search for available properties
        $response = $this->get('/properties?status=available');
        
        $response->assertStatus(200);
        $response->assertSee('Luxury Villa in Mumbai');
        $response->assertDontSee('Apartment in Delhi');
    }

    /**
     * Test property detail page loads with correct data.
     *
     * @return void
     */
    public function test_property_detail_page_loads()
    {
        $property = Property::factory()->create([
            'title' => 'Test Property',
            'description' => 'This is a test property',
            'price' => 1000000,
            'status' => 'available'
        ]);

        $response = $this->get("/properties/" . $property->id);
        
        $response->assertStatus(200);
        $response->assertSee('Test Property');
        $response->assertSee('1,000,000');
        $response->assertViewHas('property', function ($viewProperty) use ($property) {
            return $viewProperty->id === $property->id;
        });
    }

    /**
     * Test property filtering by price range.
     *
     * @return void
     */
    public function test_property_filter_by_price()
    {
        // Create test properties with different prices
        $property1 = Property::factory()->create(['price' => 1000000]);
        $property2 = Property::factory()->create(['price' => 2000000]);
        $property3 = Property::factory()->create(['price' => 3000000]);

        // Filter properties between 1.5M and 2.5M
        $response = $this->get('/properties?min_price=1500000&max_price=2500000');
        
        $response->assertStatus(200);
        $response->assertSee('2,000,000');
        $response->assertDontSee('1,000,000');
        $response->assertDontSee('3,000,000');
    }

    /**
     * Test property creation by admin.
     *
     * @return void
     */
    public function test_admin_can_create_property()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        $response = $this->actingAs($admin)
                         ->post('/admin/properties', [
                             'title' => 'New Property',
                             'description' => 'This is a new property',
                             'price' => 1500000,
                             'status' => 'available',
                             'bedrooms' => 3,
                             'bathrooms' => 2,
                             'area' => 1800,
                             'address' => '123 Test Street',
                             'city' => 'Mumbai',
                             'state' => 'Maharashtra',
                             'pincode' => '400001'
                         ]);
        
        $response->assertRedirect('/admin/properties');
        $this->assertDatabaseHas('properties', ['title' => 'New Property']);
    }
}
