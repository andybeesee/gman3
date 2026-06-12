<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->string('visibility')->default('private')->after('slug');
            $table->foreignId('created_by_user_id')->nullable()->after('visibility')->constrained('users')->nullOnDelete();

            $table->index('visibility');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->string('visibility')->default('private')->after('due_date');
            $table->foreignId('created_by_user_id')->nullable()->after('visibility')->constrained('users')->nullOnDelete();

            $table->index('visibility');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->string('visibility')->default('private')->after('due_date');
            $table->foreignId('created_by_user_id')->nullable()->after('visibility')->constrained('users')->nullOnDelete();

            $table->index('visibility');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by_user_id');
            $table->dropIndex(['visibility']);
            $table->dropColumn('visibility');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by_user_id');
            $table->dropIndex(['visibility']);
            $table->dropColumn('visibility');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by_user_id');
            $table->dropIndex(['visibility']);
            $table->dropColumn('visibility');
        });
    }
};
