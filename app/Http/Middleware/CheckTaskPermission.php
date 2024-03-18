<?php

namespace App\Http\Middleware;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Task;

class CheckTaskPermission
{
    /**
     * Check if user has permission over task (if he has manager role or the task is assigned to him)
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $task_id = $request->route('id');

        // get the task
        $task = Task::find($task_id);

        if (!$task) {
            // Task not found
            return TaskController::notFound();
        }

        // get the user's role
        $role = $request->user()->role;

        // the role is manager or user that tries to update status of his assigned task
        if ($role === 'manager' || $request->user()->id === $task->user_id) {
            $request->merge(['task' => $task]);
            return $next($request);
        }

        return AuthController::unauthorized();
    }
}
