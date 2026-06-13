<?php

use App\Enums\TeamRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('teamables', function (Blueprint $table) {
            $table->string('role')->nullable()->after('team_id');
        });

        DB::table('teamables')
            ->where('teamable_type', 'user')
            ->update(['role' => TeamRole::Member->value]);

        $teamIds = DB::table('teamables')
            ->where('teamable_type', 'user')
            ->distinct()
            ->pluck('team_id');

        foreach ($teamIds as $teamId) {
            $memberId = DB::table('teamables')
                ->where('teamable_type', 'user')
                ->where('team_id', $teamId)
                ->orderBy('id')
                ->value('teamable_id');

            if ($memberId === null) {
                continue;
            }

            DB::table('teamables')
                ->where('team_id', $teamId)
                ->where('teamable_type', 'user')
                ->where('teamable_id', $memberId)
                ->update(['role' => TeamRole::Leader->value]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teamables', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
