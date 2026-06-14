<?php

namespace App\Queries;

use App\Models\Checklist;
use App\Models\Project;
use App\Models\Record;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RecordableQuery
{
    /**
     * @return Builder<Record>
     */
    public static function visibleTo(User $user): Builder
    {
        return Record::query()->visibleTo($user);
    }

    /**
     * @return Builder<Task>
     */
    public static function tasksVisibleTo(User $user): Builder
    {
        return Task::query()->whereIn('id', self::recordableIdsVisibleTo($user, Task::class));
    }

    /**
     * @return Builder<Project>
     */
    public static function projectsVisibleTo(User $user): Builder
    {
        return Project::query()->whereIn('id', self::recordableIdsVisibleTo($user, Project::class));
    }

    /**
     * @return Builder<Checklist>
     */
    public static function checklistsVisibleTo(User $user): Builder
    {
        return Checklist::query()->whereIn('id', self::recordableIdsVisibleTo($user, Checklist::class));
    }

    /**
     * @param  class-string<Model>  $recordableClass
     * @return Builder<Record>
     */
    public static function recordableIdsVisibleTo(User $user, string $recordableClass): Builder
    {
        return self::visibleTo($user)
            ->where('recordable_type', self::aliasFor($recordableClass))
            ->select('recordable_id');
    }

    /**
     * @param  class-string<Model>  $recordableClass
     */
    protected static function aliasFor(string $recordableClass): string
    {
        return (new $recordableClass)->getMorphClass();
    }
}
