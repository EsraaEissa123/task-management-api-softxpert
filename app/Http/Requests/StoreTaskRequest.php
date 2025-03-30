<?php

namespace App\Http\Requests;

use App\Models\Task;
use App\Http\Requests\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use App\Exceptions\UnauthorizedActionException;

class StoreTaskRequest extends ApiRequest
{
    public function authorize()
    {
        return $this->user() && $this->user()->isManager();
        // return $this->user()->isManager();
    }
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'dependencies' => 'nullable|array',
            'dependencies.*' => [
                'exists:tasks,id',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $dependency = Task::find($value);

                    if (!$dependency) {
                        $fail("Dependency {$value} does not exist.");
                        return;
                    }

                    if ($dependency->status === 'completed') {
                        $fail("Dependency {$value} is already completed and cannot be added.");
                    }
                }
            ],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var array<int>|null $dependencies */
            $dependencies = $this->validated('dependencies');

            if (!empty($dependencies)) {
                $this->validateNoCircularDependencies($validator, $dependencies);
            }
        });
    }

    /**
     * Validate no circular dependencies exist in the dependency tree.
     */
    protected function validateNoCircularDependencies(Validator $validator, array $dependencyIds): void
    {
        $visited = [];

        foreach ($dependencyIds as $dependencyId) {
            if (!$this->checkDependencyTree($dependencyId, $visited)) {
                $validator->errors()->add(
                    'dependencies',
                    "Circular dependency detected involving task {$dependencyId}"
                );
                return;
            }
        }
    }

    /**
     * Recursively check the dependency tree for circular references.
     */
    protected function checkDependencyTree(int $taskId, array &$visited): bool
    {
        if (in_array($taskId, $visited, true)) {
            return false; // Circular dependency detected
        }

        $visited[] = $taskId;
        $task = Task::with('dependencies')->find($taskId);

        if (!$task) {
            return true;
        }

        foreach ($task->dependencies as $dependency) {
            if (!$this->checkDependencyTree($dependency->id, $visited)) {
                return false;
            }
        }

        return true;
    }
    protected function failedAuthorization()
    {
        throw new UnauthorizedActionException;
    }
}
