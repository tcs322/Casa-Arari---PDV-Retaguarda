<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-6">Frente de Caixa</h1>

    <!-- Mensagens de Flash -->
    @if (session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            <strong>Sucesso!</strong> {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <strong>Erro!</strong> {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        {{-- Coluna de Busca e Produtos --}}
        <div class="lg:col-span-1">
            {{-- Seção de Busca de Cliente --}}
            <div class="bg-white text-gray-700 rounded-lg shadow p-4 mb-4">
                <h2 class="text-lg font-semibold mb-4">Cliente</h2>
                
                @if($clienteSelecionado)
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                        <div class="flex justify-between items-start mb-2">
                            <div class="flex-1">
                                <div class="font-semibold text-blue-800">{{ $clienteSelecionado['nome'] }}</div>
                                <div class="text-sm text-blue-600">CPF: {{ $clienteSelecionado['cpf'] }}</div>
                                <div class="text-sm text-green-600 font-medium mt-1">
                                    Total de Vendas: {{ $clienteSelecionado['total_vendas'] }}
                                </div>
                            </div>
                            <button 
                                wire:click="removerCliente"
                                class="p-1 text-red-500 hover:text-red-700 transition-colors"
                                title="Remover cliente"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                @else
                    <input 
                        type="text" 
                        wire:model="searchCliente" 
                        wire:keydown.debounce.500ms="buscarClientes"
                        placeholder="Buscar cliente por nome, email ou CPF..."
                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >

                    {{-- Resultados da Busca de Clientes --}}
                    @if(!empty($clientesEncontrados))
                        <div class="mt-4 space-y-2 max-h-48 overflow-y-auto">
                            @foreach($clientesEncontrados as $cliente)
                                <div 
                                    class="p-3 border border-gray-200 rounded-lg hover:bg-blue-50 cursor-pointer transition-colors"
                                    wire:click="selecionarCliente({{ json_encode($cliente) }})"
                                    wire:key="cliente-{{ $cliente['uuid'] }}"
                                >
                                    <div class="font-semibold">{{ $cliente['nome'] }}</div>
                                    <div class="text-sm text-gray-600">CPF: {{ $cliente['cpf'] }}</div>
                                    <div class="text-sm text-green-600 font-medium">
                                        Vendas: {{ $cliente['total_vendas'] }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Loading State para Clientes --}}
                    @if($searchCliente && empty($clientesEncontrados))
                        <div class="mt-4 text-center text-gray-500">
                            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500 mx-auto"></div>
                            <p class="mt-2">Buscando clientes...</p>
                        </div>
                    @endif
                @endif
            </div>

            {{-- Seção de Busca de Produtos --}}
            <div class="bg-white text-gray-700 rounded-lg shadow p-4">
                <h2 class="text-lg font-semibold mb-4">Buscar Produtos</h2>
                
                <input 
                    type="text" 
                    wire:model="search" 
                    wire:keydown.debounce.500ms="buscarProdutos"
                    placeholder="Digite o nome ou código do produto..."
                    class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    autofocus
                >

                {{-- Resultados da Busca --}}
                @if(!empty($produtosEncontrados))
                    <div class="mt-4 space-y-2 max-h-96 overflow-y-auto">
                        @foreach($produtosEncontrados as $produto)
                            <div 
                                class="p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors"
                                wire:click="adicionarAoCarrinho('{{ $produto['id'] }}')"
                                wire:key="produto-{{ $produto['id'] }}"
                            >
                                <div class="font-semibold">{{ $produto['nome_titulo'] ?? $produto['nome'] }}</div>
                                <div class="text-sm text-gray-600">Código: {{ $produto['codigo'] }}</div>
                                <div class="text-sm text-green-600">R$ {{ number_format($produto['preco'], 2, ',', '.') }}</div>
                                <div class="text-sm text-gray-500">Estoque: {{ $produto['estoque'] }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Loading State --}}
                @if($search && empty($produtosEncontrados))
                    <div class="mt-4 text-center text-gray-500">
                        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500 mx-auto"></div>
                        <p class="mt-2">Buscando produtos...</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Resto do código do carrinho permanece igual --}}
        <div class="lg:col-span-2">
            <div class="bg-white text-gray-700 rounded-lg shadow p-4">
                <h2 class="text-lg font-semibold mb-4">Carrinho de Venda</h2>

                @if(empty($carrinho))
                    <div class="text-center py-8">
                        <div class="text-6xl mb-4">🛒</div>
                        <p class="text-gray-500 text-lg">Nenhum produto no carrinho</p>
                        <p class="text-gray-400 text-sm mt-2">Adicione produtos usando a busca ao lado</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($carrinho as $index => $item)
                            <div class="border border-gray-200 rounded-lg p-3 transition-shadow hover:shadow-md">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex-1">
                                        <div class="font-semibold">{{ $item['nome'] }}</div>
                                        <div class="text-sm text-gray-600">Código: {{ $item['codigo'] }}</div>
                                        <div class="text-sm text-green-600">
                                            R$ {{ number_format($item['preco'], 2, ',', '.') }} cada
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center space-x-2">
                                        <button 
                                            wire:click="atualizarQuantidade({{ $index }}, {{ $item['quantidade'] - 1 }})"
                                            class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center hover:bg-gray-300 transition-colors touch-element"
                                            title="Diminuir quantidade"
                                        >
                                            -
                                        </button>
                                        
                                        <input 
                                            type="number" 
                                            wire:model="carrinho.{{ $index }}.quantidade"
                                            wire:change="atualizarQuantidade({{ $index }}, $event.target.value)"
                                            min="1"
                                            class="w-16 p-1 border border-gray-300 rounded text-center touch-element"
                                            aria-label="Quantidade do produto"
                                        >
                                        
                                        <button 
                                            wire:click="atualizarQuantidade({{ $index }}, {{ $item['quantidade'] + 1 }})"
                                            class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center hover:bg-blue-600 transition-colors touch-element"
                                            title="Aumentar quantidade"
                                        >
                                            +
                                        </button>
                                    </div>
                                    
                                    <div class="text-right min-w-[120px]">
                                        <div class="font-semibold text-lg">
                                            R$ {{ number_format($item['subtotal'], 2, ',', '.') }}
                                        </div>
                                        <div class="text-sm text-gray-600">
                                            {{ $item['quantidade'] }} un
                                        </div>
                                    </div>
                                    
                                    <button 
                                        wire:click="removerDoCarrinho({{ $index }})"
                                        class="ml-4 p-2 text-red-500 hover:text-red-700 transition-colors touch-element"
                                        title="Remover produto"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>

                                {{-- Desconto Individual --}}
                                <div class="flex items-center space-x-2 mt-2 pt-2 border-t border-gray-100">
                                    <span class="text-sm text-gray-600 whitespace-nowrap">Desconto:</span>
                                    <input 
                                        type="number" 
                                        wire:model="carrinho.{{ $index }}.desconto"
                                        wire:change="aplicarDescontoIndividual({{ $index }}, $event.target.value, '{{ $item['tipo_desconto'] }}')"
                                        min="0"
                                        :max="item.tipo_desconto === 'percentual' ? 100 : item.subtotal"
                                        class="w-20 p-1 border border-gray-300 rounded text-center touch-element"
                                        placeholder="0"
                                    >
                                    <select 
                                        wire:model="carrinho.{{ $index }}.tipo_desconto"
                                        wire:change="aplicarDescontoIndividual({{ $index }}, {{ $item['desconto'] }}, $event.target.value)"
                                        class="p-1 border border-gray-300 rounded touch-element"
                                    >
                                        <option value="percentual">%</option>
                                        <option value="valor">R$</option>
                                    </select>
                                    
                                    @if($item['desconto'] > 0)
                                        <span class="text-sm text-red-600 ml-2">
                                            -{{ $item['tipo_desconto'] === 'percentual' ? $item['desconto'] . '%' : 'R$ ' . number_format($item['desconto'], 2, ',', '.') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Total e Finalização --}}
                    <div class="mt-6 pt-4 border-t border-gray-200">
                        {{-- Desconto Geral --}}
                        <div class="flex items-center space-x-2 mb-4 p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm text-gray-600 font-medium whitespace-nowrap">Desconto Geral:</span>
                            <input 
                                type="number" 
                                wire:model="descontoGeral"
                                wire:change="aplicarDescontoGeral"
                                min="0"
                                :max="tipoDescontoGeral === 'percentual' ? 100 : totalCarrinho"
                                class="w-20 p-2 border border-gray-300 rounded text-center touch-element"
                                placeholder="0"
                            >
                            <select 
                                wire:model="tipoDescontoGeral"
                                wire:change="aplicarDescontoGeral"
                                class="p-2 border border-gray-300 rounded touch-element"
                            >
                                <option value="percentual">%</option>
                                <option value="valor">R$</option>
                            </select>
                            
                            @if($descontoGeral > 0)
                                <span class="text-sm text-red-600 font-medium ml-2">
                                    -{{ $tipoDescontoGeral === 'percentual' ? $descontoGeral . '%' : 'R$ ' . number_format($descontoGeral, 2, ',', '.') }}
                                </span>
                            @endif
                        </div>

                        <div class="flex justify-between items-center mb-4 p-3 bg-green-50 rounded-lg">
                            <span class="text-xl font-semibold text-gray-800">Total:</span>
                            <span class="text-2xl font-bold text-green-600">
                                R$ {{ number_format($totalCarrinho, 2, ',', '.') }}
                            </span>
                        </div>
                        
                        <button 
                            wire:click="finalizarVenda"
                            wire:loading.attr="disabled"
                            class="w-full py-4 bg-green-500 text-white rounded-lg hover:bg-green-600 font-semibold text-lg transition-colors touch-element disabled:bg-gray-400 disabled:cursor-not-allowed"
                        >
                            <span wire:loading.remove>Finalizar Venda</span>
                            <span wire:loading>
                                <div class="flex items-center justify-center gap-2">
                                    <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
                                    Processando...
                                </div>
                            </span>
                        </button>

                        <div class="mt-2 text-center">
                            <button 
                                wire:click="$dispatch('open-modal', 'confirmar-limpar-carrinho')"
                                class="text-sm text-red-500 hover:text-red-700 transition-colors"
                            >
                                Limpar Carrinho
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Coluna de Resumo --}}
        <div class="lg:col-span-1">
            <div class="bg-white text-gray-700 rounded-lg shadow p-4 sticky top-4">
                <h2 class="text-lg font-semibold mb-4">Resumo da Venda</h2>
                
                <div class="space-y-3">
                    {{-- Informações do Cliente no Resumo --}}
                    @if($clienteSelecionado)
                    <div class="bg-blue-50 rounded-lg p-3 mb-3">
                        <div class="text-sm font-semibold text-blue-800 mb-1">Cliente:</div>
                        <div class="text-sm text-blue-700">{{ $clienteSelecionado['nome'] }}</div>
                        <div class="text-xs text-blue-600 mt-1">
                            Vendas anteriores: {{ $clienteSelecionado['total_vendas'] }}
                        </div>
                    </div>
                    @endif
                    
                    <div class="flex justify-between items-center py-2">
                        <span class="text-gray-600">Itens no Carrinho:</span>
                        <span class="font-semibold">{{ count($carrinho) }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center py-2 border-t">
                        <span class="text-gray-600">Subtotal:</span>
                        <span class="font-semibold">R$ {{ number_format(array_sum(array_column($carrinho, 'subtotal')), 2, ',', '.') }}</span>
                    </div>
                    
                    @if($descontoGeral > 0)
                    <div class="flex justify-between items-center py-2 text-red-600">
                        <span>Desconto Geral:</span>
                        <span class="font-semibold">
                            {{ $tipoDescontoGeral === 'percentual' ? $descontoGeral . '%' : 'R$ ' . number_format($descontoGeral, 2, ',', '.') }}
                        </span>
                    </div>
                    @endif
                    
                    <div class="flex justify-between items-center py-2 border-t border-green-200 bg-green-50 rounded-lg px-3">
                        <span class="text-lg font-semibold text-gray-800">Total:</span>
                        <span class="text-xl font-bold text-green-600">
                            R$ {{ number_format($totalCarrinho, 2, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação para Limpar Carrinho -->
@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('open-modal', (event) => {
            if (event === 'confirmar-limpar-carrinho') {
                if (confirm('Tem certeza que deseja limpar o carrinho? Todos os itens serão removidos.')) {
                    @this.limparCarrinho();
                }
            }
        });
    });
</script>
@endpush