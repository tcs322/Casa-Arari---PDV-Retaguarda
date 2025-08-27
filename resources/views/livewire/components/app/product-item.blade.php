<div>
    <form wire:submit.prevent="salvar">
        <div>
            <label for="xmlFile">Arquivo XML:</label>
            <input type="file" id="xmlFile" wire:model="xmlFile" accept=".xml">
        </div>

        @if (!empty($produtos))
            <div class="space-y-4 mt-4">
                @foreach ($produtos as $index => $produto)
                    <div class="bg-black text-white p-4 rounded">
                        <h4 class="text-lg font-bold mb-2">Produto {{ $index + 1 }}</h4>

                        <div class="mb-2">
                            <label class="block mb-1">Nome:</label>
                            <input type="text" wire:model="produtos.{{ $index }}.xProd" class="w-full p-2 text-black rounded">
                        </div>

                        <div class="mb-2">
                            <label class="block mb-1">Código:</label>
                            <input type="text" wire:model="produtos.{{ $index }}.cProd" class="w-full p-2 text-black rounded">
                        </div>

                        <div class="mb-2">
                            <label class="block mb-1">Quantidade:</label>
                            <input type="number" wire:model="produtos.{{ $index }}.qCom" class="w-full p-2 text-black rounded">
                        </div>

                        <div class="mb-2">
                            <label class="block mb-1">Preço Unitário:</label>
                            <input type="text" wire:model="produtos.{{ $index }}.vUnCom" class="w-full p-2 text-black rounded">
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-600 mt-4">Nenhum produto carregado.</p>
        @endif
        <button type="submit" style="background-color: #28a745; color: #fff; padding: 8px 16px; border: none; border-radius: 4px; text-decoration: none; display: inline-block;">Salvar Produtos</button>
    </form>
</div>
