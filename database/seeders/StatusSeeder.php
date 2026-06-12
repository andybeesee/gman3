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
                'light_theme_color' => '#6b7280',
                'dark_theme_color' => '#9ca3af',
                'sort_order' => 1,
            ],
            [
                'name' => 'In Progress',
                'slug' => 'in-progress',
                'icon' => 'play-circle',
                'light_theme_color' => '#2563eb',
                'dark_theme_color' => '#60a5fa',
                'sort_order' => 2,
            ],
            [
                'name' => 'Stuck',
                'slug' => 'stuck',
                'icon' => 'exclamation-triangle',
                'light_theme_color' => '#ea580c',
                'dark_theme_color' => '#fb923c',
                'sort_order' => 3,
            ],
            [
                'name' => 'Completed',
                'slug' => 'completed',
                'icon' => 'check-circle',
                'light_theme_color' => '#16a34a',
                'dark_theme_color' => '#4ade80',
                'sort_order' => 4,
            ],
            [
                'name' => 'Cancelled',
                'slug' => 'cancelled',
                'icon' => 'x-circle',
                'light_theme_color' => '#dc2626',
                'dark_theme_color' => '#f87171',
                'sort_order' => 5,
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
