<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Database\Factories\TeamFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (TeamFactory::TEAM_NAMES as $name) {
            Team::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name],
            );
        }

        $this->seedMembers();
    }

    protected function seedMembers(): void
    {
        $teams = Team::query()->get();
        $users = User::query()->get();

        if ($teams->isEmpty() || $users->isEmpty()) {
            return;
        }

        $maxTeamsPerUser = min(3, $teams->count());

        foreach ($users as $user) {
            $user->syncTeams(
                $teams->random(fake()->numberBetween(1, $maxTeamsPerUser)),
            );
        }
    }
}
