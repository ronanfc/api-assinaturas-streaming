<?php
/*
 * Copyright (c) 2025. Ronan Cândido
 */

namespace App\Helper;

    use App\Models\User;
    use Carbon\Carbon;
    use Stripe\Checkout\Session;

    class StripeHelper
    {
        public static function createDiscountLink(User $user, $plano)
        {
            $discountCode = match ($plano) {
                'month' => '10PERCENTOFF_MENSAL',
                'quarter' => '10PERCENTOFF_TRIMESTRAL',
                'year' => '10PERCENTOFF_ANUAL',
                default => '10PERCENTOFF',
            };

            $subscription = $user->subscription('default');
            $planId = $subscription->stripe_price;

            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price' => $planId,
                    'quantity' => 1,
                    'discounts' => [[
                        'coupon' => $discountCode,
                    ]],
                ]],
                'mode' => 'subscription',
                'success_url' => route('checkout.success'),
                'cancel_url' => route('checkout.cancel'),
            ]);

            return $session->url;
        }

        public static function converteTimestampDataHumana(?int $timestamp = null): string
        {
            if(!$timestamp) {
                return '';
            }

            try{
                $date = Carbon::createFromTimestamp($timestamp);

                return  $date->format('d-m-Y');
            } catch (\Throwable) {
                return '';
            }
        }

        public static function buscarStatusAssinatura($subscription = null): string
        {
            if (!$subscription) {
                return '';
            }

            return match (true) {
                $subscription->canceled() && !$subscription->onGracePeriod() => 'Cancelada',
                $subscription->canceled() && $subscription->onGracePeriod() => 'Cancelada Carência',
                default => 'Ativa',
            };

        }

    }
