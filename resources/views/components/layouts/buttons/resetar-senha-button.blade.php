<button
    type="button"
    class="focus:outline-none text-white bg-orange-600 hover:bg-orange-700 focus:ring-4 focus:ring-orange-300 font-medium rounded-lg text-sm px-3 py-2 me-2 mb-2 mt-2 dark:bg-orange-500 dark:hover:bg-orange-600 dark:focus:ring-orange-900"
    data-drawer-target="{{ $identificador ?? null }}"
    data-drawer-show="{{ $identificador ?? null }}"
    aria-controls="{{ $identificador ?? null }}">

    <!-- Ícone de reset -->
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
         class="bi bi-arrow-clockwise" viewBox="0 0 16 16">
        <path fill-rule="evenodd"
              d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z"/>
        <path
              d="M8 1.5a.5.5 0 0 1 .5.5v2.793l1.146-1.147a.5.5 0 0 1 .708.708l-2 2a.5.5 0 0 1-.708 0l-2-2a.5.5 0 1 1 .708-.708L7.5 4.793V2a.5.5 0 0 1 .5-.5"/>
    </svg>
</button>

<div id="{{ $identificador ?? null }}"
     class="fixed top-0 left-0 z-40 h-screen p-4 overflow-y-auto transition-transform -translate-x-full bg-white w-80 dark:bg-gray-800"
     tabindex="-1"
     aria-labelledby="drawer-label">

    <h5>
        <button type="button"
                data-drawer-hide="{{ $identificador ?? null }}"
                aria-controls="{{ $identificador ?? null }}"
                class="mb-4 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 absolute top-2.5 end-2.5 flex items-center justify-center dark:hover:bg-gray-600 dark:hover:text-white">
            <svg class="w-3 h-3" aria-hidden="true"
                 xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                <path stroke="currentColor" stroke-linecap="round"
                      stroke-linejoin="round" stroke-width="2"
                      d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
            </svg>
            <span class="sr-only">Fechar</span>
        </button>
    </h5>

    <br>

    <div class="mt-4 p-4 mb-4 text-orange-800 border border-orange-300 rounded-lg bg-orange-50
                dark:bg-gray-800 dark:text-orange-400 dark:border-orange-800" role="alert">

        <div class="flex items-center">
            <svg class="flex-shrink-0 w-4 h-4 me-2"
                 xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                 viewBox="0 0 16 16">
                <path d="M8 1a4 4 0 0 1 4 4v1h1a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h1V5a4 4 0 0 1 4-4zm-2 5h4V5a2 2 0 1 0-4 0v1z"/>
            </svg>

            <h3 class="text-lg font-medium">Resetar senha</h3>
        </div>

        <div class="mt-2 mb-4 text-sm">
            <p>Tem certeza de que deseja resetar a senha deste usuário?</p>
            <p>Essa ação não poderá ser desfeita.</p>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-4">
        <form action="{{ $route }}" method="POST" class="col-span-12">
            @csrf

            <button type="submit"
                    class="w-full inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white
                           bg-orange-600 rounded-lg hover:bg-orange-700 focus:ring-4 focus:ring-orange-300
                           dark:bg-orange-500 dark:hover:bg-orange-600 focus:outline-none dark:focus:ring-orange-800">

                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                     fill="currentColor" class="bi bi-arrow-clockwise me-2"
                     viewBox="0 0 16 16">
                    <path fill-rule="evenodd"
                          d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z"/>
                    <path
                          d="M8 1.5a.5.5 0 0 1 .5.5v2.793l1.146-1.147a.5.5 0 0 1 .708.708l-2 2a.5.5 0 0 1-.708 0l-2-2a.5.5 0 1 1 .708-.708L7.5 4.793V2a.5.5 0 0 1 .5-.5"/>
                </svg>

                Confirmar reset de senha
            </button>
        </form>
    </div>
</div>
