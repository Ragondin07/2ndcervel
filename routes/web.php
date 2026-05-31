<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ActionController;
use App\Http\Controllers\Admin\IndexingAdminController;
use App\Http\Controllers\DecisionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\PinController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\QuickAddController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::post('/pin/{type}/{id}', PinController::class)->whereNumber('id')->name('pin.toggle');
    Route::resource('projects', ProjectController::class);
    Route::patch('/projects/{project}/archive', [ProjectController::class, 'archive'])->name('projects.archive');
    Route::resource('notes', NoteController::class);
    Route::patch('/notes/{note}/archive', [NoteController::class, 'archive'])->name('notes.archive');
    Route::get('/quick-add', [QuickAddController::class, 'create'])->name('quick-add');
    Route::post('/quick-add', [QuickAddController::class, 'store'])->name('quick-add.store');
    Route::get('/search', SearchController::class)->name('search');
    Route::resource('decisions', DecisionController::class);
    Route::get('/actions', [ActionController::class, 'index'])->name('actions.index');
    Route::get('/actions/create', [ActionController::class, 'create'])->name('actions.create');
    Route::post('/actions', [ActionController::class, 'store'])->name('actions.store');
    Route::get('/actions/{action}', [ActionController::class, 'show'])->name('actions.show');
    Route::resource('files', FileController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
    Route::patch('/files/project', [FileController::class, 'bulkUpdateProject'])->name('files.bulk-project');
    Route::patch('/files/{file}/project', [FileController::class, 'updateProject'])->name('files.project');
    Route::get('/files/{file}/download', [FileController::class, 'download'])->name('files.download');
    Route::post('/files/{file}/reindex', [FileController::class, 'reindex'])->name('files.reindex');
    Route::post('/files/{file}/ocr', [FileController::class, 'retryOcr'])->name('files.ocr');
    Route::get('/inbox', [InboxController::class, 'index'])->name('inbox');
    Route::patch('/inbox/{type}/{id}/project', [InboxController::class, 'assignProject'])->name('inbox.assign-project');
    Route::patch('/inbox/notes/{note}/type', [InboxController::class, 'updateNoteType'])->name('inbox.notes.type');
    Route::post('/inbox/notes/{note}/convert-decision', [InboxController::class, 'convertNoteToDecision'])->name('inbox.notes.convert-decision');
    Route::post('/inbox/notes/{note}/convert-action', [InboxController::class, 'convertNoteToAction'])->name('inbox.notes.convert-action');
    Route::patch('/inbox/{type}/{id}/archive', [InboxController::class, 'archive'])->name('inbox.archive');
    Route::delete('/inbox/{type}/{id}', [InboxController::class, 'destroy'])->name('inbox.destroy');
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/', [IndexingAdminController::class, 'index'])->name('index');
        Route::post('/indexing/files', [IndexingAdminController::class, 'reindexFiles'])->name('indexing.reindex-files');
        Route::post('/indexing/files/{file}', [IndexingAdminController::class, 'reindexFile'])->name('indexing.reindex-file');
        Route::post('/indexing/projects/{project}', [IndexingAdminController::class, 'reindexProject'])->name('indexing.reindex-project');
        Route::post('/indexing/rebuild-search', [IndexingAdminController::class, 'rebuildSearchIndex'])->name('indexing.rebuild-search');
    });
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
