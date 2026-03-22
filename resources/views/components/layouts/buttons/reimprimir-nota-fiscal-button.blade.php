<button
    type="button"
    class="focus:outline-none text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-3 py-2 me-2 mb-2 mt-2 dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:ring-blue-900"
    data-drawer-target="{{ $identificador ?? null }}"
    data-drawer-show="{{ $identificador ?? null }}"
    aria-controls="{{ $identificador ?? null }}">
    
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer-fill" viewBox="0 0 16 16">
        <path d="M2 7a1 1 0 0 0-1 1v3a2 2 0 0 0 2 2h1v2h8v-2h1a2 2 0 0 0 2-2V8a1 1 0 0 0-1-1H2zm3 6v-3h6v3H5z"/>
        <path d="M12 3H4v3h8V3z"/>
    </svg>

</button>


<div id="{{ $identificador ?? null }}" class="fixed top-0 left-0 z-40 h-screen p-4 overflow-y-auto transition-transform -translate-x-full bg-white w-80 dark:bg-gray-800" tabindex="-1" aria-labelledby="drawer-label">

    <h5>
        <button type="button" data-drawer-hide="{{ $identificador ?? null }}" aria-controls="{{ $identificador ?? null }}" class="mb-4 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 absolute top-2.5 end-2.5 flex items-center justify-center dark:hover:bg-gray-600 dark:hover:text-white">
            
            <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
            </svg>

            <span class="sr-only">Fechar</span>
        </button>
    </h5>

    <br>

    <div class="mt-4 p-4 mb-4 text-blue-800 border border-blue-300 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400 dark:border-blue-800" role="alert">

        <div class="flex items-center">

            <svg class="flex-shrink-0 w-4 h-4 me-2" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                <path d="M2 7a2 2 0 0 1 2-2h1V3h10v2h1a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-1v3H5v-3H4a2 2 0 0 1-2-2V7z"/>
            </svg>

            <h3 class="text-lg font-medium">
                Reimpressão de nota fiscal
            </h3>

        </div>

        <div class="mt-2 mb-4 text-sm">
            <p>Deseja reimprimir esta nota fiscal?</p>
        </div>

    </div>

    <div class="grid grid-cols-12 gap-4">

        <form action="{{ $route }}" method="POST" class="col-span-12">

            @csrf

            <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 dark:bg-blue-500 dark:hover:bg-blue-600 focus:outline-none dark:focus:ring-blue-800">

                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer-fill me-2" viewBox="0 0 16 16">
                    <path d="M2 7a1 1 0 0 0-1 1v3a2 2 0 0 0 2 2h1v2h8v-2h1a2 2 0 0 0 2-2V8a1 1 0 0 0-1-1H2zm3 6v-3h6v3H5z"/>
                    <path d="M12 3H4v3h8V3z"/>
                </svg>

                Confirmar reimpressão

            </button>

        </form>

    </div>

</div>