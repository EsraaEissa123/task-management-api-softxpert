<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

class TaskPolicy
{
    public function view(User $user, Task $task): bool
    {
        return $user->isManager() || $task->assigned_to === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isManager();
    }

    public function update(User $user, Task $task): bool
    {
        if ($user->isManager()) {
            return true;
        }

        return $task->assigned_to === $user->id && $this->onlyUpdatesStatus(request());
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->isManager();
    }

    protected function onlyUpdatesStatus(Request $request): bool
    {
        $input = $request->all();
        return count($input) == 1 && array_key_exists('status', $input);
    }
}
