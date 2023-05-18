<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\PrioritiesController;
use App\Http\Controllers\ProjectsController;
use App\Http\Controllers\SectionsController;
use App\Http\Controllers\AuthController;



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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


//Login
Route::post('/auth/login', [AuthController::class, 'generateToken']);

//Create user
Route::post('/users', [UsersController::class, 'store']);


Route::middleware(['jwt.auth'])->group(function () {
//Usuario
Route::get('/users', [UsersController::class, 'index']);
Route::get('/users/{user}', [UsersController::class, 'find']);
Route::put('/users/{user}', [UsersController::class, 'update']);
Route::delete('/users/{user}', [UsersController::class, 'remove']);
Route::put('/users/reset-password/{user}', [UsersController::class, 'resetPassword']);
Route::get('/users/count-logged', [UsersController::class, 'getLoggedUsers']);

//Auth
Route::get('/auth/is-auth', [AuthController::class, 'isAuth']);

//Priority
Route::get('/priorities', [PrioritiesController::class, 'index']);
Route::post('/priorities', [PrioritiesController::class, 'store']);
Route::delete('/priorities/{priority}', [PrioritiesController::class, 'remove']);

//Project
Route::get('/projects', [ProjectsController::class, 'index']);
Route::post('/projects', [ProjectsController::class, 'store']);
Route::delete('/projects/{project}', [ProjectsController::class, 'remove']);

//Section
Route::get('/sections', [SectionsController::class, 'index']);
Route::post('/sections', [SectionsController::class, 'store']);
Route::delete('/sections/{section}', [SectionsController::class, 'remove']);
Route::get('/{project}/sections', [SectionsController::class, 'getAllUserSections']);

});




