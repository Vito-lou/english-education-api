<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\Admin\InstitutionController;
use App\Http\Controllers\Api\Admin\DepartmentController;
use App\Http\Controllers\Api\Admin\OrganizationController;

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
    Route::post('login', [AuthController::class, 'login'])->name('login');
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

    // 用户相关接口
    Route::get('/user/permissions', [\App\Http\Controllers\Api\UserController::class, 'permissions']);
    Route::get('/user/profile', [\App\Http\Controllers\Api\UserController::class, 'profile']);
});

// 管理后台API (english-education-frontend) - 需要认证
Route::middleware('auth:sanctum')->prefix('admin')->group(function () {

        // 角色权限管理
        Route::apiResource('roles', \App\Http\Controllers\Api\Admin\RoleController::class);
        Route::get('permissions', [\App\Http\Controllers\Api\Admin\PermissionController::class, 'index']);
        Route::get('permissions/data', [\App\Http\Controllers\Api\Admin\PermissionController::class, 'dataPermissions']);
        Route::get('permissions/all', [\App\Http\Controllers\Api\Admin\PermissionController::class, 'all']);
        Route::get('permissions/menu', [\App\Http\Controllers\Api\Admin\PermissionController::class, 'menuPermissions']);

        // 系统菜单管理
        Route::apiResource('system-menus', \App\Http\Controllers\Api\Admin\SystemMenuController::class);
        Route::get('system-menus-list', [\App\Http\Controllers\Api\Admin\SystemMenuController::class, 'list']);

        // 机构管理
        Route::apiResource('institutions', InstitutionController::class);
        Route::get('institutions/{institution}/statistics', [InstitutionController::class, 'statistics']);

        // 部门管理 - 自定义路由必须在apiResource之前
        Route::get('departments/tree', [DepartmentController::class, 'tree']);

        Route::apiResource('departments', DepartmentController::class);

        // 组织架构统一管理
        Route::prefix('organization')->group(function () {
            Route::get('tree', [OrganizationController::class, 'tree']);
            Route::post('nodes', [OrganizationController::class, 'createNode']);
            Route::put('nodes/{id}', [OrganizationController::class, 'updateNode']);
            Route::delete('nodes/{id}', [OrganizationController::class, 'deleteNode']);
            Route::put('nodes/{id}/move', [OrganizationController::class, 'moveNode']);
        });







        // 用户管理
        Route::apiResource('users', \App\Http\Controllers\Api\Admin\UserController::class);
        Route::put('users/{user}/roles', [\App\Http\Controllers\Api\Admin\UserController::class, 'assignRoles']);
    });

    // H5端API (english-education-h5) - TODO: 创建对应控制器
    // Route::prefix('h5')->group(function () {
    //     // 学员信息查询
    //     Route::get('students/{student}/profile', [\App\Http\Controllers\Api\H5\StudentController::class, 'profile']);
    //     Route::get('students/{student}/progress', [\App\Http\Controllers\Api\H5\StudentController::class, 'progress']);
    //     Route::get('students/{student}/class-hours', [\App\Http\Controllers\Api\H5\StudentController::class, 'classHours']);
    //
    //     // 课程信息
    //     Route::get('courses/levels', [\App\Http\Controllers\Api\H5\CourseController::class, 'levels']);
    //     Route::get('courses/levels/{level}', [\App\Http\Controllers\Api\H5\CourseController::class, 'levelDetail']);
    // });

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
