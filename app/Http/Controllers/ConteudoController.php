<?php

    namespace App\Http\Controllers;

    use App\Data\ConteudoData;
    use App\Models\Planos;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Cache;

    class ConteudoController extends Controller
    {
        public function conteudo(ConteudoData $plano)
        {
            /* @var Planos $planos */
            $plano = Planos::query()->where('slug', $plano)->firstOrFail();

            $cacheKey = Auth::id().$plano->slug;

            $conteudo = Cache::remember($cacheKey, now()->addHours(1), function () {
                return response()->json([
                    'message' => 'Conteúdo do Plano Mensal.',
                    'content' => 'Você está acessando o conteúdo exclusivo do plano mensal.',
                ]);
            });

            return response()->json($conteudo);
        }

        public function assinaturaRequired()
        {
            return response()->json([
                'error' => 'Acesso negado. É necessário possuir uma assinatura ativa.',
            ], 403);
        }

        public function upgrade()
        {
            return response()->json([
                'error' => 'Plano insuficiente. Faça um upgrade para acessar este conteúdo.',
            ], 403);
        }
    }
