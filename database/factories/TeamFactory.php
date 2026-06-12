<?php

namespace Database\Factories;

use App\Enums\Visibility;
use App\Models\Team;
use App\Models\User;
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
            'visibility' => Visibility::Private,
            'created_by_user_id' => null,
        ];
    }

    /**
     * @return $this
     */
    public function public(?User $creator = null): static
    {
        return $this->state(fn (): array => [
            'visibility' => Visibility::Public,
            'created_by_user_id' => $creator?->id,
        ]);
    }
}
