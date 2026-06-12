<?php

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
        DB::table('projects')->update([
            'visibility' => 'private',
        ]);

        DB::table('projects')
            ->where('ownership', 'user')
            ->whereNotNull('owner_user_id')
            ->update([
                'created_by_user_id' => DB::raw('owner_user_id'),
            ]);

        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['ownership']);
            $table->dropColumn('ownership');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('ownership')->default('team')->after('due_date');
            $table->index('ownership');
        });

        DB::table('projects')
            ->whereNotNull('owner_user_id')
            ->update(['ownership' => 'user']);

        DB::table('projects')
            ->whereNull('owner_user_id')
            ->update(['ownership' => 'team']);
    }
};
