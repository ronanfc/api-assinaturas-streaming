<?php

    use App\Helper\StripeHelper;
    use App\Mail\DescontoPagamentoAntecipado;
    use App\Models\User;
    use App\Notifications\AssinaturaExpirando;
    use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
    use Illuminate\Support\Facades\Mail;
    use Illuminate\Support\Facades\Schedule;
    use Stripe\Stripe;

    Artisan::command('inspire', function () {
    /** @var ClosureCommand $this */
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


    Artisan::command('notificacao:expirando-assinatura', function () {
        $users = User::whereHas('subscriptions', function ($query) {
            $query->where('ends_at', '<=', now()->addDays(3))
                ->where('stripe_status', 'active');
        })->get();

        foreach ($users as $user) {
            $user->notify(new AssinaturaExpirando());
        }

        $this->info('Notificações de expiração enviadas com sucesso!');
    })->describe('Envia notificações de expiração de assinatura.');


    Artisan::command('desconto:pagamento-antecipado', function () {
        Stripe::setApiKey(config('cashier.secret'));

        $users = User::whereHas('subscriptions', function ($query) {
            $query->where('stripe_status', 'active');
        })->get();

        foreach ($users as $user) {
            $subscription = $user->subscription('default');

            if (!$subscription) {
                continue; // Ignora se o usuário não tiver uma assinatura válida
            }

            $stripeSubscription = $subscription->asStripeSubscription();
            $dueDate = $stripeSubscription->current_period_end; // Data de vencimento em timestamp
            $dueDateFormatted = date('Y-m-d', $dueDate);

            $plano = $stripeSubscription->items->data[0]->price->recurring->interval;

            // Determina o prazo de envio do lembrete
            $reminderDays = match ($plano) {
                'month' => 10,       // 10 dias antes para mensal
                'quarter' => 30,     // 30 dias antes para trimestral
                'year' => 90,        // 90 dias antes para anual
                default => 0,        // Caso não reconhecido, ignora
            };

            $reminderDate = now()->addDays($reminderDays)->format('Y-m-d');

            // Verifica se estamos no prazo correto para enviar o lembrete
            if (date('Y-m-d', $dueDate) == $reminderDate) {
                $discountUrl = StripeHelper::createDiscountLink($user, $plano);
                Mail::to($user)->send(new DescontoPagamentoAntecipado($user, $dueDateFormatted, $discountUrl));
            }
        }
    });


    Schedule::command('notificacao:expirando-assinatura')->daily();
    Schedule::command('notificacao:desconto-pagamento-antecipado')->daily();
