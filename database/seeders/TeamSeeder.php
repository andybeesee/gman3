<?php

namespace Database\Seeders;

use App\Enums\Visibility;
use App\Models\Team;
use App\Models\User;
use Database\Factories\TeamFactory;
use Database\Seeders\Concerns\SeedsVisibilityGrants;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TeamSeeder extends Seeder
{
    use SeedsVisibilityGrants;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::query()->get();

        foreach (TeamFactory::TEAM_NAMES as $name) {
            $creator = $users->isNotEmpty() ? $users->random() : null;

            Team::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'visibility' => fake()->boolean(25) ? Visibility::Public : Visibility::Private,
                    'created_by_user_id' => $creator?->id,
                ],
            );
        }

        $this->seedMembers($users);
        $this->seedTeamVisibilityGrants($users);
    }

    /**
     * @param  Collection<int, User>  $users
     */
    protected function seedMembers(Collection $users): void
    {
        $teams = Team::query()->get();

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

    /**
     * @param  Collection<int, User>  $users
     */
    protected function seedTeamVisibilityGrants(Collection $users): void
    {
        Team::query()
            ->where('visibility', Visibility::Private)
            ->inRandomOrder()
            ->limit(4)
            ->get()
            ->each(fn (Team $team) => $this->maybeSeedVisibilityGrants($team, $users));
    }
}
