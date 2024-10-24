<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Phone>
 */
class PhoneFactory extends Factory
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
            'client_id' => fake()->randomElement(Client::pluck('id')),
            //'client_id' => Client::factory(),
            'number' => fake()->numerify('##########'),
            'created_at' => $date,
            'updated_at' => $date
        ];
    }
}
