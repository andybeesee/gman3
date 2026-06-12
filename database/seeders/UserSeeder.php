<?php

namespace Database\Seeders;

use App\Models\User;
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
    }
}
