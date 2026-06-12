<?php

namespace Database\Factories;

use App\Enums\Visibility;
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
            'visibility' => fake()->boolean(20) ? Visibility::Public : Visibility::Private,
            'created_by_user_id' => null,
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

            if ($project->created_by_user_id === null) {
                $creator = User::query()->inRandomOrder()->first();

                if ($creator !== null) {
                    $project->forceFill(['created_by_user_id' => $creator->id])->save();
                }
            }
        });
    }

    /**
     * @return $this
     */
    public function privateForUser(User $user): static
    {
        return $this->state(fn (): array => [
            'visibility' => Visibility::Private,
            'created_by_user_id' => $user->id,
            'owner_user_id' => $user->id,
        ])->afterCreating(function (Project $project): void {
            $project->teams()->detach();
        });
    }

    /**
     * @return $this
     */
    public function privateForTeam(?User $creator = null): static
    {
        return $this->state(fn (): array => [
            'visibility' => Visibility::Private,
            'created_by_user_id' => $creator?->id,
            'owner_user_id' => null,
        ]);
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

    /**
     * @return $this
     *
     * @deprecated Use privateForUser() instead.
     */
    public function personallyOwnedBy(User $user): static
    {
        return $this->privateForUser($user);
    }

    /**
     * @return $this
     *
     * @deprecated Use privateForTeam() instead.
     */
    public function teamOwned(?User $creator = null): static
    {
        return $this->privateForTeam($creator);
    }
}
