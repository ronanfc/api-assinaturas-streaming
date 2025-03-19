<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\AssinaturaStoreRequest;
    use App\Models\User;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Cache;
    use Stripe\Price;
    use Stripe\Product;
    use Stripe\Stripe;
    use Stripe\Subscription;

    /**
     * @OA\Info(
     *     title="API Assinatura Streaming",
     *     version="1.0.0"
     * )
     *
     * @OA\SecurityScheme(
     *     securityScheme="bearerAuth",
     *     type="http",
     *     scheme="bearer",
     *     bearerFormat="JWT"
     * )
     */

    class AssinaturaController extends Controller
    {

        public function __construct()
        {
            Stripe::setApiKey(env('STRIPE_SECRET'));
        }

        /**
         * @OA\Get(
         *     path="/assinaturas",
         *     summary="Lista todas as assinaturas",
         *     tags={"Assinaturas"},
         *     security={{"bearerAuth":{}}},
         *     @OA\Response(response=200, description="Lista de assinaturas")
         * )
         */

        public function index()
        {

            $users = Cache::remember('todas_assinaturas', now()->addHours(1), function () {
                return User::query()->withWhereHas('subscriptions')->get();
            });

            $usuariosAssinante = User::query()
                ->where('is_admin', false)
                ->pluck('name', 'id')
                ->prepend('Selecione o usuário', '');

            $planos = $this->buscarPlanos();

            return view('assinaturas.index', compact('users', 'usuariosAssinante', 'planos'));
        }

        /**
         * @OA\Get(
         *     path="/assinaturas/{user}",
         *     summary="Exibe uma assinatura",
         *     tags={"Assinaturas"},
         *     security={{"bearerAuth":{}}},
         *     @OA\Parameter(
         *         name="user",
         *         in="path",
         *         required=true,
         *         @OA\Schema(type="integer")
         *     ),
         *     @OA\Response(response=200, description="Detalhes da assinatura"),
         *     @OA\Response(response=404, description="Assinatura não encontrada")
         * )
         */
        public function show(Request $request, $id)
        {
            $user = User::find($id);

            if (!$user) {
                $user = $request->user();
            }

            $subscription = $user->subscription('default');

            if (!$subscription) {
                abort(404, 'Assinatura não encontrada.');
            }

            $stripeSubscription = Subscription::retrieve($subscription->stripe_id);

            return view('assinatura.show', compact('user', 'stripeSubscription'));
        }

        /**
         * @OA\Post(
         *     path="/assinaturas",
         *     summary="Cria uma nova assinatura",
         *     tags={"Assinaturas"},
         *     security={{"bearerAuth":{}}},
         *     @OA\RequestBody(
         *         required=true,
         *         @OA\JsonContent(
         *             required={"user_id", "price_id"},
         *             @OA\Property(property="user_id", type="integer", example=1),
         *             @OA\Property(property="price_id", type="string", example="price_12345")
         *         )
         *     ),
         *     @OA\Response(response=201, description="Assinatura criada com sucesso"),
         *     @OA\Response(response=500, description="Erro ao criar assinatura")
         * )
         */
        public function store(AssinaturaStoreRequest $request)
        {
            $user = User::find($request->user_id);

            if (!$user) {
                $user = $request->user();
            }

            try {

                // Cria um cliente no Stripe se não existir
                if (!$user->hasStripeId()) {
                    $user->createAsStripeCustomer();
                }

                if (!$user->hasPaymentMethod()) {
                    $paymentMethod = $user->addPaymentMethod('pm_card_visa');
                    $user->updateDefaultPaymentMethod($paymentMethod->id);
                } else {
                    $paymentMethod = $user->paymentMethods()->first();
                }

                // Criar assinatura
                $user->newSubscription('default', $request->price_id)->create($paymentMethod->id);

                Cache::forget('todas_assinaturas');

                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Assinatura criada com sucesso!']);
                }
                return redirect()->back()->with('success', 'Assinatura criada com sucesso!');
            } catch (\Exception $e) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Assinatura criada com sucesso!'], 500);
                }
                return redirect()->back()->with('error', 'Erro ao criar assinatura');
            }

        }

        /**
         * @OA\Post(
         *     path="/assinaturas/cancelar",
         *     summary="Cancela uma assinatura",
         *     tags={"Assinaturas"},
         *     security={{"bearerAuth":{}}},
         *     @OA\RequestBody(
         *         required=false,
         *         @OA\JsonContent(
         *             @OA\Property(property="id", type="integer", example=1)
         *         )
         *     ),
         *     @OA\Response(response=200, description="Assinatura cancelada com sucesso"),
         *     @OA\Response(response=500, description="Erro ao cancelar assinatura")
         * )
         */
        public function cancelar(Request $request)
        {
           if($userId = $request->get('id')){
               $user = User::find($userId);
           } else {
               $user = $request->user();
           }

            $subscription = $user->subscription('default');

            if ($subscription && $subscription->active() && !$subscription->canceled()) {
                $subscription->cancel();
                Cache::forget('todas_assinaturas');
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Assinatura cancelada com sucesso!']);
                }
                return back()->with('success', 'Assinatura cancelada com sucesso!');
            }

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Assinatura não encontrada ou já cancelada!'], 500);
            }
            return back()->with('error', 'Assinatura não encontrada ou já cancelada!');


        }

        public function cancelarImediato(Request $request)
        {
            if($userId = $request->get('id')){
                $user = User::find($userId);
            } else {
                $user = $request->user();
            }

            $subscription = $user->subscription('default');

            if ($subscription && $subscription->active()) {
                $subscription->cancelNow();
                Cache::forget('todas_assinaturas');
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Assinatura cancelada com sucesso!']);
                }
                return back()->with('success', 'Assinatura cancelada com sucesso!');
            }

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Assinatura não encontrada ou já cancelada!'], 404);
            }
            return back()->with('error', 'Assinatura não encontrada ou já cancelada!');

        }

        /**
         * @OA\Post(
         *     path="/assinaturas/reativar",
         *     summary="Reativa uma assinatura em período de carência",
         *     tags={"Assinaturas"},
         *     security={{"bearerAuth":{}}},
         *     @OA\RequestBody(
         *         required=false,
         *         @OA\JsonContent(
         *             @OA\Property(property="id", type="integer", example=1)
         *         )
         *     ),
         *     @OA\Response(response=200, description="Assinatura reativada com sucesso"),
         *     @OA\Response(response=500, description="Erro ao reativar assinatura")
         * )
         */
        public function reativar(Request $request)
        {
            if($userId = $request->get('id')){
                $user = User::find($userId);
            } else {
                $user = $request->user();
            }

            $subscription = $user->subscription('default');

            if ($subscription && $subscription->onGracePeriod()) {
                $subscription->resume();
                Cache::forget('todas_assinaturas');
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Assinatura reativada com sucesso!']);
                }
                return back()->with('success', 'Assinatura reativada com sucesso!');
            }

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Assinatura não pode ser reativada!']);

            }
            return back()->with('error', 'Assinatura não pode ser reativada!');
        }

        /**
         * @OA\Post(
         *     path="/assinaturas/status",
         *     summary="Verifica o status da assinatura do usuário autenticado",
         *     tags={"Assinaturas"},
         *     security={{"bearerAuth":{}}},
         *     @OA\Response(response=200, description="Status da assinatura"),
         *     @OA\Response(response=404, description="Sem assinatura ativa")
         * )
         */
        public function verificarStatus(Request $request)
        {
            $user = $request->user();

            if ($user->subscribed('default')) {
                return response()->json([
                    'plano' => $user->subscription('default')->stripe_plan,
                    'data_expiracao' => $user->subscription('default')->ends_at,
                    'status_assinatura' => $user->subscription('default')->status,
                ]);
            }

            return response()->json([
                'status' => 'Sem assinatura ativa',
            ]);
        }

        private function buscarPlanos()
        {
            $products = Product::all([
                'active' => true,
                'limit' => 100,
            ]);

            $planos[''] = 'Selecione o plano';

            foreach ($products->data as $product) {
                // Buscar preços ativos para cada produto
                $prices = Price::all([
                    'product' => $product->id,
                    'active' => true,
                    'limit' => 1,
                ]);

                foreach ($prices->data as $price) {
                    $planos[$price->id] = $product->name;
                }
            }

            return $planos;
        }

    }
