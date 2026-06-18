<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\CommentThreadController;
use App\Http\Controllers\CommentMessageController;

Route::get('/', function () {
    return Inertia::render('Auth/Login', [
        'canLogin' => Route::has('login'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->middleware('guest'); 

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')
    ->scopeBindings()
    ->group(function () {

        Route::post('/projects', [ProjectController::class, 'store'])
            ->name('projects.store');

        Route::delete('/projects/{project}', [ProjectController::class, 'delete'])
            ->name('projects.delete');

        Route::get('/projects/{project}', [ProjectController::class, 'show'])
            ->name('projects.show');

        Route::post('/projects/{project}/cover', [ProjectController::class, 'updateCover'])
            ->name('projects.cover.update');

        Route::delete('/projects/{project}/cover', [ProjectController::class, 'deleteCover'])
            ->name('projects.cover.delete');

        Route::patch('/projects/{project}', [ProjectController::class, 'update'])
            ->name('projects.update');

        Route::post('/projects/{project}/chapters', [ChapterController::class, 'store'])
            ->name('projects.chapters.store');

        Route::get('/projects/{project}/chapters/{chapter}', [ChapterController::class, 'show'])
            ->name('projects.chapters.show');

        Route::get('/projects/{project}/chapters/{chapter}/content', [ChapterController::class, 'getContent'])
            ->name('projects.chapters.content');

        Route::patch('/projects/{project}/chapters/{chapter}/content', [ChapterController::class, 'updateContent'])
            ->name('projects.chapters.updateContent');

        Route::patch('/projects/{project}/chapters/{chapter}/details', [ChapterController::class, 'updateDetails'])
            ->name('projects.chapters.updateDetails');

        Route::post(
            '/projects/{project}/chapters/{chapter}/comment-threads', [CommentThreadController::class, 'store']
        )->name('comment-threads.store');

        Route::post('/comment-threads/{thread}/messages', [CommentMessageController::class, 'store']
        )->name('comment-messages.store');

        Route::delete('/comment-messages/{message}', [CommentMessageController::class, 'destroy']
        )->name('comment-messages.destroy');

        Route::delete('/comment-threads/{thread}', [CommentThreadController::class, 'destroy']
        )->name('comment-threads.destroy');
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
