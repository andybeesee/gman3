<?php

namespace Database\Factories;

use App\Enums\ProjectOwnership;
use App\Models\Project;
use App\Models\Status;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->optional(0.8)->dateTimeBetween('-1 month', '+1 month');
        $dueDate = $startDate
            ? fake()->dateTimeBetween($startDate, (clone $startDate)->modify('+3 months'))
            : fake()->optional(0.7)->dateTimeBetween('now', '+6 months');

        return [
            'title' => fake()->sentence(3),
            'description' => fake()->optional(0.6)->paragraph(),
            'start_date' => $startDate,
            'due_date' => $dueDate,
            'ownership' => ProjectOwnership::Team,
            'owner_user_id' => null,
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Project $project): void {
            $status = Status::query()->inRandomOrder()->first();

            if ($status !== null && $project->status?->id !== $status->id) {
                $project->setStatus($status);
            }

            $teams = Team::query()->inRandomOrder()->limit(fake()->numberBetween(1, 2))->get();

            $project->syncTeams($teams);
        });
    }

    /**
     * @return $this
     */
    public function personallyOwnedBy(User $user): static
    {
        return $this->state(fn (): array => [
            'ownership' => ProjectOwnership::User,
            'owner_user_id' => $user->id,
        ])->afterCreating(function (Project $project): void {
            $project->teams()->detach();
        });
    }

    /**
     * @return $this
     */
    public function teamOwned(): static
    {
        return $this->state(fn (): array => [
            'ownership' => ProjectOwnership::Team,
            'owner_user_id' => null,
        ]);
    }
}
