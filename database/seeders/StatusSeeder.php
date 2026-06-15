<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'name' => 'Pending',
                'slug' => 'pending',
                'icon' => 'clock',
                'color' => 'gray',
                'sort_order' => 1,
            ],
            [
                'name' => 'In Progress',
                'slug' => 'in-progress',
                'icon' => 'play-circle',
                'color' => 'blue',
                'sort_order' => 2,
            ],
            [
                'name' => 'Stuck',
                'slug' => 'stuck',
                'icon' => 'exclamation-triangle',
                'color' => 'orange',
                'sort_order' => 3,
            ],
            [
                'name' => 'Completed',
                'slug' => 'completed',
                'icon' => 'check-circle',
                'color' => 'green',
                'sort_order' => 4,
                'is_closed' => true,
            ],
            [
                'name' => 'Cancelled',
                'slug' => 'cancelled',
                'icon' => 'x-circle',
                'color' => 'red',
                'sort_order' => 5,
                'is_closed' => true,
            ],
        ];

        foreach ($statuses as $status) {
            Status::query()->updateOrCreate(
                ['slug' => $status['slug']],
                $status,
            );
        }
    }
}
