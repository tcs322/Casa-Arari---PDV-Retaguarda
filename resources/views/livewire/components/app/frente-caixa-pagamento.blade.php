<div class="container mx-auto p-4">
    <div class="bg-white text-gray-600 rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-700">Finalizar Venda</h1>
            <button 
                wire:click="voltarParaCarrinho"
                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400"
            >
                Voltar ao Carrinho
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Resumo da Venda --}}
            <div class="lg:col-span-1">
                <div class="bg-gray-50 rounded-lg p-4">
                    <h2 class="text-lg font-semibold mb-4">Resumo da Venda</h2>
                    
                    <div class="space-y-3">
                        @foreach($carrinho as $item)
                        <div class="border-b pb-3">
                            <div class="font-semibold">{{ $item['nome'] }}</div>
                            <div class="text-sm text-gray-600">
                                {{ $item['quantidade'] }} x R$ {{ number_format($item['preco'], 2, ',', '.') }}
                            </div>
                            <div class="text-sm font-semibold">
                                R$ {{ number_format($item['subtotal'], 2, ',', '.') }}
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="border-t pt-4 mt-4 space-y-2">
                        <div class="flex justify-between">
                            <span>Subtotal:</span>
                            <span>R$ {{ number_format(array_sum(array_column($carrinho, 'subtotal')), 2, ',', '.') }}</span>
                        </div>
                        
                        @if($descontoGeral > 0)
                        <div class="flex justify-between text-red-600">
                            <span>Desconto Geral:</span>
                            <span>
                                {{ $tipoDescontoGeral === 'percentual' ? $descontoGeral . '%' : 'R$ ' . number_format($descontoGeral, 2, ',', '.') }}
                            </span>
                        </div>
                        @endif
                        
                        <div class="flex justify-between font-semibold text-lg border-t pt-2">
                            <span>Total:</span>
                            <span>R$ {{ number_format($total, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Forma de Pagamento --}}
            <div class="lg:col-span-2">
                <div class="bg-white border rounded-lg p-6">
                    <h2 class="text-lg font-semibold mb-4">Forma de Pagamento</h2>
                    
                    <div class="space-y-4">
                        {{-- Select Forma de Pagamento --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Selecione a forma de pagamento:
                            </label>
                            <select 
                                wire:model.live="formaPagamento"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                                <option value="">Selecione uma opção</option>
                                @foreach(App\Enums\FormaPagamentoEnum::getInstances() as $forma)
                                <option value="{{ $forma->value }}">
                                    {{ $forma->value }}
                                </option>
                                @endforeach
                            </select>
                            @error('formaPagamento')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Campo para Dinheiro --}}
                        @if($ehDinheiro)
                        <div class="mt-4 p-4 bg-green-50 rounded-lg">
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Pagamento em Dinheiro
                            </label>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm text-gray-600 mb-1">Valor Recebido:</label>
                                    <input 
                                        type="text" 
                                        wire:model.live="valorRecebido"
                                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                                        placeholder="R$ 0,00"
                                    >
                                    @error('valorRecebido')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="bg-white p-3 rounded border">
                                    <strong class="text-lg">Troco: R$ {{ number_format($troco, 2, ',', '.') }}</strong>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Campos para Cartão --}}
                        @if($ehCartao)
                        <div class="mt-4 p-4 bg-blue-50 rounded-lg space-y-4">
                            {{-- Bandeira do Cartão --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Selecione a bandeira do cartão:
                                </label>
                                <select 
                                    wire:model="bandeiraCartao"
                                    class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                >
                                    <option value="">Selecione a bandeira</option>
                                    @foreach(App\Enums\BandeiraCartaoEnum::getInstances() as $bandeira)
                                    <option value="{{ $bandeira->value }}">
                                        {{ $bandeira->value }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('bandeiraCartao')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Parcelamento --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Número de Parcelas:
                                </label>
                                <select 
                                    wire:model.live="parcelas"
                                    class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                >
                                    @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}">{{ $i }}x</option>
                                    @endfor
                                </select>
                                @if($parcelas > 1)
                                <div class="mt-2 bg-white p-3 rounded border">
                                    <strong>Valor da Parcela: R$ {{ number_format($valorParcela, 2, ',', '.') }}</strong>
                                </div>
                                @endif
                                @error('parcelas')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        @endif

                        {{-- Observações --}}
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Observações</label>
                            <textarea 
                                wire:model="observacoes"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                rows="3"
                                placeholder="Observações adicionais da venda..."
                            ></textarea>
                        </div>

                        {{-- Botão Finalizar --}}
                        <button 
                            wire:click="processarPagamento"
                            class="w-full py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 font-semibold mt-6"
                        >
                            Processar Pagamento
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>