<?php

namespace Database\Seeders;

use App\Models\Checklist;
use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\Concerns\SeedsVisibilityGrants;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class ChecklistSeeder extends Seeder
{
    use SeedsVisibilityGrants;

    private const TARGET_CHECKLIST_COUNT = 300;

    private const TASKS_PER_CHECKLIST_MIN = 3;

    private const TASKS_PER_CHECKLIST_MAX = 9;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $checklistsToCreate = self::TARGET_CHECKLIST_COUNT - Checklist::query()->count();

        if ($checklistsToCreate <= 0) {
            return;
        }

        $users = User::query()->get();
        $teams = Team::query()->get();
        $projects = Project::query()->with('teams')->get();

        for ($i = 0; $i < $checklistsToCreate; $i++) {
            $this->seedChecklist($users, $teams, $projects);
        }
    }

    /**
     * @param  Collection<int, User>  $users
     * @param  Collection<int, Team>  $teams
     * @param  Collection<int, Project>  $projects
     */
    protected function seedChecklist(Collection $users, Collection $teams, Collection $projects): void
    {
        if ($users->isEmpty()) {
            return;
        }

        $roll = fake()->numberBetween(1, 100);

        if ($roll <= 40 && $projects->isNotEmpty()) {
            $checklist = Checklist::factory()->ownedBy($projects->random())->create();
        } elseif ($roll <= 75 && $teams->isNotEmpty()) {
            $team = $teams->random();
            $checklist = Checklist::factory()->ownedBy($team)->create();
            $checklist->syncTeams([$team]);
        } else {
            $checklist = Checklist::factory()->ownedBy($users->random())->create();
        }

        $this->maybeSeedVisibilityGrants($checklist, $users, $teams);

        $taskCount = fake()->numberBetween(self::TASKS_PER_CHECKLIST_MIN, self::TASKS_PER_CHECKLIST_MAX);

        for ($position = 1; $position <= $taskCount; $position++) {
            Task::factory()
                ->ownedBy($checklist->owner)
                ->create([
                    'checklist_id' => $checklist->id,
                    'checklist_position' => $position,
                    'visibility' => $checklist->visibility,
                    'created_by_user_id' => $checklist->created_by_user_id,
                ]);
        }

        $checklist->syncTaskDateRollup();
    }
}
