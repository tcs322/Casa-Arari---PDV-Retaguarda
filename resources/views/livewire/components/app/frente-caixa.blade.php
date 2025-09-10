<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-6">Frente de Caixa</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Coluna de Busca e Produtos --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow p-4">
                <h2 class="text-lg font-semibold mb-4 text-gray-700">Buscar Produtos</h2>
                
                <input 
                    type="text" 
                    wire:model="search" 
                    wire:keydown.debounce.500ms="buscarProdutos"
                    placeholder="Digite o nome ou código do produto..."
                    class="w-full p-3 border border-gray-300 rounded-lg text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    autofocus
                >

                {{-- Resultados da Busca --}}
                @if(!empty($produtosEncontrados))
                    <div class="mt-4 space-y-2 max-h-96 overflow-y-auto">
                        @foreach($produtosEncontrados as $produto)
                            <div 
                                class="p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer"
                                wire:click="adicionarAoCarrinho('{{ $produto['id'] }}')"
                            >
                                <div class="font-semibold text-gray-700">{{ $produto['nome_titulo'] }}</div>
                                <div class="text-sm text-gray-600">Código: {{ $produto['codigo'] }}</div>
                                <div class="text-sm text-green-600">R$ {{ number_format($produto['preco'], 2, ',', '.') }}</div>
                                <div class="text-sm text-gray-500">Estoque: {{ $produto['estoque'] }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Coluna do Carrinho --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow p-4">
                <h2 class="text-lg font-semibold mb-4 text-gray-700">Carrinho de Venda</h2>

                @if(empty($carrinho))
                    <p class="text-gray-500 text-center py-8">Nenhum produto no carrinho</p>
                @else
                    <div class="space-y-3">
                        @foreach($carrinho as $index => $item)
                            <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                <div class="flex-1">
                                    <div class="font-semibold text-gray-700">{{ $item['nome'] }}</div>
                                    <div class="text-sm text-gray-600">Código: {{ $item['codigo'] }}</div>
                                </div>
                                
                                <div class="flex items-center space-x-2">
                                    <button 
                                        wire:click="atualizarQuantidade({{ $index }}, {{ $item['quantidade'] - 1 }})"
                                        class="px-2 py-1 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300"
                                    >
                                        -
                                    </button>
                                    
                                    <input 
                                        type="number" 
                                        wire:model="carrinho.{{ $index }}.quantidade"
                                        wire:change="atualizarQuantidade({{ $index }}, $event.target.value)"
                                        min="1"
                                        class="w-16 p-1 border text-gray-700 border-gray-300 rounded text-center"
                                    >
                                    
                                    <button 
                                        wire:click="atualizarQuantidade({{ $index }}, {{ $item['quantidade'] + 1 }})"
                                        class="px-2 py-1 bg-gray-200 rounded-lg hover:bg-gray-300"
                                    >
                                        +
                                    </button>
                                </div>
                                
                                <div class="text-right">
                                    <div class="font-semibold text-gray-700">R$ {{ number_format($item['subtotal'], 2, ',', '.') }}</div>
                                    <div class="text-sm text-gray-600">R$ {{ number_format($item['preco'], 2, ',', '.') }} un</div>
                                </div>
                                
                                <button 
                                    wire:click="removerDoCarrinho({{ $index }})"
                                    class="ml-4 p-2 text-red-500 hover:text-red-700"
                                >
                                    ✕
                                </button>
                            </div>
                        @endforeach
                    </div>

                    {{-- Total e Finalização --}}
                    <div class="mt-6 pt-4 border-t border-gray-200">
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-xl text-gray-700 font-semibold">Total:</span>
                            <span class="text-xl text-gray-700 font-semibold">R$ {{ number_format($totalCarrinho, 2, ',', '.') }}</span>
                        </div>
                        
                        <button 
                            wire:click="finalizarVenda"
                            class="w-full py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 font-semibold"
                        >
                            Finalizar Venda
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>