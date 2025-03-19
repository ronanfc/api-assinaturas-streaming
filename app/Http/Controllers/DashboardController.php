<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Laravel\Cashier\SubscriptionItem;
use Stripe\Price;
use Stripe\Stripe;

class DashboardController extends Controller
{
    public function index()
    {

        $dados = Cache::remember('metricas_assinaturas', now()->addMinutes(15), function () {

            Stripe::setApiKey(env('STRIPE_SECRET'));

            $prices = Price::all()->data;

            $priceMap = collect($prices)->mapWithKeys(function ($price) {
                return [$price->id => $price->unit_amount];
            });

            $assinaturasAtivas = SubscriptionItem::whereHas('subscription', function ($query) {
                $query->where('stripe_status', 'active');
            });

            $receitaTotal = $assinaturasAtivas->get()->sum(function ($item) use ($priceMap) {
                return ($priceMap[$item->stripe_price] ?? 0) * $item->quantity;
            });

            $data = [];

            $receitasPorMes = $assinaturasAtivas->get()->mapWithKeys(function ($item) use ($priceMap, $data) {
                if (isset($priceMap[$item->stripe_price])) {
                    $mes = $item->created_at->format('M');
                    $quantidade = $item->quantity;
                    $valor = ($priceMap[$item->stripe_price] * $quantidade) / 100;
                    if (isset($data[$mes])) {
                        $data[$mes] += $valor;
                    } else {
                        $data[$mes] = $valor;
                    }
                }
                return [$mes => $data[$mes]];
            })->toArray();


            $inicioDoMes = Carbon::now()->startOfMonth();
            $fimDoMes = Carbon::now()->endOfMonth();

            $assinaturasAtivasMes = SubscriptionItem::whereHas('subscription', function ($query) use ($inicioDoMes, $fimDoMes) {
                $query->where('stripe_status', 'active')
                    ->whereBetween('created_at', [$inicioDoMes, $fimDoMes]);
            });

            $receitaMes = $assinaturasAtivasMes->get()->sum(function ($item) use ($priceMap) {
                $preco = $priceMap[$item->stripe_price] ?? 0;
                return ($preco * $item->quantity);
            });

        return [
                'totalAssinaturas' => User::whereNotNull('stripe_id')->count(),
                'receitaTotal' => $receitaTotal > 0 ? $receitaTotal / 100 : 0,
                'receitaMes' => $receitaMes > 0 ? $receitaMes / 100 : 0,
                'assinaturasAtivas' => $assinaturasAtivas->count(),
                'receitasPorMes' => $receitasPorMes
            ];
        });

        return view('dashboard', compact('dados'));

    }
}
