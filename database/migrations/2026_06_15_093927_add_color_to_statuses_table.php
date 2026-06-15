<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('statuses', function (Blueprint $table) {
            $table->string('color')->default('gray')->after('icon');
        });

        // Backfill known seeded colors to their slug equivalents
        $map = [
            '#6b7280' => 'gray',
            '#2563eb' => 'blue',
            '#ea580c' => 'orange',
            '#16a34a' => 'green',
            '#dc2626' => 'red',
        ];

        foreach ($map as $hex => $slug) {
            DB::table('statuses')
                ->where('light_theme_color', $hex)
                ->update(['color' => $slug]);
        }

        Schema::table('statuses', function (Blueprint $table) {
            $table->dropColumn(['light_theme_color', 'dark_theme_color']);
        });
    }

    public function down(): void
    {
        Schema::table('statuses', function (Blueprint $table) {
            $table->string('light_theme_color')->after('icon');
            $table->string('dark_theme_color')->after('light_theme_color');
        });

        Schema::table('statuses', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }
};
