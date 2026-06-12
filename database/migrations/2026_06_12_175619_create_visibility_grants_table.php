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
        Schema::create('visibility_grants', function (Blueprint $table) {
            $table->id();
            $table->morphs('grantable');
            $table->morphs('grantee');
            $table->timestamps();

            $table->unique([
                'grantable_type',
                'grantable_id',
                'grantee_type',
                'grantee_id',
            ], 'visibility_grants_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visibility_grants');
    }
};
