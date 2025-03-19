<?php

    namespace App\Http\Middleware;

    use App\Models\Planos;
    use Closure;
    use Illuminate\Http\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Illuminate\Support\Facades\Cache;

    class VerificarAssinatura
    {
        /**
         * Handle an incoming request.
         *
         * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
         */
        public function handle(Request $request, Closure $next, $plano = null): Response
        {
            $user = $request->user();

            if (!$user) {
                return response()->json(['error' => 'Usuário não autenticado.'], 401);
            }

            /* @var Planos $plano */
            $plano = Planos::query()->where('slug', $plano)->first();

            if (!$plano) {
                return response()->json(['error' => 'Plano não localizado'], 404);
            }

            $cacheKey = "usuario_assinatura_{$plano->slug}_{$user->id}";

            $isSubscribed = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($user, $plano) {

                // Se o usuário não estiver autenticado ou não possuir assinatura ativa
                if (!$user || !$user->subscribed('default')) {
                    return response()->json([
                        'error' => 'Acesso restrito. Assinatura ativa necessária.',
                    ], 403);
                }

                $assinatura = $user->subscription('default');


                $existePlano = $assinatura->items->constains('stripe_price', $plano->price_id);

                if ($existePlano) {
                    return response()->json([
                        'error' => 'Assinatura insuficiente. Faça upgrade para acessar este conteúdo.',
                    ], 403);
                }
                return true;
            });

            if ($isSubscribed instanceof Response) {
                return $isSubscribed;
            }


            return $next($request);
        }
    }
