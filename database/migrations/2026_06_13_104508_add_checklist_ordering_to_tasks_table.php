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
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('checklist_id')->nullable()->after('owner_id')->constrained()->nullOnDelete();
            $table->unsignedInteger('checklist_position')->nullable()->after('checklist_id');

            $table->unique(['checklist_id', 'checklist_position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['checklist_id']);
            $table->dropUnique(['checklist_id', 'checklist_position']);
            $table->dropColumn(['checklist_id', 'checklist_position']);
        });
    }
};
