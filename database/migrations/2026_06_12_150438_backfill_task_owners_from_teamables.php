<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (DB::table('tasks')->orderBy('id')->lazyById() as $task) {
            if ($task->owner_type !== null) {
                continue;
            }

            $teamOwner = DB::table('teamables')
                ->where('teamable_type', 'task')
                ->where('teamable_id', $task->id)
                ->orderBy('id')
                ->first();

            if ($teamOwner !== null) {
                DB::table('tasks')
                    ->where('id', $task->id)
                    ->update([
                        'owner_type' => 'team',
                        'owner_id' => $teamOwner->team_id,
                    ]);

                continue;
            }

            $userOwner = DB::table('assignables')
                ->where('assignable_type', 'task')
                ->where('assignable_id', $task->id)
                ->orderBy('id')
                ->first();

            if ($userOwner !== null) {
                DB::table('tasks')
                    ->where('id', $task->id)
                    ->update([
                        'owner_type' => 'user',
                        'owner_id' => $userOwner->user_id,
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('tasks')->update([
            'owner_type' => null,
            'owner_id' => null,
        ]);
    }
};
