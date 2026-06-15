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
    public const COLORS = [
        'slate', 'gray', 'zinc', 'stone',
        'red', 'orange', 'amber', 'yellow',
        'lime', 'green', 'emerald', 'teal',
        'cyan', 'sky', 'blue', 'indigo',
        'violet', 'purple', 'fuchsia', 'pink',
        'rose', 'coral', 'maroon', 'navy',
        'forest', 'sage', 'mint', 'lavender',
        'gold', 'brown',
    ];

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'icon' => 'clock',
            'color' => fake()->randomElement(self::COLORS),
            'sort_order' => fake()->numberBetween(1, 10),
            'is_closed' => false,
        ];
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_closed' => true,
        ]);
    }
}
