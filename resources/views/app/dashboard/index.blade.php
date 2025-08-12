@extends('app.layouts.app')

@section('title', 'Dashboard')

<x-layouts.headers.create-header :title="'Dashboard'"/>

@section('content')
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
