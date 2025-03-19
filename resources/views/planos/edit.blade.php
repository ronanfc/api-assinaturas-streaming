<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Editar Plano - {{ $product['nome'] }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <section>

                        {{ html()->form('PUT', route('planos.update', $product['id']))->attribute('enctype', 'multipart/form-data')->open() }}
                        {{ html()->hidden('id', $product['id']) }}

                        {{ html()->token() }}

                                <div class="row mb-2">
                                    <div class="col-6">
                                        <label class="form-label">Nome do Plano:</label>
                                        {{ html()->text('name')->class('mb-1 form-control')
                                            ->value( old('name', $product['nome'] ?? ''))
                                            ->required() }}
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Preço (em R$):</label>
                                        {{ html()->number('price')->class('mb-1 form-control')
                                            ->value(old('name', $product['prices'][0]['amount'] ?? ''))
                                            ->attribute('step', '0.01')->required() }}
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Recorrência:</label>
                                        {{ html()->select('interval')->class('mb-1 form-control')
                                            ->value(old('name', $product['prices'][0]['interval'] ?? ''))
                                            ->options([
                                                 "month" => 'Mensal',
                                                 "quarter" => 'Trimestral',
                                                 "year" => 'Anual'
                                                ])
                                            }}
                                    </div>
                                </div>

                        <button type="submit" class="btn btn-primary">Atualizar</button>

                        {{ html()->form()->close() }}

                    </section>

                </div>
            </div>
        </div>
    </div>

</x-app-layout>
