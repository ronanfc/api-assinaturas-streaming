<?php

    use App\Http\Controllers\AuthController;
    use App\Http\Controllers\AssinaturaController;
    use App\Http\Controllers\ConteudoController;
    use App\Http\Controllers\PlanosController;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Route;


    Route::get('/', function (Request $request) {
        return 'API running';
    });

    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::middleware('auth:api')->post('logout', [AuthController::class, 'logout']);

    Route::middleware('auth:api')->group(function () {

        Route::get('/assinatura/required', [ConteudoController::class, 'assinaturaRequired'])
            ->name('api.assinatura.required');
        Route::get('/assinatura/upgrade', [ConteudoController::class, 'upgrade'])
            ->name('api.assinatura.upgrade');

        Route::get('/conteudo/{plano}', [ConteudoController::class, 'conteudo'])
            ->middleware('assinaturas')
            ->name('api.conteudo');


        Route::post('/assinatura', [AssinaturaController::class, 'store']);
        Route::post('/assinatura/cancelar', [AssinaturaController::class, 'cancelar']);
        Route::post('/assinatura/cancelarImediato', [AssinaturaController::class, 'cancelarImediato']);
        Route::post('/assinatura/reativar', [AssinaturaController::class, 'reativar']);
        Route::post('/assinatura/mudar-plano', [AssinaturaController::class, 'mudarPlano']);
        Route::post('/assinatura/verificar-status', [AssinaturaController::class, 'verificarStatus']);
        Route::post('/assinatura/vencimento', [AssinaturaController::class, 'vencimentoAssinatura']);
    });

    Route::middleware(['auth:api', 'is_admin'])->group(function () {
        Route::get('/planos', [PlanosController::class, 'index']); // Listar planos
        Route::post('/planos', [PlanosController::class, 'store']);   // Criar plano
        Route::put('/planos/{id}', [PlanosController::class, 'update']); // Atualizar plano
        Route::delete('/planos/{id}', [PlanosController::class, 'destroy']); // Deletar plano
    });
