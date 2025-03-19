<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Gerenciamento de Assinaturas
        </h2>
    </x-slot>

    @include('layouts.message')

    <section>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">

                        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight mb-1">
                            Criar Nova Assinatura
                        </h2>

                        <form action="{{ route('assinatura.store') }}" method="POST">
                            @csrf

                            <div class="row mb-3">
                                <div class="col-6">
                                        <label for="user_id" class="form-label">Usuário:</label>
                                        {{ html()->select('user_id')
                                            ->class('form-select')
                                            ->options($usuariosAssinante)
                                            ->value(request()->get('user_id') ?? '')
                                            ->required()
                                        }}
                                </div>
                                <div class="col-6">
                                    <label for="price_id" class="form-label">Plano:</label>
                                    {{ html()->select('price_id')
                                            ->class('form-select')
                                            ->options($planos)
                                            ->value(request()->get('price_id') ?? '')
                                            ->required()
                                        }}
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">Criar Assinatura</button>
                        </form>

                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th>Usuário</th>
                            <th>E-mail</th>
                            <th>Plano</th>
                            <th>Status</th>
                            <th>Próxima Fatura</th>
                            <th>Ações</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($users as $user)
                            @php
                                $subscription = $user->subscription('default');
                                $product = \Stripe\Product::retrieve($subscription->asStripeSubscription()->items->data[0]->plan->product);
                            @endphp
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $product->name ?? 'N/A' }}</td>
                                <td>{{ \App\Helper\StripeHelper::buscarStatusAssinatura($subscription) }}</td>
                                <td>
                                    {{
                                        $subscription->active() ?
                                        \App\Helper\StripeHelper::converteTimestampDataHumana($subscription->asStripeSubscription()->current_period_end)
                                         : ''
                                     }}
                                </td>
                                <td>
                                    <a href="{{ route('assinatura.show', ['id' => $user]) }}" class="btn btn-info btn-sm">
                                        <i class="fa fa-search" title="Detalhes"></i>
                                    </a>
                                    @if($subscription->canceled() && $subscription->onGracePeriod())
                                        <form action="{{ route('assinatura.reativar', ['id' => $user]) }}" method="POST" class="d-inline" >
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm">
                                                <i class="fa fa-recycle"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @if($subscription->active() && !$subscription->canceled())
                                        <form action="{{ route('assinatura.cancelar', ['id' => $user]) }}" method="POST"
                                              class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fa fa-trash" title="Cancelar"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">Nenhum assinante encontrado.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </section>


</x-app-layout>
