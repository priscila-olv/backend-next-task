<?php

use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\TasksController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\PrioritiesController;
use App\Http\Controllers\ProjectsController;
use App\Http\Controllers\SectionsController;
use App\Http\Controllers\AuthController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


//Login Google
Route::get('/auth/google', [GoogleAuthController::class, 'redirectToGoogle']);
Route::get('/auth/callback', [GoogleAuthController::class, 'googleAuth']);

//PasswordReset
Route::post('/auth/send-email-confirmation', [AuthController::class, 'sendEmailConfirmation']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

//Login
Route::post('/auth/login', [AuthController::class, 'generateToken']);

//Create user
Route::post('/users', [UsersController::class, 'store']);

Route::middleware(['jwt.auth'])->group(function () {
//Usuario
Route::get('/users', [UsersController::class, 'index']);
Route::put('/users/{user}', [UsersController::class, 'update']);
Route::delete('/users/{user}', [UsersController::class, 'remove']);
Route::put('/users/reset-password/{user}', [UsersController::class, 'resetPassword']);

//Auth
Route::get('/auth/is-auth', [AuthController::class, 'isAuth']);
Route::post('/logout', [AuthController::class, 'logout']);

//Priority
Route::get('/priorities', [PrioritiesController::class, 'index']);
Route::post('/priorities', [PrioritiesController::class, 'store']);
Route::delete('/priorities/{priority}', [PrioritiesController::class, 'remove']);

//Project
Route::get('/projects', [ProjectsController::class, 'index']);
Route::post('/projects', [ProjectsController::class, 'store']);
Route::delete('/projects/{project}', [ProjectsController::class, 'remove']);
Route::patch('/projects/{project}', [ProjectsController::class, 'update']);
Route::post('/projects/invite-email', [ProjectsController::class, 'inviteUserToProject']);
Route::post('projects/shared',[ProjectsController::class, 'storeShared']);
Route::post('projects/shared/{project}/refresh-token',[ProjectsController::class, 'refreshTokenProject']);
Route::post('projects/participate', [ProjectsController::class, 'participateProject']);

//Section
Route::get('/sections', [SectionsController::class, 'index']);
Route::post('/sections', [SectionsController::class, 'store']);
Route::delete('/sections/{section}', [SectionsController::class, 'remove']);
Route::get('projects/{project}/sections', [SectionsController::class, 'getAllProjectSections']);
Route::put('/sections/{section}', [SectionsController::class, 'update']);

//Task
  Route::get('/tasks', [TasksController::class, 'index']);
  Route::post('/tasks', [TasksController::class, 'store']);
  Route::delete('/tasks/{task}', [TasksController::class, 'remove']);
  Route::get('/tasks/{task}', [TasksController::class, 'getByIdTask']);
  Route::get('/sections/{section}/tasks', [TasksController::class, 'getByIdSection']);
  Route::patch('/tasks/{task}', [TasksController::class, 'update']);
});




