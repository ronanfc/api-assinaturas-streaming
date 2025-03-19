<?php

    use App\Http\Controllers\AssinaturaController;
    use App\Http\Controllers\DashboardController;
    use App\Http\Controllers\PlanosController;
    use App\Http\Controllers\ProfileController;
    use App\Http\Controllers\StripeWebhookController;
    use Illuminate\Support\Facades\Route;

    Route::get('/', function () {
        return view('welcome');
    });


    Route::middleware(['auth', 'is_admin'])->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        Route::get('/planos', [PlanosController::class, 'index'])->name('planos.index');
        Route::post('/planos', [PlanosController::class, 'store'])->name('planos.store');
        Route::get('/planos/{id}/edit', [PlanosController::class, 'edit'])->name('planos.edit');
        Route::put('/planos/{id}', [PlanosController::class, 'update'])->name('planos.update');
        Route::delete('/planos/{id}', [PlanosController::class, 'destroy'])->name('planos.destroy');

        Route::get('/assinatura', [AssinaturaController::class, 'index'])->name('assinatura.index');
        Route::post('/assinatura', [AssinaturaController::class, 'store'])->name('assinatura.store');
        Route::get('/assinatura/{user}', [AssinaturaController::class, 'show'])->name('assinatura.show');
        Route::post('/assinatura/cancelar', [AssinaturaController::class, 'cancelar'])->name('assinatura.cancelar');
        Route::post('/assinatura/reativar', [AssinaturaController::class, 'reativar'])->name('assinatura.reativar');

    });

    Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook']);



    require __DIR__.'/auth.php';

