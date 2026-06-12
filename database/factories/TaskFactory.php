<?php

namespace Database\Factories;

use App\Enums\Visibility;
use App\Models\Project;
use App\Models\Status;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
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
            ? fake()->dateTimeBetween($startDate, (clone $startDate)->modify('+3 weeks'))
            : fake()->optional(0.7)->dateTimeBetween('now', '+2 months');

        return [
            'title' => fake()->sentence(4),
            'description' => fake()->optional(0.6)->paragraph(),
            'start_date' => $startDate,
            'due_date' => $dueDate,
            'visibility' => fake()->boolean(20) ? Visibility::Public : Visibility::Private,
            'created_by_user_id' => null,
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Task $task): void {
            $status = Status::query()->inRandomOrder()->first();

            if ($status !== null && $task->status?->id !== $status->id) {
                $task->setStatus($status);
            }

            $assigneeCount = fake()->numberBetween(1, 3);
            $assignees = User::query()->inRandomOrder()->limit($assigneeCount)->get();

            $task->assignees()->attach($assignees->pluck('id'));

            if (fake()->boolean(70)) {
                $teamCount = fake()->numberBetween(1, 2);
                $teams = Team::query()->inRandomOrder()->limit($teamCount)->get();

                $task->teams()->attach($teams->pluck('id'));
                $task->setOwner($teams->first());
                $this->assignCreator($task);

                return;
            }

            $task->setOwner($assignees->first());
            $this->assignCreator($task);
        });
    }

    protected function assignCreator(Task $task): void
    {
        if ($task->created_by_user_id !== null) {
            return;
        }

        $creatorId = match ($task->owner_type) {
            'user' => $task->owner_id,
            'team' => $task->assignees()->value('users.id'),
            'project' => Project::query()->whereKey($task->owner_id)->value('created_by_user_id'),
            default => $task->assignees()->value('users.id'),
        };

        if ($creatorId === null) {
            $creatorId = User::query()->inRandomOrder()->value('id');
        }

        if ($creatorId !== null) {
            $task->forceFill(['created_by_user_id' => $creatorId])->save();
        }
    }

    /**
     * @return $this
     */
    public function ownedBy(User|Team|Project $owner): static
    {
        return $this->afterCreating(function (Task $task) use ($owner): void {
            $task->setOwner($owner);
        });
    }

    /**
     * @return $this
     */
    public function forProject(Project $project): static
    {
        return $this->afterCreating(function (Task $task) use ($project): void {
            $task->setOwner($project);
            $task->syncTeams($project->teams);

            $assignees = $this->assigneesForProject($project);

            if ($assignees->isNotEmpty()) {
                $task->syncAssignees($assignees);
            }

            $task->forceFill([
                'visibility' => fake()->boolean(90)
                    ? $project->visibility
                    : (fake()->boolean(50) ? Visibility::Public : Visibility::Private),
                'created_by_user_id' => $project->created_by_user_id
                    ?? $assignees->first()?->id
                    ?? User::query()->inRandomOrder()->value('id'),
            ])->save();
        });
    }

    /**
     * @return Collection<int, User>
     */
    protected function assigneesForProject(Project $project): Collection
    {
        if ($project->isUserOwned() && $project->owner_user_id !== null) {
            return User::query()->whereKey($project->owner_user_id)->get();
        }

        $memberIds = $project->teams()
            ->with('members')
            ->get()
            ->flatMap(fn (Team $team) => $team->members)
            ->unique('id')
            ->pluck('id');

        if ($memberIds->isEmpty()) {
            return User::query()->inRandomOrder()->limit(fake()->numberBetween(1, 3))->get();
        }

        return User::query()
            ->whereIn('id', $memberIds->random(min(fake()->numberBetween(1, 3), $memberIds->count()))->all())
            ->get();
    }
}
