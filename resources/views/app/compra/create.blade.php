@extends('app.layouts.app')

@section('breadcrumb')
    {{ Breadcrumbs::render('setor.create') }}
@endsection

@section('title', 'Nova Compra')

<x-layouts.headers.create-header :title="'Nova Compra'"/>

@section('content')
    @include('components.alerts.form-errors')
    <div id="accordion-collapse" data-accordion="collapse">
        <h2 id="accordion-collapse-heading-2">
            <button type="button" class="flex items-center justify-between w-full p-5 font-medium rtl:text-right text-gray-500 border border-b-0 border-gray-200 focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-800 dark:border-gray-700 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 gap-3" data-accordion-target="#accordion-collapse-body-2" aria-expanded="false" aria-controls="accordion-collapse-body-2">
                <span>Lote </span>
                <svg data-accordion-icon class="w-3 h-3 rotate-180 shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5 5 1 1 5"/>
                </svg>
            </button>
        </h2>
        <div id="accordion-collapse-body-2" class="hidden" aria-labelledby="accordion-collapse-heading-2">
            <div class="p-5 border border-b-0 border-gray-200 dark:border-gray-700">
                <div class="p-4 mb-2 text-sm text-gray-800 rounded-lg bg-gray-50 dark:bg-gray-800 dark:text-gray-300" role="alert">
                    <small>Plano Pagamento: <b>{{ $formData['lote']['plano_pagamento']['descricao'] }}</b></small>
                    <ul>
                        @foreach($formData['lote']['plano_pagamento']['condicoes_pagamento'] as $condicaoPagamento)
                            <li>
                                <p>
                                    <small>Parcelas: <b>{{$condicaoPagamento['qtd_parcelas']}}</b></small> |
                                    <small>Repetições: <b>{{$condicaoPagamento['repeticoes']}}</b></small> |
                                    <small>Comissão Venda: <b>{{$condicaoPagamento['percentual_comissao_vendedor']}} %</b></small> |
                                    <small>Comissão Compra: <b>{{$condicaoPagamento['percentual_comissao_comprador']}} %</b></small>
                                </p>
                            </li>
                        @endforeach
                    </ul>
                    <br>
                    <small>Valor estimado para o lote: </small> <x-layouts.badges.info-money
                        textLength="sm"
                        :convert="false"
                        :value="$formData['lote']['valor_estimado']"
                    />
                </div>
                <ul role="list" class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($formData['lote']['itens'] as $index => $item)
                        <div class="p-2 mb-2 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400" role="alert">
                            <li class="sm:py-4">
                                <div class="flex items-center">
                                    <div class="flex-1 min-w-0 ms-4">
                                        <p class="text-sm font-medium text-gray-900 truncate dark:text-white">
                                            {{$item['descricao']}}
                                        </p>
                                        <p class="text-sm text-gray-500 truncate dark:text-gray-400">
                                            <small>Gênero: <b>{{ \App\Enums\GeneroLoteItemEnum::getDescription((int)$item['genero']) }}</b></small> |
                                            <small>Espécie: <b>{{$item['especie']['nome']}}</b></small> |
                                            <small>Raça: <b>{{$item['raca']['nome']}}</b></small>
                                        </p>
                                        <p class="text-sm font-medium text-gray-900 truncate dark:text-white">
                                            <small>Valor estimado: </small> <x-layouts.badges.info-money
                                                textLength="sm"
                                                :convert="false"
                                                :value="$item['valor_estimado']"
                                            />
                                        </p>
                                    </div>
                                </div>
                            </li>
                        </div>
                    @empty
                        <div class="p-4 mb-4 text-sm text-yellow-800 rounded-lg bg-yellow-50 dark:bg-gray-800 dark:text-yellow-300" role="alert">
                            Nenhum registro adicionado até o momento, <b>preencha o formulário</b> e clique em <b>adicionar</b> para incluir itens no lote
                        </div>
                    @endforelse
                </ul>
            </div>
        </div>
        <hr>
    </div>
    @livewire('components.app.compra-create', [$formData])
@endsection
