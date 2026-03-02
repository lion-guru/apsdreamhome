<?php
namespace Tests\Integration\API;

use Tests\TestCase;
use App\Http\Controllers\PropertyController;

class PropertyAPITest extends TestCase
{
    public function testCanCreateProperty()
    {
        $user = $this->createTestUser();
        
        $propertyData = [
            'title' => 'Beautiful Apartment',
            'description' => 'A beautiful apartment in the city center',
            'price' => 250000.00,
            'location' => 'City Center',
            'property_type' => 'apartment',
            'user_id' => $user->id
        ];
        
        $controller = new PropertyController();
        $response = $controller->create($propertyData);
        
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertJsonStructure($response->getContent(), [
            'success' => true,
            'data' => [
                'id' => '',
                'title' => '',
                'price' => '',
                'location' => ''
            ]
        ]);
    }
    
    public function testCanGetProperties()
    {
        $user = $this->createTestUser();
        $this->createTestProperty(['user_id' => $user->id]);
        $this->createTestProperty(['user_id' => $user->id]);
        
        $controller = new PropertyController();
        $response = $controller->index();
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJsonStructure($response->getContent(), [
            'success' => true,
            'data' => [
                '*' => [
                    'id' => '',
                    'title' => '',
                    'price' => '',
                    'location' => ''
                ]
            ]
        ]);
    }
    
    public function testCanGetProperty()
    {
        $property = $this->createTestProperty();
        
        $controller = new PropertyController();
        $response = $controller->show($property->id);
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJsonStructure($response->getContent(), [
            'success' => true,
            'data' => [
                'id' => '',
                'title' => '',
                'description' => '',
                'price' => '',
                'location' => '',
                'property_type' => ''
            ]
        ]);
    }
    
    public function testCanUpdateProperty()
    {
        $property = $this->createTestProperty();
        
        $updateData = [
            'title' => 'Updated Property Title',
            'price' => 300000.00
        ];
        
        $controller = new PropertyController();
        $response = $controller->update($property->id, $updateData);
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJsonStructure($response->getContent(), [
            'success' => true,
            'data' => [
                'id' => '',
                'title' => 'Updated Property Title',
                'price' => 300000.00
            ]
        ]);
    }
    
    public function testCanDeleteProperty()
    {
        $property = $this->createTestProperty();
        
        $controller = new PropertyController();
        $response = $controller->delete($property->id);
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJsonStructure($response->getContent(), [
            'success' => true,
            'message' => ''
        ]);
        
        $this->assertDatabaseMissing('properties', ['id' => $property->id]);
    }
    
    public function testCanSearchProperties()
    {
        $this->createTestProperty(['title' => 'Beautiful Apartment', 'location' => 'City Center']);
        $this->createTestProperty(['title' => 'Modern House', 'location' => 'Suburbs']);
        
        $searchData = [
            'query' => 'Beautiful',
            'location' => 'City Center'
        ];
        
        $controller = new PropertyController();
        $response = $controller->search($searchData);
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJsonStructure($response->getContent(), [
            'success' => true,
            'data' => [
                '*' => [
                    'id' => '',
                    'title' => '',
                    'location' => ''
                ]
            ]
        ]);
    }
}
