<?php

namespace Database\Factories;

use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Team>
 */
class TeamFactory extends Factory
{
    /**
     * @var list<string>
     */
    public const TEAM_NAMES = [
        'Engineering',
        'Design',
        'Product',
        'Marketing',
        'Support',
        'Operations',
        'Finance',
        'Legal',
        'Customer Success',
        'Sales',
        'Human Resources',
        'Research',
        'Infrastructure',
        'Security',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement(self::TEAM_NAMES);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}
