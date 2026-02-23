<?php

namespace Tests\Unit;

use Tests\Unit\TestCase;
use App\Models\User;
use PDO;

/**
 * Unit tests for the User model
 */
class UserModelTest extends TestCase
{
    /** @var PDO */
    private $db;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = getTestDbConnection();
    }

    public function testUserModelCanBeCreated()
    {
        $user = new User([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
    }

    public function testUserCanBeSavedToDatabase()
    {
        $user = new User([
            'name' => 'Database Test User',
            'email' => 'dbtest@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'status' => 'active'
        ]);

        $result = $user->save();
        $this->assertTrue($result);

        // Verify it was saved
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute(['dbtest@example.com']);
        $savedUser = $stmt->fetch();

        $this->assertNotFalse($savedUser);
        $this->assertEquals('Database Test User', $savedUser['name']);
    }

    public function testUserCanBeFoundById()
    {
        // First create a user
        $user = new User([
            'name' => 'Find Test User',
            'email' => 'findtest@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'status' => 'active'
        ]);
        $user->save();
        $userId = $this->db->lastInsertId();

        // Now find it
        $foundUser = User::find($userId);
        $this->assertNotNull($foundUser);
        $this->assertEquals('Find Test User', $foundUser->name);
        $this->assertEquals('findtest@example.com', $foundUser->email);
    }

    public function testUserWhereQueryWorks()
    {
        // Create test users
        $user1 = new User([
            'name' => 'Query Test User 1',
            'email' => 'querytest1@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'status' => 'active'
        ]);
        $user1->save();

        $user2 = new User([
            'name' => 'Query Test User 2',
            'email' => 'querytest2@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'status' => 'inactive'
        ]);
        $user2->save();

        // Test where query
        $activeUsers = User::where('status', 'active')->get();
        $this->assertCount(1, $activeUsers);
        $this->assertEquals('Query Test User 1', $activeUsers[0]->name);
    }

    public function testUserToArrayWorks()
    {
        $user = new User([
            'name' => 'Array Test User',
            'email' => 'arraytest@example.com',
            'password' => 'password123'
        ]);

        $array = $user->toArray();
        $this->assertIsArray($array);
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('email', $array);
        $this->assertEquals('Array Test User', $array['name']);
        $this->assertEquals('arraytest@example.com', $array['email']);
    }

    public function testUserJsonSerializationWorks()
    {
        $user = new User([
            'name' => 'JSON Test User',
            'email' => 'jsontest@example.com',
            'password' => 'password123'
        ]);

        $json = $user->toJson();
        $this->assertIsString($json);

        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded);
        $this->assertEquals('JSON Test User', $decoded['name']);
    }
}
