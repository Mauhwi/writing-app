<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ChapterController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');

Route::delete('/projects/{project}', [ProjectController::class, 'delete'])
    ->name('projects.delete');

Route::get('/projects/{project}', [ProjectController::class, 'show'])
    ->name('projects.show');

Route::post('/projects/{project}/cover', [ProjectController::class, 'updateCover'])
    ->name('projects.cover.update');

Route::delete('/projects/{project}/cover', [ProjectController::class, 'deleteCover'])
    ->name('projects.cover.delete');

Route::post('/projects/{project}/chapters', [ChapterController::class, 'store'])
    ->name('projects.chapters.store');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
