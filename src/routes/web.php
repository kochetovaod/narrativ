<?php

use App\Enums\Permission;
use App\Http\Controllers\PreviewController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'permission:' . Permission::PreviewContent->value, 'noindex'])
    ->prefix('preview')
    ->group(function (): void {
        Route::get('{type}/{slug}', PreviewController::class)
            ->where(['type' => '[A-Za-z_]+'])
            ->name('preview.show');
    });

Route::get('/', function () {
    return view('welcome');
});
