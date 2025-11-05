@extends('app.layouts.app')

@section('title', 'Dashboard')

<x-layouts.headers.create-header :title="'Casa Arari Dashboard'"/>

@section('content')
<section class="container px-4 mx-auto space-y-20">
    <div class="flex justify-start mt-10 space-x-10">
        <!-- Botão Nova Venda -->
        <a href="{{ route('frente-caixa') }}"
           class="flex items-center px-6 py-3 text-white bg-green-600 rounded-lg shadow-md hover:bg-blue-700 transition duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-30 mr-2" fill="currentColor" viewBox="0 0 16 16">
                <path d="M8 7a.5.5 0 0 1 .5.5V9h1.5a.5.5 0 0 1 0 1H8.5v1.5a.5.5 0 0 1-1 0V10H6a.5.5 0 0 1 0-1h1.5V7.5A.5.5 0 0 1 8 7z"/>
                <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .49.598l-1.5 7A.5.5 0 0 1 13 11H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM4.415 10h8.17l1.2-5.6H3.215l1.2 5.6z"/>
                <path d="M6 12a2 2 0 1 0 4 0 2 2 0 0 0-4 0zM1 12a2 2 0 1 0 4 0 2 2 0 0 0-4 0z"/>
            </svg>
            Nova Venda
        </a>

        <!-- Botão Novo Cliente -->
        <a href="{{ route('cliente.create') }}"
           class="flex items-center px-6 py-3 text-white bg-green-600 rounded-lg shadow-md hover:bg-green-700 transition duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-30 mr-2" fill="currentColor" viewBox="0 0 16 16">
                <path d="M6 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
                <path d="M8 9a5 5 0 0 0-8 0v1h8V9z"/>
                <path d="M15.5 5a.5.5 0 0 0-.5.5V7h-1.5a.5.5 0 0 0 0 1H15v1.5a.5.5 0 0 0 1 0V8h1.5a.5.5 0 0 0 0-1H16V5.5a.5.5 0 0 0-.5-.5z"/>
            </svg>
            Novo Cliente
        </a>
        <form id="formAberturaCaixa" action="{{ route('caixa.abrir') }}" method="POST">
            @csrf
            <button type="button" id="btnAbrirModalAbertura"
                    class="flex items-center px-6 py-3 text-white bg-green-600 rounded-lg shadow-md hover:bg-green-700 transition duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-30 mr-2" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8 7a.5.5 0 0 1 .5.5V9h1.5a.5.5 0 0 1 0 1H8.5v1.5a.5.5 0 0 1-1 0V10H6a.5.5 0 0 1 0-1h1.5V7.5A.5.5 0 0 1 8 7z"/>
                        <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .49.598l-1.5 7A.5.5 0 0 1 13 11H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM4.415 10h8.17l1.2-5.6H3.215l1.2 5.6z"/>
                        <path d="M6 12a2 2 0 1 0 4 0 2 2 0 0 0-4 0zM1 12a2 2 0 1 0 4 0 2 2 0 0 0-4 0z"/>
                    </svg>
                Abrir Caixa
            </button>
        </form>

        <div id="modalAbertura" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Confirmar Abertura de Caixa</h2>
                <p class="text-gray-600 mb-6">
                    Deseja realizar a abertura do caixa?<br>
                </p>
                <div class="flex justify-end gap-3">
                    <button id="btnCancelarAbertura"
                            class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 transition duration-200">
                        Cancelar
                    </button>
                    <button id="btnConfirmarAbertura"
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition duration-200">
                        Confirmar
                    </button>
                </div>
            </div>
        </div>

        <form id="formFechamentoCaixa" action="{{ route('caixa.fechar') }}" method="POST">
            @csrf
            <button type="button" id="btnAbrirModalFechamento"
                    class="flex items-center px-6 py-3 text-white bg-red-600 rounded-lg shadow-md hover:bg-green-700 transition duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-30 mr-2" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8 7a.5.5 0 0 1 .5.5V9h1.5a.5.5 0 0 1 0 1H8.5v1.5a.5.5 0 0 1-1 0V10H6a.5.5 0 0 1 0-1h1.5V7.5A.5.5 0 0 1 8 7z"/>
                        <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .49.598l-1.5 7A.5.5 0 0 1 13 11H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM4.415 10h8.17l1.2-5.6H3.215l1.2 5.6z"/>
                        <path d="M6 12a2 2 0 1 0 4 0 2 2 0 0 0-4 0zM1 12a2 2 0 1 0 4 0 2 2 0 0 0-4 0z"/>
                    </svg>
                Fechar Caixa
            </button>
        </form>

        <!-- Modal -->
        <div id="modalFechamento" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Confirmar Fechamento de Caixa</h2>
                <p class="text-gray-600 mb-6">
                    Tem certeza que deseja realizar o fechamento do caixa?<br>
                    Essa ação não poderá ser desfeita.
                </p>
                <div class="flex justify-end gap-3">
                    <button id="btnCancelarFechamento"
                            class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 transition duration-200">
                        Cancelar
                    </button>
                    <button id="btnConfirmarFechamento"
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition duration-200">
                        Confirmar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Total Diário -->
    <div class="flex justify-start mt-10">
        <x-layouts.cards.total-diario :total="$dashboardData['totalDiario'] ?? 0" />
    </div>
</section>

<div class="flex flex-wrap -mx-3 mb-2">
{{--    <div class="w-full md:w-3/12 px-3 mb-6 md:mb-0">--}}
{{--        <x-layouts.cards.sample-card-icon--}}
{{--            :icon="''"--}}
{{--            :content="'Servidores'"--}}
{{--            :title="$dashboardData['quantitativos']['servidores']"--}}
{{--            :route-action-text="'mais detalhes'"--}}
{{--            :route-action="route('servidor.index')">--}}
{{--        </x-layouts.cards.sample-card-icon>--}}
{{--    </div>--}}
{{--    <div class="w-full md:w-3/12 px-3 mb-6 md:mb-0">--}}
{{--        <x-layouts.cards.sample-card-icon--}}
{{--            :icon="''"--}}
{{--            :content="'Usuários'"--}}
{{--            :title="$dashboardData['quantitativos']['usuarios']"--}}
{{--            :route-action-text="'mais detalhes'"--}}
{{--            :route-action="route('usuario.index')">--}}
{{--        </x-layouts.cards.sample-card-icon>--}}
{{--    </div>--}}
{{--    <div class="w-full md:w-3/12 px-3 mb-6 md:mb-0">--}}
{{--        <x-layouts.cards.sample-card-icon--}}
{{--            :icon="''"--}}
{{--            :content="'Cargos'"--}}
{{--            :title="$dashboardData['quantitativos']['cargos']"--}}
{{--            :route-action-text="'mais detalhes'"--}}
{{--            :route-action="route('cargo.index')">--}}
{{--        </x-layouts.cards.sample-card-icon>--}}
{{--    </div>--}}
{{--    <div class="w-full md:w-3/12 px-3 mb-6 md:mb-0">--}}
{{--        <x-layouts.cards.sample-card-icon--}}
{{--            :icon="''"--}}
{{--            :content="'Fornecedores'"--}}
{{--            :title="$dashboardData['quantitativos']['fornecedores']"--}}
{{--            :route-action-text="'mais detalhes'"--}}
{{--            :route-action="route('fornecedor.index')">--}}
{{--        </x-layouts.cards.sample-card-icon>--}}
{{--    </div>--}}
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // --- FECHAMENTO DE CAIXA ---
        const modalFechamento = document.getElementById('modalFechamento');
        const btnAbrirFechamento = document.getElementById('btnAbrirModalFechamento');
        const btnCancelarFechamento = document.getElementById('btnCancelarFechamento');
        const btnConfirmarFechamento = document.getElementById('btnConfirmarFechamento');
        const formFechamento = document.getElementById('formFechamentoCaixa');

        if (btnAbrirFechamento) {
            btnAbrirFechamento.addEventListener('click', () => {
                modalFechamento.classList.remove('hidden');
            });
        }

        if (btnCancelarFechamento) {
            btnCancelarFechamento.addEventListener('click', () => {
                modalFechamento.classList.add('hidden');
            });
        }

        if (btnConfirmarFechamento) {
            btnConfirmarFechamento.addEventListener('click', () => {
                modalFechamento.classList.add('hidden');
                formFechamento.submit();
            });
        }

        if (modalFechamento) {
            modalFechamento.addEventListener('click', (e) => {
                if (e.target === modalFechamento) modalFechamento.classList.add('hidden');
            });
        }

        // --- ABERTURA DE CAIXA ---
        const modalAbertura = document.getElementById('modalAbertura');
        const btnAbrirAbertura = document.getElementById('btnAbrirModalAbertura');
        const btnCancelarAbertura = document.getElementById('btnCancelarAbertura');
        const btnConfirmarAbertura = document.getElementById('btnConfirmarAbertura');
        const formAbertura = document.getElementById('formAberturaCaixa');

        if (btnAbrirAbertura) {
            btnAbrirAbertura.addEventListener('click', () => {
                modalAbertura.classList.remove('hidden');
            });
        }

        if (btnCancelarAbertura) {
            btnCancelarAbertura.addEventListener('click', () => {
                modalAbertura.classList.add('hidden');
            });
        }

        if (btnConfirmarAbertura) {
            btnConfirmarAbertura.addEventListener('click', () => {
                modalAbertura.classList.add('hidden');
                formAbertura.submit();
            });
        }

        if (modalAbertura) {
            modalAbertura.addEventListener('click', (e) => {
                if (e.target === modalAbertura) modalAbertura.classList.add('hidden');
            });
        }
    });
</script>
@endsection
