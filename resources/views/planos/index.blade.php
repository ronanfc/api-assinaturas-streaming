<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Gerenciamento de Planos
        </h2>
    </x-slot>

    @include('layouts.message')

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <section>
                        <!-- Formulário para Criar Plano -->
                        <h2 class="'font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight mb-1">
                            Criar Novo Plano
                        </h2>
                        <form method="POST" action="{{ route('planos.store') }}" class="mt-6 space-y-6">
                            @csrf

                            <div class="row">
                                <div class="col-6">
                                    <label class="form-label">Nome do Plano:</label>
                                    {{ html()->text('name')->class('mb-1 form-control')->required() }}
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Preço (em R$):</label>
                                    {{ html()->number('amount')->class('mb-1 form-control')->attribute('step', '0.01')->required() }}
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Recorrência:</label>
                                    {{ html()->select('interval')->class('mb-1 form-control')
                                        ->options([
                                             "month" => 'Mensal',
                                             "quarter" => 'Trimestral',
                                             "year" => 'Anual'
                                            ])
                                        }}
                                </div>
                                <div class="col-6 d-flex align-items-center justify-content-center">
                                    <x-primary-button>Criar Plano</x-primary-button>
                                </div>
                            </div>
                        </form>
                    </section>

                </div>
            </div>
        </div>
    </div>


    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">

                <h2 class="'font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight mb-1">Planos</h2>

                <table class="table table-striped table-responsive">
                    <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Preço</th>
                        <th>Recorrência</th>
                        <th class="text-center">Ações</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($data as $product)
                        <tr class="p-1">
                            <td>{{ $product['nome'] }}</td>
                            <td>
                                @forelse($product['prices'] as $price)
                                    R$ {{ isset($price['amount']) ? number_format( $price['amount'] , 2, ',', '.') : ''}}
                                    <br>
                                @empty
                                    Nenhum preço
                                @endforelse
                            </td>

                            <td>
                                @forelse($product['prices'] as $price)
                                    {{ $price['recorrencia'] }} <br>
                                @empty
                                    Nenhuma recorrência
                                @endforelse
                            </td>
                            <td class="text-center">

                                <a class="btn btn-sm btn-primary me-3"
                                   href="{{  route('planos.edit', ['id' => $product['id']]) }}">
                                    <i class="fa fa-pencil-alt"></i>
                                </a>

                                <!-- Formulário para Deletar -->
                                <form method="POST" action="{{ route('planos.destroy', $product['id']) }}" class="d-inline">
                                    @csrf @method('DELETE')

                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Deseja realmente deletar?');">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">Nenhum plano encontrado.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>

            </div>
        </div>
    </div>

</x-app-layout>


