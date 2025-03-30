<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private TaskService $taskService) {}

    public function index(): JsonResponse
    {
        try {
            $tasks = $this->taskService->getAllTasks(
                request()->user(),
                request()->only(['status', 'due_date_from', 'due_date_to', 'assigned_to'])
            );
            return response()->json($tasks);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Error fetching tasks');
        }
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        try {
            $task = $this->taskService->createTask(
                $request->user(),
                $request->validated()
            );
            return response()->json($task, 201);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Error creating task');
        }
    }

    public function show(Task $task): JsonResponse
    {
        try {
            $this->authorize('view', $task);
            return response()->json($task->load('dependencies'));
        } catch (Throwable $e) {
            return $this->handleException($e, 'Error retrieving task');
        }
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        try {
            $updatedTask = $this->taskService->updateTask(
                $task,
                $request->user(),
                $request->validated()
            );
            return response()->json($updatedTask);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Error updating task');
        }
    }

    /**
     * Centralized error handling method
     */
    private function handleException(Throwable $e, string $contextMessage): JsonResponse
    {
        Log::error($contextMessage, [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return match (true) {
            $e instanceof AuthorizationException => response()->json([
                'message' => 'Unauthorized access.',
                'error' => $e->getMessage(),
            ], 403),

            $e instanceof ModelNotFoundException => response()->json([
                'message' => 'Resource not found.',
                'error' => $e->getMessage(),
            ], 404),

            default => response()->json([
                'message' => 'An unexpected error occurred.',
                'error' => $e->getMessage(),
            ], 500),
        };
    }
}
