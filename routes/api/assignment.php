<?php

use App\Http\Controllers\Api\Assignment\AssignmentController;
use App\Http\Controllers\Api\AssignmentSubmission\AssignmentSubmissionController;
use App\Http\Controllers\Api\LearningMaterial\LearningMaterialController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {

    // ASSIGNMENTS
    Route::middleware('role:SUPER_ADMIN,ADMIN,TEACHER')->group(function () {
        Route::get('/assignments', [AssignmentController::class, 'index']);
        Route::post('/assignments', [AssignmentController::class, 'store']);
        Route::get('/assignments/{assignment}', [AssignmentController::class, 'show']);
        Route::put('/assignments/{assignment}', [AssignmentController::class, 'update']);
        Route::patch('/assignments/{assignment}', [AssignmentController::class, 'update']);
        Route::delete('/assignments/{assignment}', [AssignmentController::class, 'destroy']);
    });

    // ASSIGNMENT SUBMISSIONS - Teacher/Admin
    Route::middleware('role:SUPER_ADMIN,ADMIN,TEACHER')->group(function () {
        Route::get('/assignment-submissions', [AssignmentSubmissionController::class, 'index']);
        Route::get('/assignment-submissions/{assignmentSubmission}', [AssignmentSubmissionController::class, 'show']);
        Route::patch('/assignment-submissions/{assignmentSubmission}/grade', [AssignmentSubmissionController::class, 'grade']);
    });

    // ASSIGNMENT SUBMISSIONS - Student
    Route::middleware('role:STUDENT')->group(function () {
        Route::post('/assignment-submissions', [AssignmentSubmissionController::class, 'store']);
        Route::put('/assignment-submissions/{assignmentSubmission}', [AssignmentSubmissionController::class, 'update']);
        Route::patch('/assignment-submissions/{assignmentSubmission}', [AssignmentSubmissionController::class, 'update']);
    });

    // Submission deletion - Admin only
    Route::middleware('role:SUPER_ADMIN,ADMIN')->delete(
        '/assignment-submissions/{assignmentSubmission}',
        [AssignmentSubmissionController::class, 'destroy']
    );

    // LEARNING MATERIALS
    Route::middleware('role:SUPER_ADMIN,ADMIN,TEACHER')->group(function () {
        Route::get('/learning-materials', [LearningMaterialController::class, 'index']);
        Route::get('/learning-materials/{learningMaterial}', [LearningMaterialController::class, 'show']);
        Route::post('/learning-materials', [LearningMaterialController::class, 'store']);
        Route::put('/learning-materials/{learningMaterial}', [LearningMaterialController::class, 'update']);
        Route::patch('/learning-materials/{learningMaterial}', [LearningMaterialController::class, 'update']);
        Route::post('/learning-materials/{learningMaterial}/publish', [LearningMaterialController::class, 'publish']);
        Route::post('/learning-materials/{learningMaterial}/unpublish', [LearningMaterialController::class, 'unpublish']);
        Route::delete('/learning-materials/{learningMaterial}', [LearningMaterialController::class, 'destroy']);
    });
});