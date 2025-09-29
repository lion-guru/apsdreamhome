<?php

namespace Tests\Unit\Factories;

use PHPUnit\Framework\TestCase;
use Tests\TestHelper;

class PropertyFactoryTest extends TestCase
{
    /** @test */
    public function it_creates_a_property_with_default_attributes()
    {
        $property = create('App\Models\Property');
        
        $this->assertArrayHasKey('id', $property);
        $this->assertArrayHasKey('title', $property);
        $this->assertArrayHasKey('description', $property);
        $this->assertArrayHasKey('price', $property);
        $this->assertArrayHasKey('bedrooms', $property);
        $this->assertArrayHasKey('bathrooms', $property);
        $this->assertArrayHasKey('area', $property);
        $this->assertArrayHasKey('address', $property);
        $this->assertArrayHasKey('city', $property);
        $this->assertArrayHasKey('state', $property);
        $this->assertArrayHasKey('zip_code', $property);
        $this->assertArrayHasKey('type', $property);
        $this->assertArrayHasKey('status', $property);
        
        // Check if the property type is valid
        $validTypes = ['house', 'apartment', 'condo', 'townhouse'];
        $this->assertContains($property['type'], $validTypes);
        
        // Check if the status is valid
        $validStatuses = ['available', 'pending', 'sold', 'rented'];
        $this->assertContains($property['status'], $validStatuses);
    }
    
    /** @test */
    public function it_creates_a_property_with_specific_type()
    {
        $property = create('App\Models\Property', ['type' => 'apartment']);
        $this->assertEquals('apartment', $property['type']);
        
        $property = create('App\Models\Property', ['type' => 'house']);
        $this->assertEquals('house', $property['type']);
    }
    
    /** @test */
    public function it_creates_a_property_with_specific_status()
    {
        $property = create('App\Models\Property', ['status' => 'sold']);
        $this->assertEquals('sold', $property['status']);
        
        $property = create('App\Models\Property', ['status' => 'available']);
        $this->assertEquals('available', $property['status']);
    }
    
    /** @test */
    public function it_creates_a_property_in_specific_location()
    {
        $city = 'San Francisco';
        $state = 'CA';
        
        $property = create('App\Models\Property', [
            'city' => $city,
            'state' => $state,
        ]);
        
        $this->assertEquals($city, $property['city']);
        $this->assertEquals($state, $property['state']);
    }
    
    /** @test */
    public function it_creates_multiple_properties()
    {
        $properties = create('App\Models\Property', [], 3);
        
        $this->assertCount(3, $properties);
        $this->assertNotEquals($properties[0]['title'], $properties[1]['title']);
    }
}
