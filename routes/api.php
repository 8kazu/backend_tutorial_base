<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ArticleController;

// ================================
// コメント機能
// ================================
Route::get('/articles/{article_id}/comments', [CommentController::class, 'index']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/articles/{article_id}/comments', [CommentController::class, 'store']);
    Route::put('/comments/{comment_id}', [CommentController::class, 'update']);
    Route::delete('/comments/{comment_id}', [CommentController::class, 'destroy']);
});

// ================================
// 記事管理
// ================================
Route::get('/articles', [ArticleController::class, 'index']);
Route::get('/articles/{articleId}', [ArticleController::class, 'show']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/articles', [ArticleController::class, 'store']);
    Route::put('/articles/{articleId}', [ArticleController::class, 'update']);
    Route::delete('/articles/{articleId}', [ArticleController::class, 'destroy']);
});

// ================================
// ユーザー管理
// ================================

// 認証関連
Route::post('/register/step1', [UserController::class, 'registerStep1']);
Route::post('/register/step2', [UserController::class, 'registerStep2']);
Route::post('/login', [UserController::class, 'login']);
// ユーザー情報関連
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [UserController::class, 'show']);
    Route::put('/user', [UserController::class, 'update']);
    Route::delete('/user', [UserController::class, 'destroy']);
    Route::post('/logout', [UserController::class, 'logout']);
});
