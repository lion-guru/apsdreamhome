<?php

namespace Tests\Factories;

class UserFactory extends Factory
{
    /**
     * The model class this factory creates
     */
    protected $model = 'App\\Models\\User';
    
    /**
     * The table name
     */
    protected $table = 'users';

    /**
     * Define the model's default state
     */
    protected function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => password_hash('password', PASSWORD_BCRYPT),
            'phone' => $this->faker->phoneNumber,
            'role' => 'user',
            'email_verified_at' => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
            'created_at' => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
            'updated_at' => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Set the user's role
     */
    public function role(string $role): self
    {
        $this->defaults['role'] = $role;
        return $this;
    }

    /**
     * Set the user as an admin
     */
    public function admin(): self
    {
        return $this->role('admin');
    }

    /**
     * Set the user as an agent
     */
    public function agent(): self
    {
        return $this->role('agent');
    }

    /**
     * Set the user's password
     */
    public function password(string $password): self
    {
        $this->defaults['password'] = password_hash($password, PASSWORD_BCRYPT);
        return $this;
    }
    
    /**
     * Get the table name for this factory
     */
    protected function getTableName(): string
    {
        return $this->table ?? 'users';
    }
    
    /**
     * Get the parameter types for binding
     */
    protected function getBindTypes(array $attributes): string
    {
        $types = '';
        foreach ($attributes as $value) {
            if (is_int($value)) {
                $types .= 'i';
            } elseif (is_float($value)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
        }
        return $types;
    }
}
