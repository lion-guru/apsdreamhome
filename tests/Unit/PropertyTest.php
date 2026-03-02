<?php
namespace Tests\Unit;

use App\Models\Property;
use Tests\TestCase;

class PropertyTest extends TestCase
{
    public function testPropertyCanBeCreated()
    {
        $userId = $this->createTestUser();
        
        $propertyData = [
            'title' => 'Beautiful Apartment',
            'description' => 'A beautiful apartment in the city center',
            'price' => 250000.00,
            'location' => 'City Center',
            'property_type' => 'apartment',
            'user_id' => $userId
        ];
        
        $property = new Property();
        $property->fill($propertyData);
        $property->save();
        
        $this->assertDatabaseHas('properties', [
            'title' => 'Beautiful Apartment',
            'price' => 250000.00
        ]);
    }
    
    public function testPropertyBelongsToUser()
    {
        $user = $this->createTestUser();
        $property = $this->createTestProperty(['user_id' => $user->id]);
        
        $this->assertEquals($user->id, $property->user->id);
        $this->assertEquals($user->name, $property->user->name);
    }
    
    public function testPropertyCanChangeStatus()
    {
        $property = $this->createTestProperty();
        
        $property->changeStatus('sold');
        
        $this->assertEquals('sold', $property->status);
        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'status' => 'sold'
        ]);
    }
    
    public function testPropertyCanUpdatePrice()
    {
        $property = $this->createTestProperty();
        $newPrice = 300000.00;
        
        $property->updatePrice($newPrice);
        
        $this->assertEquals($newPrice, $property->price);
        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'price' => $newPrice
        ]);
    }
    
    public function testPropertyScopeActive()
    {
        $activeProperty = $this->createTestProperty(['status' => 'active']);
        $inactiveProperty = $this->createTestProperty(['status' => 'inactive']);
        
        $activeProperties = Property::active()->get();
        
        $this->assertCount(1, $activeProperties);
        $this->assertEquals($activeProperty->id, $activeProperties->first()->id);
    }
}
