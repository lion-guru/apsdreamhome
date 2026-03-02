<?php
namespace Tests\Integration\API;

use Tests\TestCase;
use App\Http\Controllers\UserController;

class UserAPITest extends TestCase
{
    public function testCanRegisterUser()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];
        
        $controller = new UserController();
        $response = $controller->register($userData);
        
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertJsonStructure($response->getContent(), [
            'success' => true,
            'data' => [
                'id' => '',
                'name' => '',
                'email' => ''
            ]
        ]);
    }
    
    public function testCannotRegisterUserWithDuplicateEmail()
    {
        $this->createTestUser(['email' => 'test@example.com']);
        
        $userData = [
            'name' => 'Jane Doe',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];
        
        $controller = new UserController();
        $response = $controller->register($userData);
        
        $this->assertEquals(422, $response->getStatusCode());
        $this->assertJsonStructure($response->getContent(), [
            'success' => false,
            'message' => '',
            'errors' => ''
        ]);
    }
    
    public function testCanLoginUser()
    {
        $password = 'password123';
        $user = $this->createTestUser([
            'email' => 'test@example.com',
            'password' => password_hash($password, PASSWORD_DEFAULT)
        ]);
        
        $loginData = [
            'email' => 'test@example.com',
            'password' => $password
        ];
        
        $controller = new UserController();
        $response = $controller->login($loginData);
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJsonStructure($response->getContent(), [
            'success' => true,
            'data' => [
                'token' => '',
                'user' => [
                    'id' => '',
                    'name' => '',
                    'email' => ''
                ]
            ]
        ]);
    }
    
    public function testCannotLoginWithInvalidCredentials()
    {
        $this->createTestUser(['email' => 'test@example.com']);
        
        $loginData = [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ];
        
        $controller = new UserController();
        $response = $controller->login($loginData);
        
        $this->assertEquals(401, $response->getStatusCode());
        $this->assertJsonStructure($response->getContent(), [
            'success' => false,
            'message' => ''
        ]);
    }
    
    public function testCanGetUserProfile()
    {
        $user = $this->createTestUser();
        
        $controller = new UserController();
        $response = $controller->profile($user->id);
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJsonStructure($response->getContent(), [
            'success' => true,
            'data' => [
                'id' => '',
                'name' => '',
                'email' => '',
                'role' => '',
                'status' => ''
            ]
        ]);
    }
    
    public function testCanUpdateUserProfile()
    {
        $user = $this->createTestUser();
        
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ];
        
        $controller = new UserController();
        $response = $controller->updateProfile($user->id, $updateData);
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJsonStructure($response->getContent(), [
            'success' => true,
            'data' => [
                'id' => '',
                'name' => 'Updated Name',
                'email' => 'updated@example.com'
            ]
        ]);
    }
}
