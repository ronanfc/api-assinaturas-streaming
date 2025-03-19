<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <p>Total de Assinaturas: {{ $dados['totalAssinaturas'] }}</p>
                    <p>Receita Total: R$ {{ number_format($dados['receitaTotal'], 2, ',', '.') }}</p>
                    <p>Receita Mês: R$ {{ number_format($dados['receitaMes'], 2, ',', '.') }}</p>
                    <p>Assinaturas Ativas: {{ $dados['assinaturasAtivas'] }}</p>

                    <hr>

                    <canvas id="receitasChart"></canvas>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>


@vite(['resources/js/dashboard.js'])

<script>
    const receitaData = @json($dados['receitasPorMes']);
</script>
