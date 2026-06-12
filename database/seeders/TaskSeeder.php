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

    private const TARGET_STANDALONE_TASK_COUNT = 5000;

    private const DATE_CHANGE_HISTORY_RATIO = 0.15;

    private const STATUS_CHANGE_HISTORY_RATIO = 0.20;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $standaloneTaskCount = Task::query()
            ->where('owner_type', '!=', 'project')
            ->count();

        $tasksToCreate = self::TARGET_STANDALONE_TASK_COUNT - $standaloneTaskCount;

        if ($tasksToCreate <= 0) {
            $this->seedDateChangeHistory();
            $this->seedStatusChangeHistory();

            return;
        }

        $users = User::query()->get();
        $teams = Team::query()->get();

        Task::factory()->count($tasksToCreate)->create();

        Task::query()
            ->where('owner_type', '!=', 'project')
            ->latest('id')
            ->limit($tasksToCreate)
            ->get()
            ->each(fn (Task $task) => $this->maybeSeedVisibilityGrants($task, $users, $teams));

        $this->seedDateChangeHistory();
        $this->seedStatusChangeHistory();
    }

    protected function seedDateChangeHistory(): void
    {
        $limit = (int) ceil(Task::query()->count() * self::DATE_CHANGE_HISTORY_RATIO);

        Task::query()
            ->inRandomOrder()
            ->limit($limit)
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
        $users = User::query()->inRandomOrder()->limit(50)->get();
        $limit = (int) ceil(Task::query()->count() * self::STATUS_CHANGE_HISTORY_RATIO);

        Task::query()
            ->inRandomOrder()
            ->limit($limit)
            ->get()
            ->each(function (Task $task) use ($statuses, $users): void {
                $progression = $statuses->random(fake()->numberBetween(1, 3));

                foreach ($progression as $status) {
                    $task->setStatus($status, $users->random());
                }
            });
    }
}
