<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
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

// 认证路由
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'user']);
    });
});

// 受保护的路由
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // 原典法系统路由
    Route::prefix('offline')->group(function () {
        // 学生管理
        Route::apiResource('students', \App\Http\Controllers\StudentController::class);
        
        // 课程管理
        Route::apiResource('courses', \App\Http\Controllers\CourseController::class);
        
        // 课时记录
        Route::apiResource('lessons', \App\Http\Controllers\LessonController::class);
    });
    
    // 线上课程系统路由
    Route::prefix('online')->group(function () {
        // 线上课程
        Route::apiResource('courses', \App\Http\Controllers\OnlineCourseController::class);
        
        // 订单管理
        Route::apiResource('orders', \App\Http\Controllers\OrderController::class);
        
        // 分销商管理
        Route::apiResource('distributors', \App\Http\Controllers\DistributorController::class);
    });
});
