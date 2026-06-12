<?php

namespace Database\Factories;

use App\Models\Status;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Status>
 */
class StatusFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'icon' => 'clock',
            'light_theme_color' => fake()->hexColor(),
            'dark_theme_color' => fake()->hexColor(),
            'sort_order' => fake()->numberBetween(1, 10),
        ];
    }
}
