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
        Schema::create('records', function (Blueprint $table) {
            $table->id();
            $table->morphs('recordable');
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->string('visibility')->default('private');
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->nullableMorphs('owner');
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->unique(['recordable_type', 'recordable_id']);
            $table->index(['visibility', 'due_date', 'id']);
            $table->index('created_by_user_id');
        });

        $this->backfillRecordRows();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('records');
    }

    private function backfillRecordRows(): void
    {
        foreach ($this->recordableSources() as $source) {
            DB::table($source['table'])
                ->orderBy('id')
                ->lazyById()
                ->each(function (object $recordable) use ($source): void {
                    DB::table('records')->insert([
                        'recordable_type' => $source['type'],
                        'recordable_id' => $recordable->id,
                        'title' => $recordable->{$source['title']},
                        'description' => property_exists($recordable, 'description') ? $recordable->description : null,
                        'start_date' => property_exists($recordable, 'start_date') ? $recordable->start_date : null,
                        'due_date' => property_exists($recordable, 'due_date') ? $recordable->due_date : null,
                        'visibility' => property_exists($recordable, 'visibility') ? $recordable->visibility : 'private',
                        'created_by_user_id' => property_exists($recordable, 'created_by_user_id') ? $recordable->created_by_user_id : null,
                        'owner_type' => $source['owner_type']($recordable),
                        'owner_id' => $source['owner_id']($recordable),
                        'created_at' => $recordable->created_at,
                        'updated_at' => $recordable->updated_at,
                    ]);
                });
        }
    }

    /**
     * @return list<array{table: string, type: string, title: string, owner_type: Closure(object): ?string, owner_id: Closure(object): ?int}>
     */
    private function recordableSources(): array
    {
        return [
            [
                'table' => 'tasks',
                'type' => 'task',
                'title' => 'title',
                'owner_type' => fn (object $recordable): ?string => $recordable->owner_type,
                'owner_id' => fn (object $recordable): ?int => $recordable->owner_id,
            ],
            [
                'table' => 'projects',
                'type' => 'project',
                'title' => 'title',
                'owner_type' => fn (object $recordable): ?string => $recordable->owner_user_id === null ? null : 'user',
                'owner_id' => fn (object $recordable): ?int => $recordable->owner_user_id,
            ],
            [
                'table' => 'checklists',
                'type' => 'checklist',
                'title' => 'title',
                'owner_type' => fn (object $recordable): ?string => $recordable->owner_type,
                'owner_id' => fn (object $recordable): ?int => $recordable->owner_id,
            ],
            [
                'table' => 'teams',
                'type' => 'team',
                'title' => 'name',
                'owner_type' => fn (): ?string => null,
                'owner_id' => fn (): ?int => null,
            ],
        ];
    }
};
