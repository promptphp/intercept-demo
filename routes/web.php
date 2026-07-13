<?php

use App\Http\Controllers\Demos\LogDebuggerController;
use App\Http\Controllers\Demos\SupportChatController;
use App\Http\Controllers\Demos\TriageController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'demos.index')->name('home');

Route::prefix('demos')->name('demos.')->group(function () {
    Route::get('support', [SupportChatController::class, 'show'])->name('support');
    Route::post('support', [SupportChatController::class, 'store'])->name('support.store');

    Route::get('triage', [TriageController::class, 'index'])->name('triage');
    Route::post('triage/reset', [TriageController::class, 'reset'])->name('triage.reset');
    Route::post('triage/{inboundEmail}', [TriageController::class, 'store'])->name('triage.store');

    Route::get('debugger', [LogDebuggerController::class, 'show'])->name('debugger');
    Route::post('debugger', [LogDebuggerController::class, 'store'])->name('debugger.store');
});
