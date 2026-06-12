<?php

namespace Database\Seeders;

use App\Models\Status;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\Concerns\SeedsVisibilityGrants;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    use SeedsVisibilityGrants;

    private const TARGET_TASK_COUNT = 1000;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tasksToCreate = self::TARGET_TASK_COUNT - Task::query()->count();

        if ($tasksToCreate <= 0) {
            return;
        }

        $users = User::query()->get();
        $teams = Team::query()->get();

        Task::factory()->count($tasksToCreate)->create();

        Task::query()
            ->latest('id')
            ->limit($tasksToCreate)
            ->get()
            ->each(fn (Task $task) => $this->maybeSeedVisibilityGrants($task, $users, $teams));

        $this->seedDateChangeHistory();
        $this->seedStatusChangeHistory();
    }

    protected function seedDateChangeHistory(): void
    {
        Task::query()
            ->inRandomOrder()
            ->limit(270)
            ->get()
            ->each(function (Task $task): void {
                if ($task->start_date !== null) {
                    $originalStart = $task->start_date;
                    $task->update([
                        'start_date' => $originalStart->copy()->addDays(fake()->numberBetween(1, 7)),
                    ]);
                }

                if ($task->due_date !== null) {
                    $originalDue = $task->due_date;
                    $task->update([
                        'due_date' => $originalDue->copy()->addDays(fake()->numberBetween(-3, 14)),
                    ]);
                }
            });
    }

    protected function seedStatusChangeHistory(): void
    {
        $statuses = Status::query()->orderBy('sort_order')->get();
        $users = User::query()->inRandomOrder()->limit(20)->get();

        Task::query()
            ->inRandomOrder()
            ->limit(400)
            ->get()
            ->each(function (Task $task) use ($statuses, $users): void {
                $progression = $statuses->random(fake()->numberBetween(1, 3));

                foreach ($progression as $status) {
                    $task->setStatus($status, $users->random());
                }
            });
    }
}
