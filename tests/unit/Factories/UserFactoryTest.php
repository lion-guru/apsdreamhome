<?php

namespace Tests\Unit\Factories;

use Tests\TestCase;
use Tests\TestHelper;

class UserFactoryTest extends TestCase
{
    /** @test */
    public function it_creates_a_user_with_default_attributes()
    {
        $user = create('App\Models\User');
        
        $this->assertArrayHasKey('id', $user);
        $this->assertArrayHasKey('name', $user);
        $this->assertArrayHasKey('email', $user);
        $this->assertArrayHasKey('password', $user);
        $this->assertArrayHasKey('role', $user);
        $this->assertEquals('user', $user['role']);
    }
    
    /** @test */
    public function it_creates_an_admin_user()
    {
        $user = create('App\Models\User', ['role' => 'admin']);
        
        $this->assertEquals('admin', $user['role']);
    }
    
    /** @test */
    public function it_creates_an_agent_user()
    {
        $user = create('App\Models\User', ['role' => 'agent']);
        
        $this->assertEquals('agent', $user['role']);
    }
    
    /** @test */
    public function it_creates_a_user_with_custom_password()
    {
        $password = 'custom_password';
        $user = create('App\Models\User', ['password' => $password]);
        
        $this->assertTrue(password_verify($password, $user['password']));
    }
    
    /** @test */
    public function it_creates_multiple_users()
    {
        $users = create('App\Models\User', [], 5);
        
        $this->assertCount(5, $users);
        $this->assertNotEquals($users[0]['email'], $users[1]['email']);
    }
}
