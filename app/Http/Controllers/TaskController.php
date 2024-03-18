<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\TaskDependency;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    /**
     * get all tasks based on filters
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function showAll(Request $request)
    {

        // Validate incoming request
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|nullable|string|max:255',
            'description' => 'sometimes|nullable|string',
            'user_id' => 'sometimes|nullable|integer|exists:users,id',
            'due_date_from' => 'sometimes|nullable|date',
            'due_date_to' => 'sometimes|nullable|date',
            'status' => 'sometimes|nullable|in:pending,completed,canceled'
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            // Return validation errors
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $query = Task::query();

        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->input('title') . '%');
        }

        if ($request->filled('description')) {
            $query->where('description', 'like', '%' . $request->input('description') . '%');
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('due_date_from')) {
            $query->where('due_date', '>=', $request->input('due_date_from'));
        }

        if ($request->filled('due_date_to')) {
            $query->where('due_date', '<=', $request->input('due_date_to'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // id role is user get only the tasks assigned to that user
        if ($request->user()->role === 'user') {
            $query->where('user_id', $request->user()->id);
        }

        $tasks = $query->get();

        return response()->json($tasks, 200);
    }

    /**
     * get one task based on id with its dependencies
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function showOne(Request $request)
    {
        // get task from request as it was addend in the middle ware
        // no need for check if task exists as already checked in the middleware
        $task = $request->input('task');

        // no need for check if task exists as already checked in the middleware

        // get task dependencies
        $dependencies = Task::join('task_dependencies', 'tasks.id', '=', 'task_dependencies.dependency_id')
            ->where('task_dependencies.task_id', $task->id)
            ->select('tasks.*')
            ->get();

        return response()->json(['task' => $task, 'dependencies' => $dependencies], 200);
    }

    /**
     * insert new task
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function insert(Request $request)
    {
        // Validate incoming request
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'user_id' => 'required|integer|exists:users,id',
            'due_date' => 'required|date',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            // Return validation errors
            return response()->json(['errors' => $validator->errors()], 400);
        }

        try {
            // Create new task instance
            $task = new Task;
            $task->title = $request->input('title');
            $task->description = $request->input('description');
            $task->user_id = $request->input('user_id');
            $task->due_date = $request->input('due_date');
            $task->status = 'pending'; // set any new task to pending

            // Save the task
            $task->save();

            // return success message with task instance
            return response()->json(['message' => 'Task created successfully', 'task' => $task], 201);
        } catch (\Exception $e) {
            // return any error
            return response()->json(['errors' => $e], 500);
        }
    }

    /**
     * update existing task with id
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Validate incoming request
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|nullable|string|max:255',
            'description' => 'sometimes|nullable|string',
            'user_id' => 'sometimes|nullable|integer|exists:users,id',
            'due_date' => 'sometimes|nullable|date',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            // Return validation errors
            return response()->json(['errors' => $validator->errors()], 400);
        }

        try {
            // Find task by id
            $task = Task::find($id);

            if (!$task) {
                // Task not found
                return TaskController::notFound();
            }

            if ($request->filled('title')) {
                $task->title = $request->input('title');
            }

            if ($request->has('description')) {
                $task->description = $request->input('description');
            }

            if ($request->filled('user_id')) {
                $task->user_id = $request->input('user_id');
            }

            if ($request->filled('due_date')) {
                $task->due_date = $request->input('due_date');
            }

            // Save the task
            $task->save();

            // return success message with task instance
            return response()->json(['message' => 'Task updated successfully', 'task' => $task], 200);
        } catch (\Exception $e) {
            // return any error
            return response()->json(['errors' => $e], 500);
        }
    }

    /**
     * add task dependencies
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addDependency(Request $request)
    {
        $task_id = $request->input('task_id');
        $dependency_ids = $request->input('dependency_id');

        // Validate incoming request
        $validator = Validator::make($request->all(), [
            'task_id' => 'required|integer|exists:tasks,id',
            'dependency_id' => 'required|array',
            'dependency_id.*' => [
                'exists:tasks,id',
                Rule::notIn([$task_id]), // make sure task id not in dependencies
            ]
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            // Return validation errors
            return response()->json(['errors' => $validator->errors()], 400);
        }


        // Check if the reverse dependency already exists
        // can't have 1 depend on 2 and 2 depend on 1
        foreach ($dependency_ids as $dependency_id) {
            if (TaskDependency::where('task_id', $dependency_id)->where('dependency_id', $task_id)->exists()) {
                return response()->json(['errors' => 'Task: ' . $dependency_id . ' already has dependency on task: ' . $task_id], 400);
            }
        }

        try {
            $dependencies = array();
            foreach ($dependency_ids as $dependency_id) {
                // Create new task dependency instance
                $task_dependency = new TaskDependency;
                $task_dependency->task_id = $task_id;
                $task_dependency->dependency_id = $dependency_id;

                // Save the dependency
                $task_dependency->save();
                $dependencies[] = $task_dependency;
            }

            // return success message with task instance
            return response()->json(['message' => 'Task dependency added successfully', 'task_dependencies' => $dependencies], 200);
        } catch (\Exception $e) {
            // return any error
            return response()->json(['errors' => $e], 500);
        }
    }

    /**
     * update task status
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request)
    {
        // Validate incoming request
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,completed,canceled'
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            // Return validation errors
            return response()->json(['errors' => $validator->errors()], 400);
        }

        try {

            // get task from request as it was addend in the middle ware
            // no need for check if task exists as already checked in the middleware
            $task = $request->input('task');

            $task->status = $request->input('status');

            // Save the task
            $task->save();

            // return success message with task instance
            return response()->json(['message' => 'Task status updated successfully', 'task' => $task], 200);
        } catch (\Exception $e) {
            // return any error
            return response()->json(['errors' => $e], 500);
        }
    }

    /**
     * Show message to user if task not found.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function notFound()
    {
        return response()->json(['errors' => 'Task not found'], 404);
    }
}
