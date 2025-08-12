<div class="conceitos-avaliacao-itens">
    <div class="flex flex-wrap -mx-3 mt-2 conceitos-avaliacao-item">
        <x-layouts.inputs.input-array-text
            label="Item"
            name="itens_conceitos_avaliacao[0][nome]"
            lenght="6/12"
            :value="$conceitoAvaliacao->nome ?? old('nome')"
        />
        <x-layouts.inputs.input-normal-number
            label="Pontuacao"
            name="itens_conceitos_avaliacao[0][pontuacao]"
            lenght="2/12"
            :value="$conceitoAvaliacao->pontuacao ?? old('pontuacao')"
        />
        <button
            type="button"
            class="focus:outline-none text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-3 me-2 mt-6 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-900"
            onclick="inputItem()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                    <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/>
                </svg>
        </button>
        <button
            type="button"
            class="focus:outline-none text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-3 me-2 me-2 mt-6 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900"
            onclick="deleteInputItem()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3-fill" viewBox="0 0 16 16">
                    <path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5Zm-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5ZM4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06Zm6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528ZM8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5Z"/>
                </svg>
        </button>
    </div>
</div>

<script>
    function inputItem() {
        let containerAvaliacaoItens = document.getElementsByClassName('conceitos-avaliacao-itens');

        let conceitosAvaliacaoItem = document.getElementsByClassName('conceitos-avaliacao-item');

        let quantidadeItens = conceitosAvaliacaoItem.length;

        let cloneConceitosAvaliacaoItem = conceitosAvaliacaoItem[0].cloneNode(true);

        let inputItens = cloneConceitosAvaliacaoItem.getElementsByTagName('input');
        for (let item of inputItens) {
            item.name = item.name.replace('[0]', `[${quantidadeItens}]`);
            item.id = item.id.replace('[0]', `[${quantidadeItens}]`);
        }

        let buttonItens = cloneConceitosAvaliacaoItem.getElementsByTagName('button');
        for (let buttonDelete of buttonItens) {
            buttonItens[1].onclick = function(){
                deleteInputItem(cloneConceitosAvaliacaoItem)
            };
        }

        containerAvaliacaoItens[0].appendChild(cloneConceitosAvaliacaoItem);
    }

    function deleteInputItem(element) {
        if (element)
            element.remove();
    }
</script>
