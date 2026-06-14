<?php

namespace App\Http\Requests;

use App\Enums\Visibility;
use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Task::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'visibility' => ['required', Rule::enum(Visibility::class)],
            'owner_type' => ['required', Rule::in(['user', 'team', 'project'])],
            'owner_team_id' => [
                Rule::requiredIf(fn (): bool => $this->string('owner_type')->toString() === 'team'),
                'nullable',
                'integer',
            ],
            'owner_project_id' => [
                Rule::requiredIf(fn (): bool => $this->string('owner_type')->toString() === 'project'),
                'nullable',
                'integer',
            ],
            'assignee_ids' => ['nullable', 'array'],
            'assignee_ids.*' => ['integer', 'exists:users,id'],
            'team_ids' => ['nullable', 'array'],
            'team_ids.*' => ['integer', 'exists:teams,id'],
        ];
    }

    /**
     * @return list<callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->selectedTeamOwnerIsHidden()) {
                    $validator->errors()->add('owner_team_id', __('Choose a team you can access.'));
                }

                if ($this->selectedProjectOwnerIsHidden()) {
                    $validator->errors()->add('owner_project_id', __('Choose a project you can access.'));
                }

                if ($this->containsHiddenTeams()) {
                    $validator->errors()->add('team_ids', __('Choose teams you can access.'));
                }
            },
        ];
    }

    protected function selectedTeamOwnerIsHidden(): bool
    {
        if ($this->string('owner_type')->toString() !== 'team') {
            return false;
        }

        $ownerTeamId = $this->integer('owner_team_id');

        if ($ownerTeamId === 0) {
            return true;
        }

        return ! Team::query()->visibleTo($this->user())->whereKey($ownerTeamId)->exists();
    }

    protected function selectedProjectOwnerIsHidden(): bool
    {
        if ($this->string('owner_type')->toString() !== 'project') {
            return false;
        }

        $ownerProjectId = $this->integer('owner_project_id');

        if ($ownerProjectId === 0) {
            return true;
        }

        return ! Project::query()->visibleTo($this->user())->whereKey($ownerProjectId)->exists();
    }

    protected function containsHiddenTeams(): bool
    {
        $teamIds = collect($this->input('team_ids', []))
            ->filter()
            ->unique()
            ->values();

        if ($teamIds->isEmpty()) {
            return false;
        }

        $visibleTeamCount = Team::query()
            ->visibleTo($this->user())
            ->whereKey($teamIds)
            ->count();

        return $visibleTeamCount !== $teamIds->count();
    }
}
