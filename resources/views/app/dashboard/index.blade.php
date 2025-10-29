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
@endsection
