<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Throwable;

class TaskService
{
    public function getAllTasks(User $user, array $filters = [])
    {
        return Task::query()
            ->when(isset($filters['status']), fn($q) => $q->where('status', $filters['status']))
            ->when(isset($filters['due_date_from']), fn($q) => $q->where('due_date', '>=', $filters['due_date_from']))
            ->when(isset($filters['due_date_to']), fn($q) => $q->where('due_date', '<=', $filters['due_date_to']))
            ->when(isset($filters['assigned_to']), fn($q) => $q->where('assigned_to', $filters['assigned_to']))
            ->with(['creator', 'assignee', 'dependencies'])
            ->when(!$user->isManager(), fn($q) => $q->where('assigned_to', $user->id))
            ->get();
    }

    public function createTask(User $creator, array $data): Task
    {
        return DB::transaction(function () use ($creator, $data) {
            $task = Task::create([
                'title' => $data['title'],
                'description' => $data['description'],
                'due_date' => $data['due_date'],
                'assigned_to' => $data['assigned_to'],
                'created_by' => $creator->id,
            ]);

            if (!empty($data['dependencies'])) {
                $task->dependencies()->sync($data['dependencies']);
            }

            return $task->load('dependencies');
        });
    }

    public function updateTask(Task $task, User $user, array $data): Task
    {
        return DB::transaction(function () use ($task, $user, $data) {
            if ($user->isManager()) {
                $task->update($data);
                if (array_key_exists('dependencies', $data)) {
                    $task->dependencies()->sync($data['dependencies']);
                }
            } elseif (isset($data['status'])) {
                $task->update(['status' => $data['status']]);
            }

            return $task->load('dependencies');
        });
    }
}
