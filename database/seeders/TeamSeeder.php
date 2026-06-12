<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teams = [
            'Engineering',
            'Design',
            'Product',
            'Marketing',
            'Support',
            'Operations',
            'Finance',
            'Legal',
        ];

        foreach ($teams as $name) {
            Team::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name],
            );
        }

        Team::factory()->count(4)->create();
    }
}
