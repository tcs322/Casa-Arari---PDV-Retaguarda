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
        {{-- Informações da Nota --}}
        @if($notaInfo['numero'] || $notaInfo['valor'] || $notaInfo['fornecedor'])
        <div class="bg-gray-100 p-4 rounded mb-6">
            <h3 class="text-xl font-bold mb-4">Informações da Nota</h3>

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

        {{-- Upload XML --}}
        <div>
            <label for="xmlFile" class="block text-sm font-medium text-gray-700">Arquivo XML:</label>
            <input type="file" id="xmlFile" wire:model="xmlFile" accept=".xml" 
                   class="mt-1 block w-full text-sm text-gray-500">
            @error('xmlFile') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        {{-- Produtos --}}
        @if (!empty($produtos))
            <div class="space-y-4 mt-6">
                <h3 class="text-xl font-bold mb-4">Produtos da Nota</h3>
                @foreach ($produtos as $index => $produto)
                    <div class="bg-gray-200 p-4 rounded">
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
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-600 mt-4">Nenhum produto carregado.</p>
        @endif

        {{-- Botão Salvar --}}
        @if(!empty($produtos))
        <button type="submit" 
            style="background-color: #28a745; color: #fff; padding: 8px 16px; 
                   border: none; border-radius: 4px; text-decoration: none; display: inline-block;"
            class="mt-6">
            Salvar Nota
        </button>
        @endif
    </form>
</div>