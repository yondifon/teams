<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Team>
 */
class TeamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->company(),
            'user_id' => User::factory(),
        ];
    }

    public function personalTeam(): self
    {
        return $this->state(fn (array $attributes) => [
            'personal_team' => true,
        ]);
    }

    public function regularTeam(): self
    {
        return $this->state(fn (array $attributes) => [
            'personal_team' => false,
        ]);
    }
}
