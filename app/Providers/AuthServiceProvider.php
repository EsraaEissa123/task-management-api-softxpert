<?php

namespace App\Providers;

use App\Models\Task;
use App\Models\User;
use App\Policies\TaskPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Task::class => TaskPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('update-task', function (User $user, Task $task) {
            return $user->isManager() ||
                  ($task->assigned_to === $user->id && $this->onlyUpdatesStatus(request()));
        });

        Gate::define('assign-task', function (User $user) {
            return $user->isManager();
        });
    }

    protected function onlyUpdatesStatus($request): bool
    {
        return count($request->all()) === 1 && $request->has('status');
    }
}
