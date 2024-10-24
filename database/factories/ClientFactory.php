<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $date = date('Y-m-d H:i:s', time() - rand(999, 9999999));

        return [
            'name' => fake()->name(),
            'personal_no' => fake()->unique()->numerify('##########'),
            'card_no' => fake()->unique()->numerify('#########'),
            'created_at' => $date,
            'updated_at' => $date
        ];
    }
}
