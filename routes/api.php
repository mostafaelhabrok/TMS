<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;

// use App\Http\Middleware\CheckRole;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// auth middleware group
Route::middleware(['auth:sanctum'])->group(function () {
    // Group of routes for TaskController
    Route::controller(TaskController::class)->group(function () {

        Route::get('/tasks', 'showAll');                                            // get tasks

        // middleware group for task permission
        Route::middleware(['taskPerm'])->group(function () {
            Route::post('/tasks/{id}/status', 'updateStatus');                      // update task status
            Route::get('/tasks/{id}', 'showOne');                                   // get specific task
        });

        // middleware group for manger role
        Route::middleware(['role:manager'])->group(function () {
            Route::post('/tasks', 'insert');                                        // create task
            Route::put('/tasks/{id}', 'update');                                    // update task
            Route::post('/tasks/dependency', 'addDependency');                      // add task dependency
        });

        Route::get('/task_not_found',  'notFound')->name('notFound');
    });
});


// Group of routes for AuthController
Route::controller(AuthController::class)->group(function () {
    Route::post('/authenticate',  'authenticate');                                  // create new token
    Route::get('/unauthenticated',  'unauthenticated')->name('unauthenticated');    // show unauthenticated message
    Route::get('/unauthorized',  'unauthorized')->name('unauthorized');             // show unauthorized message
});

Route::post('/users',[UserController::class, 'insert']);                             // create new user



