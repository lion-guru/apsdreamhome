<?php

namespace Tests\Factories;

class PropertyFactory extends Factory
{
    /**
     * The model class this factory creates
     */
    protected string $model = 'App\Models\Property';

    /**
     * Define the model's default state
     */
    protected function definition(): array
    {
        $types = ['house', 'apartment', 'condo', 'townhouse'];
        $statuses = ['available', 'pending', 'sold', 'rented'];
        $cities = ['New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix'];
        $states = ['NY', 'CA', 'IL', 'TX', 'AZ'];
        
        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(3),
            'price' => $this->faker->randomFloat(2, 100000, 1000000),
            'bedrooms' => $this->faker->numberBetween(1, 5),
            'bathrooms' => $this->faker->randomFloat(1, 1, 4),
            'area' => $this->faker->numberBetween(800, 5000),
            'address' => $this->faker->streetAddress,
            'city' => $this->faker->randomElement($cities),
            'state' => $this->faker->randomElement($states),
            'zip_code' => $this->faker->postcode,
            'type' => $this->faker->randomElement($types),
            'status' => $this->faker->randomElement($statuses),
            'created_at' => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
            'updated_at' => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Set the property type
     */
    public function type(string $type): self
    {
        return $this->state(['type' => $type]);
    }

    /**
     * Set the property status
     */
    public function status(string $status): self
    {
        return $this->state(['status' => $status]);
    }

    /**
     * Set the property price range
     */
    public function priceRange(float $min, float $max): self
    {
        return $this->state([
            'price' => $this->faker->randomFloat(2, $min, $max)
        ]);
    }

    /**
     * Set the number of bedrooms
     */
    public function bedrooms(int $count): self
    {
        return $this->state(['bedrooms' => $count]);
    }

    /**
     * Set the location
     */
    public function location(string $city, string $state): self
    {
        return $this->state([
            'city' => $city,
            'state' => $state,
        ]);
    }

    /**
     * Set custom attributes for the model
     */
    public function state(array $state): self
    {
        $this->defaults = array_merge($this->defaults, $state);
        return $this;
    }

    /**
     * Override the make method to include defaults
     */
    public function make(array $attributes = []): array
    {
        return array_merge(
            $this->definition(),
            $this->defaults,
            $attributes
        );
    }
}
