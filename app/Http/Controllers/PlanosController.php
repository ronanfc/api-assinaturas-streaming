<?php

    namespace App\Http\Controllers;

    use App\Data\PlanosData;
    use App\Models\Planos;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Cache;
    use Stripe\Price;
    use Stripe\Product;
    use Stripe\Stripe;

    class PlanosController extends Controller
    {
        public function __construct()
        {
            Stripe::setApiKey(env('STRIPE_SECRET'));
        }

        /**
         * @OA\Get(
         *     path="/api/planos",
         *     summary="Listar todos os planos ativos",
         *     tags={"Planos"},
         *     security={{"bearerAuth":{}}},
         *     @OA\Response(
         *         response=200,
         *         description="Lista de planos ativos",
         *         @OA\JsonContent(
         *             type="array",
         *             @OA\Items(
         *                 @OA\Property(property="id", type="string", example="prod_1234"),
         *                 @OA\Property(property="nome", type="string", example="Plano Mensal"),
         *                 @OA\Property(property="prices", type="array", @OA\Items(
         *                     @OA\Property(property="price_id", type="string", example="price_abc123"),
         *                     @OA\Property(property="amount", type="number", example=99.90),
         *                     @OA\Property(property="currency", type="string", example="BRL"),
         *                     @OA\Property(property="recorrencia", type="string", example="Mensal"),
         *                     @OA\Property(property="interval", type="string", example="month")
         *                 ))
         *             )
         *         )
         *     )
         * )
         */

        public function index(Request $request)
        {

            $data = Cache::remember('todos_planos', now()->addHours(1), function () {
                $products = Product::all(['active' => true])->data;

                $data = [];

                foreach ($products as $product) {
                    // Buscar preços associados ao produto
                    $prices = Price::all(['product' => $product->id, 'active' => true])->data;

                    $data[] = [
                        'id' => $product->id,
                        'nome' => $product->name,
                        'prices' => array_map(function ($price) {
                            [$interval, $recorrencia] = match ($price->recurring->interval) {
                                'month' => $price->recurring->interval_count == 1 ? ['month', 'Mensal'] : [
                                    'quarter', 'Trimestral'
                                ],
                                'year' => ['year', 'Anual'],
                                default => ['', ''],
                            };

                            return [
                                'price_id' => $price->id,
                                'amount' => $price->unit_amount / 100,
                                'currency' => strtoupper($price->currency),
                                'recorrencia' => $recorrencia,
                                'interval' => $interval,
                            ];
                        }, $prices),
                    ];
                }
                return $data;
            });

            if ($request->expectsJson()) {
                return response()->json([
                    'data' => $data,
                ]);
            }

            return view('planos.index', compact('data'));
        }

        /**
         * @OA\Post(
         *     path="/api/planos",
         *     summary="Criar um novo plano",
         *     tags={"Planos"},
         *     security={{"bearerAuth":{}}},
         *     @OA\RequestBody(
         *         required=true,
         *         @OA\JsonContent(
         *             required={"name", "amount"},
         *             @OA\Property(property="name", type="string", example="Plano Premium"),
         *             @OA\Property(property="amount", type="number", example=199.90),
         *             @OA\Property(property="interval", type="string", example="month")
         *         )
         *     ),
         *     @OA\Response(
         *         response=201,
         *         description="Plano criado com sucesso!",
         *         @OA\JsonContent(
         *             @OA\Property(property="message", type="string", example="Plano criado com sucesso!"),
         *             @OA\Property(property="id", type="string", example="prod_abc123")
         *         )
         *     )
         * )
         */

        public function store(Request $request, PlanosData $data)
        {
            try {
                $data->criarPlanos();
                if ($request->expectsJson()) {
                    return response()->json($data->response('data', 'Plano criado com sucesso!'), 201);
                }
                Cache::forget('todos_planos');
                return redirect()->route('planos.index')->with('success', 'Plano criado com sucesso!');
            } catch (\Throwable $e) {
                if ($request->expectsJson()) {
                    abort(500, "Erro ao atualizar plano");
                }
                return redirect()->route('planos.index')->with('error', 'Erro ao criar plano!');
            }

        }

        /**
         * @OA\Get(
         *     path="/api/planos/{id}/edit",
         *     summary="Exibe os detalhes de um plano para edição",
         *     tags={"Planos"},
         *     security={{"bearerAuth":{}}},
         *     @OA\Parameter(
         *         name="id",
         *         in="path",
         *         required=true,
         *         description="ID do plano (product_id do Stripe)",
         *         @OA\Schema(type="string", example="prod_abc123")
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="Detalhes do plano para edição",
         *         @OA\JsonContent(
         *             @OA\Property(property="id", type="string", example="prod_abc123"),
         *             @OA\Property(property="nome", type="string", example="Plano Premium"),
         *             @OA\Property(property="prices", type="array", @OA\Items(
         *                 @OA\Property(property="price_id", type="string", example="price_abc123"),
         *                 @OA\Property(property="amount", type="number", example=199.90),
         *                 @OA\Property(property="interval", type="string", example="month")
         *             ))
         *         )
         *     ),
         *     @OA\Response(
         *         response=404,
         *         description="Plano não encontrado"
         *     )
         * )
         */

        public function edit(Request $request, $id)
        {

            try {
                $product = Product::retrieve($id);
                $prices = Price::all(['product' => $product->id, 'active' => true])->data;

                $product = [
                    'id' => $product->id,
                    'nome' => $product->name,
                    'prices' => array_map(function ($price) {
                        $interval = match ($price->recurring->interval) {
                            'month' => $price->recurring->interval_count == 1 ? 'month' : 'quarter',
                            'year' => 'year',
                            default => '',
                        };
                        return [
                            'price_id' => $price->id,
                            'amount' => $price->unit_amount / 100,
                            'interval' => $interval,
                        ];
                    }, $prices),
                ];

                if ($request->expectsJson()) {
                    return response()->json($product);
                }
                return view('planos.edit', compact('product'));
            } catch (\Exception $e) {
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'Plano não encontrado!'], 404);
                }
                return redirect()->route('planos.index')->with('error', 'Plano não encontrado!');
            }
        }

        /**
         * @OA\Put(
         *     path="/api/planos/{id}",
         *     summary="Atualiza os detalhes de um plano",
         *     tags={"Planos"},
         *     security={{"bearerAuth":{}}},
         *     @OA\Parameter(
         *         name="id",
         *         in="path",
         *         required=true,
         *         description="ID do plano (product_id do Stripe)",
         *         @OA\Schema(type="string", example="prod_abc123")
         *     ),
         *     @OA\RequestBody(
         *         required=true,
         *         @OA\JsonContent(
         *             required={"nome", "price_id", "amount", "interval"},
         *             @OA\Property(property="nome", type="string", example="Plano Anual"),
         *             @OA\Property(property="price_id", type="string", example="price_abc123"),
         *             @OA\Property(property="amount", type="number", example=299.90),
         *             @OA\Property(property="interval", type="string", example="year")
         *         )
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="Plano atualizado com sucesso",
         *         @OA\JsonContent(
         *             @OA\Property(property="message", type="string", example="Plano atualizado com sucesso!")
         *         )
         *     ),
         *     @OA\Response(
         *         response=404,
         *         description="Plano não encontrado"
         *     ),
         *     @OA\Response(
         *         response=500,
         *         description="Erro ao atualizar plano"
         *     )
         * )
         */
        public function update(Request $request, PlanosData $data, $id)
        {
            try {

                dd($request->all());

                $plano = Planos::query()->where('product_id', $id)->firstOrFail();
                $data->atualizarPlano($plano);
                Cache::forget('todos_planos');
                if ($request->expectsJson()) {
                    return response()->json($data->response('data', 'Plano atualizado com sucesso!'));
                }
                return redirect()->route('planos.index')->with('success', 'Plano atualizado com sucesso!');

            } catch (\Throwable $e) {
                if ($request->expectsJson()) {
                    abort(500, "Erro ao atualizar plano");
                }
                return redirect()->route('planos.index')->with('error', 'Erro ao atualizar plano!');
            }


        }

        /**
         * @OA\Delete(
         *     path="/api/planos/{id}",
         *     summary="Desativar um plano",
         *     tags={"Planos"},
         *     security={{"bearerAuth":{}}},
         *     @OA\Parameter(
         *         name="id",
         *         in="path",
         *         required=true,
         *         description="ID do plano (product_id do Stripe)",
         *         @OA\Schema(type="string", example="prod_abc123")
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="Plano desativado com sucesso!",
         *         @OA\JsonContent(
         *             @OA\Property(property="message", type="string", example="Plano deletado com sucesso!")
         *         )
         *     )
         * )
         */
        public function destroy(Request $request, $id)
        {
            try {

                $product = Product::retrieve($id);
                $prices = Price::all(['product' => $product->id]);
                foreach ($prices->data as $price) {
                    Price::update($price->id, [
                        'active' => false,
                    ]);
                }

                Product::update($id, [
                    'active' => false,
                ]);

                $plano = Planos::query()->where('product_id', $id)->first();
                if ($plano) {
                    $plano->delete();
                }

                Cache::forget('todos_planos');

                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Plano deletado com sucesso!']);
                }
                return redirect()->route('planos.index')->with('success', 'Plano deletado com sucesso!');
            } catch (\Throwable $e) {
                if ($request->expectsJson()) {
                    abort(500, "Erro ao deletar plano");
                }
                return redirect()->route('planos.index')->with('error', 'Erro ao deletar plano!');
            }

        }

    }
