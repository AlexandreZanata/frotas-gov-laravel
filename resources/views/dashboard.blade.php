<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Painel de Controle do Gestor') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                    <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium">Total de Corridas</h3>
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-2">{{ $totalRuns }}</p>
                </div>
                <a href="{{ route('vehicles.status') }}" class="block">
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                        <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium">Veículos em Uso</h3>
                        <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-2">{{ $totalVehiclesInUse }}</p>
                    </div>
                </a>
                <a href="{{ route('reports.fuel-analysis') }}" class="block">
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                        <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium">Gasto com Combustível</h3>
                        <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-2">R$ {{ $totalFuelCost }}</p>
                    </div>
                </a>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                    <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium">Quilometragem Total</h3>
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-2">{{ $totalKm }} Km</p>
                </div>
            </section>

            <section class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                    <h3 class="font-semibold text-lg text-gray-900 dark:text-gray-100 mb-4">Corridas por Veículo</h3>
                    <div class="h-96">
                        <canvas id="runsByVehicleChart"></canvas>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                    <h3 class="font-semibold text-lg text-gray-900 dark:text-gray-100 mb-4">Gastos Mensais com Combustível</h3>
                    <div class="h-96">
                        <canvas id="fuelExpensesChart"></canvas>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <x-slot name="scripts">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const runsByVehicleData = {!! $runsByVehicleData !!};
                const monthlyFuelData = {!! $monthlyFuelData !!};

                // Lógica dos gráficos (pode ser movida para um arquivo JS separado depois)
                const powerBiPalette = ['#01B8AA', '#374649', '#FD625E', '#F2C80F', '#5F6B6D'];
                Chart.register(ChartDataLabels);
                Chart.defaults.font.family = "'Figtree', sans-serif";
                Chart.defaults.plugins.tooltip.backgroundColor = '#333';

                // Gráfico 1: Corridas por Veículo
                const runsCtx = document.getElementById('runsByVehicleChart');
                if (runsCtx) {
                    new Chart(runsCtx, {
                        type: 'bar',
                        data: {
                            labels: runsByVehicleData.map(item => item.name),
                            datasets: [{
                                label: 'Total de Corridas',
                                data: runsByVehicleData.map(item => item.run_count),
                                backgroundColor: powerBiPalette,
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                datalabels: {
                                    anchor: 'end',
                                    align: 'end',
                                    color: document.body.classList.contains('dark') ? '#FFF' : '#374649',
                                    font: { weight: 'bold' }
                                }
                            },
                            scales: {
                                x: { ticks: { display: false }, grid: { display: false } },
                                y: { grid: { display: false } }
                            }
                        }
                    });
                }

                // Gráfico 2: Gastos com Combustível
                const fuelCtx = document.getElementById('fuelExpensesChart');
                if (fuelCtx) {
                    new Chart(fuelCtx, {
                        type: 'line',
                        data: {
                            labels: monthlyFuelData.map(item => new Date(item.month + '-02').toLocaleDateString('pt-BR', { month: 'short', year: '2-digit'})),
                            datasets: [{
                                label: 'Gasto Total (R$)',
                                data: monthlyFuelData.map(item => item.total_value),
                                borderColor: powerBiPalette[0],
                                tension: 0.3,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                datalabels: { display: false }
                            },
                            scales: {
                                y: {
                                    ticks: {
                                        callback: (value) => 'R$ ' + value.toLocaleString('pt-BR')
                                    }
                                }
                            }
                        }
                    });
                }
            });
        </script>
    </x-slot>
</x-app-layout>
