<?php

use App\Models\Task;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * @var array<string, string>
     */
    private array $morphColumns = [
        'assignables' => 'assignable_type',
        'date_changes' => 'dateable_type',
        'status_changes' => 'statusable_type',
        'teamables' => 'teamable_type',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->morphColumns as $table => $column) {
            DB::table($table)
                ->where($column, Task::class)
                ->update([$column => 'task']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->morphColumns as $table => $column) {
            DB::table($table)
                ->where($column, 'task')
                ->update([$column => Task::class]);
        }
    }
};
