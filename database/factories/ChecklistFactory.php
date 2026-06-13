<?php

namespace Database\Factories;

use App\Enums\Visibility;
use App\Models\Checklist;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Checklist>
 */
class ChecklistFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => SeedNames::checklistTitle(),
            'description' => SeedNames::optionalDescription(),
            'visibility' => fake()->boolean(20) ? Visibility::Public : Visibility::Private,
            'created_by_user_id' => null,
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Checklist $checklist): void {
            if ($checklist->owner_type !== null) {
                $this->assignCreator($checklist);

                return;
            }

            if (fake()->boolean(70)) {
                $teams = Team::query()->inRandomOrder()->limit(fake()->numberBetween(1, 2))->get();

                if ($teams->isNotEmpty()) {
                    $checklist->syncTeams($teams);
                    $checklist->setOwner($teams->first());
                    $this->assignCreator($checklist);

                    return;
                }
            }

            $owner = User::query()->inRandomOrder()->first();

            if ($owner !== null) {
                $checklist->setOwner($owner);
            }

            $this->assignCreator($checklist);
        });
    }

    /**
     * @return $this
     */
    public function ownedBy(User|Team|Project $owner): static
    {
        return $this->afterCreating(function (Checklist $checklist) use ($owner): void {
            $checklist->setOwner($owner);

            if ($owner instanceof User) {
                $checklist->forceFill(['created_by_user_id' => $owner->id])->save();

                return;
            }

            if ($owner instanceof Project) {
                $checklist->syncTeams($owner->teams);
                $checklist->forceFill([
                    'visibility' => fake()->boolean(90) ? $owner->visibility : $checklist->visibility,
                    'created_by_user_id' => $projectCreatorId = $owner->created_by_user_id,
                ])->save();

                if ($projectCreatorId !== null) {
                    return;
                }
            }

            $this->assignCreator($checklist);
        });
    }

    protected function assignCreator(Checklist $checklist): void
    {
        if ($checklist->created_by_user_id !== null) {
            return;
        }

        $creatorId = match ($checklist->owner_type) {
            'user' => $checklist->owner_id,
            'team' => $checklist->teams()->first()?->members()->value('users.id'),
            'project' => Project::query()->whereKey($checklist->owner_id)->value('created_by_user_id'),
            default => null,
        };

        $creatorId ??= User::query()->inRandomOrder()->value('id');

        if ($creatorId !== null) {
            $checklist->forceFill(['created_by_user_id' => $creatorId])->save();
        }
    }
}
