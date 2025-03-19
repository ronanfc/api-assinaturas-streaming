<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\PagamentoConfirmado;
use Laravel\Cashier\Events\WebhookReceived;
use Laravel\Cashier\Payment;
use App\Models\User;

class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $payload = $request->all();

        if ($payload['type'] === 'invoice.payment_succeeded') {
            $this->handlePaymentSucceeded($payload['data']['object']);
        }

        return response()->json(['status' => 'success']);
    }

    protected function handlePaymentSucceeded($invoice)
    {
        $user = User::where('stripe_id', $invoice['customer'])->first();

        if (!$user) {
            Log::error("Usuário não encontrado para o Stripe ID: {$invoice['customer']}");
            return;
        }

        $payment = new Payment($invoice['payment_intent']);
        if ($payment->isSucceeded()) {
            Log::info("Pagamento confirmado para o usuário: {$user->email}");
            Mail::to($user->email)->send(new PagamentoConfirmado($user));
        } else {
            Log::error("Pagamento falhou para o usuário: {$user->email}");
        }
    }
}
