<?php

use App\Http\Controllers\CohortController;
use App\Http\Controllers\CommonLifeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RetroController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\KnowledgeController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\TeacherController;
use Illuminate\Support\Facades\Route;

// Redirect the root path to /dashboard
Route::redirect('/', 'dashboard');

Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('verified')->group(function () {

        //
        // Base Route (Made by Thibaud)
        //

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Cohorts
        Route::get('/cohorts', [CohortController::class, 'index'])->name('cohort.index');
        Route::get('/cohort/{cohort}', [CohortController::class, 'show'])->name('cohort.show');

        // Teachers
        Route::get('/teachers', [TeacherController::class, 'index'])->name('teacher.index');

        // Students
        Route::get('students', [StudentController::class, 'index'])->name('student.index');

        // Knowledge
        Route::get('knowledge', [KnowledgeController::class, 'index'])->name('knowledge.index');

        // Groups
        Route::get('groups', [GroupController::class, 'index'])->name('group.index');

        // Common life
        Route::get('common-life', [CommonLifeController::class, 'index'])->name('common-life.index');

        //
        // AI GEMINI
        //

        // Generate Groups
        Route::post('/cohort/{cohort}/generate-groups', [\App\Http\Controllers\CohortController::class, 'generateGroups']);

        // Save groups generate
        Route::post('/cohort/save-groups', [CohortController::class, 'saveGroups']);

        // Test AI (useless now)
        Route::get('/test-gemini', [CohortController::class, 'testGemini']);

        //
        // GROUPS
        //

        //Bonus : delete group generation:
        Route::delete('/cohort/{cohort}/delete-generation/{generation}', [CohortController::class, 'deleteGeneration'])->name('groups.deleteGeneration');


        //Aucune policies et middleware désolé :/

        //
        // RETRO
        //
        route::get('retros', [RetroController::class, 'index'])->name('retro.index');
        Route::get('/retros/all-ajax-data', [RetroController::class, 'allRetrosAjaxData'])
            ->name('retros.allAjaxData');
        Route::post('/retros/ajax-store', [RetroController::class, 'ajaxStore'])->name('retros.ajaxStore');
        Route::post('/retros/ajax-store-column', [RetroController::class, 'ajaxStoreColumn'])->name('retros.ajaxStoreColumn');
        Route::post('/retros/ajax-store-element', [RetroController::class, 'ajaxStoreElement'])->name('retros.ajaxStoreElement');
        Route::post('/retros/ajax-rename-element', [RetroController::class, 'ajaxRenameElement'])
            ->name('retros.ajaxRenameElement');
        Route::post('/retros/ajax-update-element-column', [RetroController::class, 'ajaxUpdateElementColumn'])
            ->name('retros.ajaxUpdateElementColumn');
        Route::post('/retros/ajax-delete-column', [RetroController::class, 'ajaxDeleteColumn'])
            ->name('retros.ajaxDeleteColumn');
        Route::post('/retros/ajax-delete-element', [RetroController::class, 'ajaxDeleteElement'])
            ->name('retros.ajaxDeleteElement');
    });

});

require __DIR__.'/auth.php';
