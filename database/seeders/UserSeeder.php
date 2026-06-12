<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    private const TARGET_USER_COUNT = 200;

    /**
     * Seed the application's users.
     */
    public function run(): void
    {
        if (! User::query()->where('email', 'admin@example.com')->exists()) {
            User::factory()->superAdmin()->create([
                'name' => 'Admin',
                'email' => 'admin@example.com',
            ]);
        } else {
            User::query()
                ->where('email', 'admin@example.com')
                ->update(['super_admin' => true]);
        }

        $usersToCreate = self::TARGET_USER_COUNT - User::query()->count();

        if ($usersToCreate > 0) {
            User::factory()->count($usersToCreate)->create();
        }

        $this->seedSupervisorHierarchy();
    }

    private function seedSupervisorHierarchy(): void
    {
        $admin = User::query()->where('email', 'admin@example.com')->first();

        if ($admin !== null && $admin->supervisor_id !== null) {
            $admin->forceFill(['supervisor_id' => null])->save();
        }

        $usersWithoutSupervisor = $this->usersWithoutSupervisor($admin);

        if ($usersWithoutSupervisor->isEmpty()) {
            return;
        }

        $supervisorIds = $this->existingSupervisorIds();
        $rootManagerIds = [];

        if ($supervisorIds === []) {
            ['supervisor_ids' => $supervisorIds, 'root_manager_ids' => $rootManagerIds] = $this->seedTopLevelManagers($usersWithoutSupervisor, $admin);
            $usersWithoutSupervisor = $this->usersWithoutSupervisor($admin);
        } else {
            $rootManagerIds = $usersWithoutSupervisor
                ->pluck('id')
                ->all();
        }

        if ($usersWithoutSupervisor->isEmpty() || $supervisorIds === []) {
            return;
        }

        $usersToAssign = $usersWithoutSupervisor->reject(
            fn (User $user): bool => in_array($user->id, $rootManagerIds, true),
        );

        if ($usersToAssign->isEmpty()) {
            return;
        }

        $this->assignSupervisors($usersToAssign, $supervisorIds);
    }

    /**
     * @return Collection<int, User>
     */
    private function usersWithoutSupervisor(?User $admin): Collection
    {
        return User::query()
            ->whereNull('supervisor_id')
            ->when($admin, fn ($query) => $query->whereKeyNot($admin->id))
            ->orderBy('id')
            ->get();
    }

    /**
     * @return list<int>
     */
    private function existingSupervisorIds(): array
    {
        return User::query()
            ->whereHas('subordinates')
            ->pluck('id')
            ->all();
    }

    /**
     * @param  Collection<int, User>  $usersWithoutSupervisor
     * @return array{supervisor_ids: list<int>, root_manager_ids: list<int>}
     */
    private function seedTopLevelManagers(Collection $usersWithoutSupervisor, ?User $admin): array
    {
        $topLevelCount = min(
            max(5, (int) floor($usersWithoutSupervisor->count() * 0.1)),
            $usersWithoutSupervisor->count(),
        );

        $topLevelManagers = $usersWithoutSupervisor->shuffle()->take($topLevelCount);

        if ($admin !== null && $topLevelManagers->isNotEmpty()) {
            $topLevelManagers
                ->take(min(3, $topLevelManagers->count()))
                ->each(function (User $manager) use ($admin): void {
                    $manager->forceFill([
                        'supervisor_id' => $admin->id,
                    ])->save();
                });
        }

        $rootManagerIds = $topLevelManagers
            ->filter(fn (User $manager): bool => $manager->supervisor_id === null)
            ->pluck('id')
            ->all();

        $supervisorIds = array_values(array_unique(array_filter([
            $admin?->id,
            ...$topLevelManagers->pluck('id')->all(),
        ])));

        return [
            'supervisor_ids' => $supervisorIds,
            'root_manager_ids' => $rootManagerIds,
        ];
    }

    /**
     * @param  Collection<int, User>  $users
     * @param  list<int>  $supervisorIds
     */
    private function assignSupervisors(Collection $users, array $supervisorIds): void
    {
        foreach ($users as $user) {
            $supervisorId = fake()->randomElement($supervisorIds);

            if ($supervisorId === $user->id) {
                $supervisorId = $supervisorIds[0];
            }

            $user->forceFill([
                'supervisor_id' => $supervisorId,
            ])->save();

            if (fake()->boolean(65)) {
                $supervisorIds[] = $user->id;
            }
        }
    }
}
