<?php

namespace Database\Factories;

use App\Models\Status;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

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
            $teamCount = fake()->numberBetween(1, 2);

            $task->assignees()->attach(
                User::query()->inRandomOrder()->limit($assigneeCount)->pluck('id'),
            );

            $task->teams()->attach(
                Team::query()->inRandomOrder()->limit($teamCount)->pluck('id'),
            );
        });
    }
}
