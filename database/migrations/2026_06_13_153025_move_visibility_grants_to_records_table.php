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
        Schema::table('visibility_grants', function (Blueprint $table) {
            $table->foreignId('record_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->unique(['record_id', 'grantee_type', 'grantee_id'], 'visibility_grants_record_grantee_unique');
        });

        DB::table('visibility_grants')
            ->join('records', function ($join): void {
                $join->on('records.recordable_type', '=', 'visibility_grants.grantable_type')
                    ->on('records.recordable_id', '=', 'visibility_grants.grantable_id');
            })
            ->select('visibility_grants.id', 'records.id as record_id')
            ->orderBy('visibility_grants.id')
            ->get()
            ->each(function (object $visibilityGrant): void {
                DB::table('visibility_grants')
                    ->where('id', $visibilityGrant->id)
                    ->update(['record_id' => $visibilityGrant->record_id]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visibility_grants', function (Blueprint $table) {
            $table->dropUnique('visibility_grants_record_grantee_unique');
            $table->dropConstrainedForeignId('record_id');
        });
    }
};
