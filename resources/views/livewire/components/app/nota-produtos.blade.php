<div>
    <!-- Exibe todos os erros no topo do formulário -->
    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <strong class="font-bold">Erros encontrados:</strong>
            <ul class="mt-1 list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form wire:submit.prevent="salvar">
        {{-- Upload XML --}}
        <div>
            <label for="xmlFile" class="block text-sm font-medium text-gray-700">Arquivo XML:</label>
            <input type="file" id="xmlFile" wire:model="xmlFile" accept=".xml" 
                   class="mt-1 block w-full text-sm text-gray-500">
            @error('xmlFile') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        {{-- ✅ NOVO: Tipo da Nota --}}
        @if($produtos)
            <div class="mt-6 bg-blue-50 p-4 rounded border border-blue-200">
                <h3 class="text-lg font-bold text-blue-800 mb-3">Classificação da Nota</h3>
                
                <div class="mb-4">
                    <label for="tipo_nota" class="block text-sm font-medium text-blue-700 mb-2">
                        Tipo da Nota Fiscal *
                    </label>
                    <select 
                        wire:model="tipo_nota" 
                        id="tipo_nota"
                        class="block w-full pl-3 pr-10 py-2 text-gray-900 text-base border-blue-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                        required
                    >
                        <option value="">Selecione o tipo da nota...</option>
                        @foreach($tiposNota as $tipo)
                            <option value="{{ $tipo->value }}">
                                {{ $tipo->description ?? $tipo->value }}
                            </option>
                        @endforeach
                    </select>
                    @error('tipo_nota')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    
                    <!-- Dica sobre detecção automática -->
                    @if($tipo_nota)
                        <p class="mt-2 text-sm text-blue-600">
                            ✅ Tipo detectado automaticamente. Verifique se está correto para aplicar a tributação adequada.
                        </p>
                    @else
                        <p class="mt-2 text-sm text-blue-600">
                            ℹ️ Selecione o tipo da nota para aplicar a tributação correta aos produtos.
                        </p>
                    @endif
                </div>
            </div>
        @endif

        {{-- Informações da Nota --}}
        @if($notaInfo['numero'] || $notaInfo['valor'] || $notaInfo['fornecedor'])
        <div class="bg-gray-100 p-4 rounded mb-6 mt-6">
            <h3 class="text-xl text-gray-900 font-bold mb-4">Informações da Nota</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="numero" class="block text-sm font-medium text-gray-700">Número da Nota</label>
                    <input type="text" id="numero" value="{{ $notaInfo['numero'] ?? '' }}" 
                           class="w-full p-2 border rounded text-black bg-gray-50" readonly>
                    <!-- Erro específico para número da nota -->
                    @error('numero_nota') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="valor" class="block text-sm font-medium text-gray-700">Valor Total</label>
                    <input type="text" id="valor" value="{{ $notaInfo['valor'] ?? '' }}" 
                           class="w-full p-2 border rounded text-black bg-gray-50" readonly>
                </div>

                <div>
                    <label for="fornecedor" class="block text-sm font-medium text-gray-700">Fornecedor</label>
                    <input type="text" id="fornecedor" value="{{ $notaInfo['fornecedor'] ?? '' }}" 
                           class="w-full p-2 border rounded text-black bg-gray-50" readonly>
                </div>
            </div>
        </div>
        @endif

        {{-- Produtos --}}
        @if (!empty($produtos))
            <div class="space-y-4 mt-6">
                <h3 class="text-xl font-bold mb-4">Produtos da Nota</h3>
                
                {{-- ✅ Aviso sobre tributação --}}
                @if($tipo_nota)
                    <div class="bg-green-50 border border-green-200 rounded p-3 mb-4">
                        <p class="text-green-700 text-sm">
                            📊 <strong>Tributação aplicada:</strong> 
                            Os produtos serão cadastrados com tributação de 
                            <strong>{{ $tipo_nota }}</strong>
                        </p>
                    </div>
                @else
                    <div class="bg-yellow-50 border border-yellow-200 rounded p-3 mb-4">
                        <p class="text-yellow-700 text-sm">
                            ⚠️ <strong>Atenção:</strong> 
                            Selecione o tipo da nota acima para aplicar a tributação correta aos produtos.
                        </p>
                    </div>
                @endif

                @foreach ($produtos as $index => $produto)
                    <div class="bg-gray-200 text-gray-900 p-4 rounded">
                        <h4 class="text-lg font-bold mb-2">Produto {{ $index + 1 }}</h4>

                        <div class="mb-2">
                            <label class="block mb-1">Nome:</label>
                            <input type="text" value="{{ $produto['xProd'] ?? '' }}" 
                                   class="w-full p-2 text-black rounded bg-white" readonly>
                        </div>

                        <div class="mb-2">
                            <label class="block mb-1">Código:</label>
                            <input type="text" value="{{ $produto['cProd'] ?? '' }}" 
                                   class="w-full p-2 text-black rounded bg-white" readonly>
                        </div>

                        <div class="mb-2">
                            <label class="block mb-1">Quantidade:</label>
                            <input type="number" value="{{ $produto['qCom'] ?? '' }}" 
                                   class="w-full p-2 text-black rounded bg-white" readonly>
                        </div>

                        <div class="mb-2">
                            <label class="block mb-1">Preço Unitário:</label>
                            <input type="text" value="{{ $produto['vUnCom'] ?? '' }}" 
                                   class="w-full p-2 text-black rounded bg-white" readonly>
                        </div>

                        {{-- ✅ Informações fiscais do XML (se disponíveis) --}}
                        @if(isset($produto['NCM']) || isset($produto['CEST']))
                            <div class="mt-3 pt-3 border-t border-gray-300">
                                <h5 class="font-semibold text-sm text-gray-600 mb-2">Dados Fiscais do XML:</h5>
                                <div class="grid grid-cols-2 gap-2 text-sm">
                                    @if(isset($produto['NCM']))
                                        <div>
                                            <span class="text-gray-500">NCM:</span>
                                            <span class="font-mono">{{ $produto['NCM'] }}</span>
                                        </div>
                                    @endif
                                    @if(isset($produto['CEST']))
                                        <div>
                                            <span class="text-gray-500">CEST:</span>
                                            <span class="font-mono">{{ $produto['CEST'] }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-600 mt-4">Nenhum produto carregado.</p>
        @endif

        {{-- Botão Salvar --}}
        @if(!empty($produtos))
        <div class="mt-6 flex items-center justify-between">
            <div class="text-sm text-gray-500">
                @if($tipo_nota)
                    ✅ Pronto para salvar com tributação de <strong>{{ $tipo_nota }}</strong>
                @else
                    ⚠️ Selecione o tipo da nota antes de salvar
                @endif
            </div>
            <button type="submit" 
                style="background-color: #28a745; color: #fff; padding: 10px 20px; 
                       border: none; border-radius: 4px; text-decoration: none; display: inline-block; font-weight: bold;"
                class="hover:bg-green-600 transition-colors {{ !$tipo_nota ? 'opacity-50 cursor-not-allowed' : '' }}"
                {{ !$tipo_nota ? 'disabled' : '' }}>
                💾 Salvar Nota e Produtos
            </button>
        </div>
        @endif
    </form>
</div>