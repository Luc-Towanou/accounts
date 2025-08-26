<?php

use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\Api\CategorieController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\MoisComptableController;
use App\Http\Controllers\Api\OperationController;
use App\Http\Controllers\Api\RecurrenceController;
use App\Http\Controllers\Api\RegleCalculController;
use App\Http\Controllers\Api\SousTableauController;
use App\Http\Controllers\Api\SousVariableController;
use App\Http\Controllers\Api\TableauController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VariableController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('auth')->group(function () { 
    Route::post('/register', [AuthController::class, 'register']); 
    Route::post('/login', [AuthController::class, 'login']); 
    Route::post('/login/otp', [AuthController::class, 'loginByOtp']);
    Route::post('/verifymailotp', [AuthController::class, 'verifymailByOtp']);
    Route::post('/resendotp', [AuthController::class, 'resendOtp']);
    // Route::post('/password/sendotp', [AuthController::class, 'sendResetOtp']);
    Route::post('/password/resetotp', [AuthController::class, 'ResetPasswordbyOtp']);



    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [UserController::class, 'me']); // 
        Route::post('/logout', [AuthController::class, 'logout']); 
    });
});


Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('mois-comptables', MoisComptableController::class);
    Route::apiResource('tableaux', TableauController::class);
    Route::apiResource('sous-tableaux', SousTableauController::class);
    Route::apiResource('variables', VariableController::class);
    Route::apiResource('sous-variables', SousVariableController::class);
    Route::apiResource('operations', OperationController::class);
    Route::apiResource('regles-calcul', RegleCalculController::class);
    Route::apiResource('recurrences', RecurrenceController::class);

    // operations
    Route::get('operations/variable/{variableId}', [OperationController::class, 'index']);
    Route::put('/operations/{operationId}', [OperationController::class, 'update']);
    Route::delete('operations/{operationId}', [OperationController::class, 'destroy']);

    // variables
    
    Route::get('/variables/montant/{id}', [VariableController::class, 'montant']);

    // Dashboard
    Route::prefix('dashboard')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index']);
    });
});
Route::get('/mois-comptable/{id}/export-pdf', [MoisComptableController::class, 'exportMoisPDF'])
     ->name('mois-comptable.export-pdf');
// routes/web.php
Route::get('/mois-comptable-pro/{id}/export-pdf', [MoisComptableController::class, 'exportPdf'])
     ->name('mois-comptable-pro.export-pdf');
// text regles de calcul : 
// Pour tester avec Postman ou API

// Route::middleware(['auth:sanctum'])->prefix('regles-calcul/test')->group(function () {
Route::apiResource('categories', CategorieController::class);
Route::get('categories/{id}/variables', [CategorieController::class, 'variables']);
Route::get('categories-count', [CategorieController::class, 'countVariables']);
Route::get('categorie-slug/{slug}', [CategorieController::class, 'bySlug']);

Route::prefix('regles-calcul/test')->group(function () {


    // 1. Tester l’évaluation
    Route::post('/evaluer', [RegleCalculController::class, 'evaluer']);
    Route::get('/evaluer/variable/{variableId}', [RegleCalculController::class, 'evaluerVariable']);

    // 2. Tester la validation
    Route::post('/valider', [RegleCalculController::class, 'valider']);

    // 3. Tester l'appartenance d'une variable à une règle
    Route::get('/variable-utilisee/{id}', [RegleCalculController::class, 'variableRegle']);

    // 4. Tester l'appartenance d'une sous-variable à une règle
    Route::get('/sous-variable-utilisee/{id}', [RegleCalculController::class, 'sousVariableRegle']);

    // 5. Annalyser une regles et renvoyer ses élements 
    Route::get('/analyse/elements/{id}', [RegleCalculController::class, 'analyseRegle']);
});