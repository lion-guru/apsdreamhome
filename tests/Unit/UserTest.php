<?php
namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function testUserCanBeCreated()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'role' => 'user'
        ];
        
        $user = new User();
        $user->fill($userData);
        $user->save();
        
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);
    }
    
    public function testUserEmailMustBeUnique()
    {
        $this->createTestUser([
            'email' => 'test@example.com'
        ]);
        
        $this->expectException(\Exception::class);
        
        $user = new User();
        $user->fill([
            'name' => 'Jane Doe',
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);
        $user->save();
    }
    
    public function testUserCanAuthenticate()
    {
        $password = 'password123';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $user = new User();
        $user->fill([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => $hashedPassword
        ]);
        $user->save();
        
        $this->assertTrue($user->authenticate($password));
        $this->assertFalse($user->authenticate('wrongpassword'));
    }
    
    public function testUserHasRole()
    {
        $user = new User();
        $user->fill([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'role' => 'admin'
        ]);
        $user->save();
        
        $this->assertTrue($user->hasRole('admin'));
        $this->assertFalse($user->hasRole('user'));
    }
    
    public function testUserCanChangePassword()
    {
        $user = $this->createTestUser();
        $newPassword = 'newpassword123';
        
        $user->changePassword($newPassword);
        
        $this->assertTrue($user->authenticate($newPassword));
        $this->assertFalse($user->authenticate('password123'));
    }
}
