<div class="container mx-auto p-4">
    <div class="bg-white text-gray-600 rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-700">Finalizar Venda</h1>
            <button 
                wire:click="voltarParaCarrinho"
                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition duration-200"
            >
                Voltar ao Carrinho
            </button>
        </div>

        {{-- Alertas de Sucesso/Erro --}}
        @if(session()->has('success'))
            <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                <div class="font-bold">{{ session('success.title') }}</div>
                <div>{{ session('success.message') }}</div>
            </div>
        @endif

        @if(session()->has('error'))
            <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                <div class="font-bold">{{ session('error.title') }}</div>
                <div>{{ session('error.message') }}</div>
            </div>
        @endif

        {{-- Informações do Cliente --}}
        @if($cliente)
        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-blue-800 mb-2">Cliente da Venda</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <span class="font-medium text-blue-700">Nome:</span>
                            <span class="text-blue-900 ml-2">{{ $cliente['nome'] }}</span>
                        </div>
                        <div>
                            <span class="font-medium text-blue-700">CPF:</span>
                            <span class="text-blue-900 ml-2">{{ $cliente['cpf'] }}</span>
                        </div>
                        <div class="md:col-span-3">
                            <span class="font-medium text-blue-700">Total de Vendas Anteriores:</span>
                            <span class="text-green-600 font-semibold ml-2">{{ $cliente['total_vendas'] }} venda(s)</span>
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                        Cliente Selecionado
                    </span>
                </div>
            </div>
        </div>
        @else
        <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <span class="text-yellow-700 font-medium">Venda sem cliente específico</span>
            </div>
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Resumo da Venda --}}
            <div class="lg:col-span-1">
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <h2 class="text-lg font-semibold mb-4 text-gray-800">Resumo da Venda</h2>
                    
                    <div class="space-y-3 max-h-80 overflow-y-auto pr-2">
                        @foreach($carrinho as $item)
                        <div class="border-b border-gray-200 pb-3 last:border-b-0">
                            <div class="font-semibold text-gray-900">{{ $item['nome'] }}</div>
                            <div class="text-sm text-gray-600">
                                {{ $item['quantidade'] }} x R$ {{ number_format($item['preco'], 2, ',', '.') }}
                            </div>
                            @if(isset($item['desconto']) && $item['desconto'] > 0)
                            <div class="text-xs text-red-600">
                                Desconto: {{ $item['tipo_desconto'] === 'percentual' ? $item['desconto'] . '%' : 'R$ ' . number_format($item['desconto'], 2, ',', '.') }}
                            </div>
                            @endif
                            <div class="text-sm font-semibold text-gray-900">
                                R$ {{ number_format($item['subtotal'], 2, ',', '.') }}
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="border-t border-gray-300 pt-4 mt-4 space-y-2">
                        @php
                            $subtotal = array_sum(array_column($carrinho, 'subtotal'));
                            $descontoCalculado = $tipoDescontoGeral === 'percentual' 
                                ? ($subtotal * $descontoGeral) / 100 
                                : $descontoGeral;
                        @endphp
                        
                        <div class="flex justify-between text-gray-700">
                            <span>Subtotal:</span>
                            <span>R$ {{ number_format($subtotal, 2, ',', '.') }}</span>
                        </div>
                        
                        @if($descontoGeral > 0)
                        <div class="flex justify-between text-red-600">
                            <span>Desconto Geral:</span>
                            <span>
                                - R$ {{ number_format($descontoCalculado, 2, ',', '.') }}
                                @if($tipoDescontoGeral === 'percentual')
                                <span class="text-xs">({{ $descontoGeral }}%)</span>
                                @endif
                            </span>
                        </div>
                        @endif
                        
                        <div class="flex justify-between font-semibold text-lg border-t border-gray-300 pt-2 text-gray-900">
                            <span>Total:</span>
                            <span>R$ {{ number_format($total, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Forma de Pagamento --}}
            <div class="lg:col-span-2">
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h2 class="text-lg font-semibold mb-4 text-gray-800">Forma de Pagamento</h2>
                    
                    <div class="space-y-6">
                        {{-- Select Forma de Pagamento --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Selecione a forma de pagamento: *
                            </label>
                            <select 
                                wire:model.live="formaPagamento"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                            >
                                <option value="">Selecione uma opção</option>
                                @foreach(App\Enums\FormaPagamentoEnum::getInstances() as $forma)
                                <option value="{{ $forma->value }}">
                                    {{ $forma->value }}
                                </option>
                                @endforeach
                            </select>
                            @error('formaPagamento')
                                <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Campo para Dinheiro --}}
                        @if($ehDinheiro)
                        <div class="p-4 bg-green-50 border border-green-200 rounded-lg space-y-3">
                            <label class="block text-sm font-medium text-gray-700">
                                Pagamento em Dinheiro
                            </label>
                            
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Valor Recebido: *</label>
                                <input 
                                    type="text" 
                                    wire:model.live="valorRecebido"
                                    class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                    placeholder="R$ 0,00"
                                    x-mask:dynamic="$money($input, ',', '.', 2)"
                                >
                                @error('valorRecebido')
                                    <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            @if($troco > 0)
                            <div class="bg-white p-3 rounded border border-green-300">
                                <strong class="text-lg text-green-700">Troco: R$ {{ number_format($troco, 2, ',', '.') }}</strong>
                            </div>
                            @endif
                            
                            @if($valorRecebido > 0 && $valorRecebido < $total)
                            <div class="bg-yellow-50 p-3 rounded border border-yellow-300">
                                <strong class="text-lg text-yellow-700">Valor insuficiente. Faltam: R$ {{ number_format($total - $valorRecebido, 2, ',', '.') }}</strong>
                            </div>
                            @endif
                        </div>
                        @endif

                        {{-- Campos para Cartão --}}
                        @if($ehCartao)
                        <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg space-y-4">
                            <label class="block text-sm font-medium text-gray-700">
                                Pagamento com Cartão
                            </label>
                            
                            {{-- Bandeira do Cartão --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Selecione a bandeira do cartão: *
                                </label>
                                <select 
                                    wire:model="bandeiraCartao"
                                    class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                >
                                    <option value="">Selecione a bandeira</option>
                                    @foreach(App\Enums\BandeiraCartaoEnum::getInstances() as $bandeira)
                                    <option value="{{ $bandeira->value }}">
                                        {{ $bandeira->value }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('bandeiraCartao')
                                    <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Parcelamento --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Número de Parcelas: *
                                </label>
                                <select 
                                    wire:model.live="parcelas"
                                    class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                >
                                    @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}">{{ $i }}x de R$ {{ number_format($total / $i, 2, ',', '.') }}</option>
                                    @endfor
                                </select>
                                
                                @if($parcelas > 1)
                                <div class="mt-2 bg-white p-3 rounded border border-blue-200">
                                    <strong class="text-blue-700">Valor da Parcela: R$ {{ number_format($valorParcela, 2, ',', '.') }}</strong>
                                </div>
                                @endif
                                
                                @error('parcelas')
                                    <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        @endif

                        {{-- Observações --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Observações</label>
                            <textarea 
                                wire:model="observacoes"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                                rows="3"
                                placeholder="Observações adicionais da venda..."
                            ></textarea>
                        </div>

                        {{-- Botão Finalizar --}}
                        <button 
                            wire:click="processarPagamento"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed"
                            class="w-full py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 font-semibold mt-2 transition duration-200 flex items-center justify-center"
                        >
                            <span wire:loading.remove>
                                @if($cliente)
                                    Finalizar Venda para {{ $cliente['nome'] }}
                                @else
                                    Finalizar Venda
                                @endif
                            </span>
                            <span wire:loading>
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Processando...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/thermal-printer.js') }}"></script>