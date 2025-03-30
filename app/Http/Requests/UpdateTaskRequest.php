<?php

namespace App\Http\Requests;

use App\Models\Task;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;

class UpdateTaskRequest extends ApiRequest
{
    protected function prepareForValidation()
    {
        $this->merge(['task' => $this->route('task')]);
    }

    public function authorize(): bool
    {
        return $this->user()->isManager() ||
            $this->task->assigned_to === $this->user()->id;
    }

    public function rules(): array
    {
        $rules = [
            'status' => ['sometimes', Rule::in(['pending', 'completed', 'canceled'])]
        ];

        if ($this->user()->isManager()) {
            $rules = array_merge($rules, [
                'title' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'due_date' => 'nullable|date|after_or_equal:today',
                'assigned_to' => 'nullable|exists:users,id',
                'dependencies' => 'nullable|array',
                'dependencies.*' => [
                    'exists:tasks,id',
                    function ($attribute, $value, $fail) {
                        if ($value == $this->task->id) {
                            $fail('A task cannot depend on itself.');
                        }
                    }
                ],
            ]);
        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $user = $this->user();

            // Only check explicitly provided input, not merged route parameters
            $input = $this->except('task');

            if (!$user->isManager()) {
                $invalidFields = array_diff(array_keys($input), ['status']);

                if (!empty($invalidFields)) {
                    $validator->errors()->add(
                        'unauthorized',
                        'You can only update the task status. Invalid fields: ' . implode(', ', $invalidFields)
                    );
                }
            }

            if (isset($input['status']) && $input['status'] === 'completed' && !$this->task->canBeCompleted()) {
                $validator->errors()->add(
                    'status',
                    'Cannot complete task. All dependencies must be completed first.'
                );
            }
        });
    }
}
