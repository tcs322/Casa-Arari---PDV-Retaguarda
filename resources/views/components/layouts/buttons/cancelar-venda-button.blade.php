<button
    type="button"
    class="focus:outline-none text-white bg-yellow-600 hover:bg-yellow-700 focus:ring-4 focus:ring-yellow-300 font-medium rounded-lg text-sm px-3 py-2 me-2 mb-2 mt-2 dark:bg-yellow-500 dark:hover:bg-yellow-600 dark:focus:ring-yellow-900"
    data-drawer-target="{{ $identificador ?? null }}"
    data-drawer-show="{{ $identificador ?? null }}"
    aria-controls="{{ $identificador ?? null }}">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-octagon-fill" viewBox="0 0 16 16">
        <path d="M11.46.146A.5.5 0 0 1 12 .5v.293L15.207 4H15.5a.5.5 0 0 1 .354.146l.5.5A.5.5 0 0 1 16 5v6a.5.5 0 0 1-.146.354l-.5.5A.5.5 0 0 1 15.5 12h-.293L12 15.207V15.5a.5.5 0 0 1-.146.354l-.5.5A.5.5 0 0 1 11 16H5a.5.5 0 0 1-.354-.146l-.5-.5A.5.5 0 0 1 4 15.5v-.293L.793 12H.5a.5.5 0 0 1-.354-.146l-.5-.5A.5.5 0 0 1 0 11V5a.5.5 0 0 1 .146-.354l.5-.5A.5.5 0 0 1 .5 4h.293L4 0.793V0.5A.5.5 0 0 1 4.146.146l.5-.5A.5.5 0 0 1 5 0h6a.5.5 0 0 1 .354.146l.5.5ZM8 4a.5.5 0 0 0-.5.5V8H5a.5.5 0 0 0 0 1h2.5v3.5a.5.5 0 0 0 1 0V9H11a.5.5 0 0 0 0-1H8.5V4.5A.5.5 0 0 0 8 4Z"/>
    </svg>
</button>

<div id="{{ $identificador ?? null }}" class="fixed top-0 left-0 z-40 h-screen p-4 overflow-y-auto transition-transform -translate-x-full bg-white w-80 dark:bg-gray-800" tabindex="-1" aria-labelledby="drawer-label">
    <h5>
        <button type="button" data-drawer-hide="{{ $identificador ?? null }}" aria-controls="{{ $identificador ?? null }}" class="mb-4 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 absolute top-2.5 end-2.5 flex items-center justify-center dark:hover:bg-gray-600 dark:hover:text-white" >
            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
            </svg>
            <span class="sr-only">Fechar</span>
        </button>
    </h5>
    <br>
    <div class="mt-4 p-4 mb-4 text-yellow-800 border border-yellow-300 rounded-lg bg-yellow-50 dark:bg-gray-800 dark:text-yellow-400 dark:border-yellow-800" role="alert">
        <div class="flex items-center">
            <svg class="flex-shrink-0 w-4 h-4 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm0 14.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3Zm.75-9.75a.75.75 0 0 0-1.5 0v5a.75.75 0 0 0 1.5 0v-5Z"/>
            </svg>
            <h3 class="text-lg font-medium">Cancelar venda</h3>
        </div>
        <div class="mt-2 mb-4 text-sm">
            <p>Tem certeza de que deseja cancelar esta venda?</p>
            <p>Essa ação não poderá ser desfeita.</p>
        </div>
    </div>
    <div class="grid grid-cols-12 gap-4">
        <form action="{{ $route }}" method="POST" class="col-span-12">
            @csrf
            <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-yellow-600 rounded-lg hover:bg-yellow-700 focus:ring-4 focus:ring-yellow-300 dark:bg-yellow-500 dark:hover:bg-yellow-600 focus:outline-none dark:focus:ring-yellow-800">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-octagon-fill me-2" viewBox="0 0 16 16">
                    <path d="M11.46.146A.5.5 0 0 1 12 .5v.293L15.207 4H15.5a.5.5 0 0 1 .354.146l.5.5A.5.5 0 0 1 16 5v6a.5.5 0 0 1-.146.354l-.5.5A.5.5 0 0 1 15.5 12h-.293L12 15.207V15.5a.5.5 0 0 1-.146.354l-.5.5A.5.5 0 0 1 11 16H5a.5.5 0 0 1-.354-.146l-.5-.5A.5.5 0 0 1 4 15.5v-.293L.793 12H.5a.5.5 0 0 1-.354-.146l-.5-.5A.5.5 0 0 1 0 11V5a.5.5 0 0 1 .146-.354l.5-.5A.5.5 0 0 1 .5 4h.293L4 0.793V0.5A.5.5 0 0 1 4.146.146l.5-.5A.5.5 0 0 1 5 0h6a.5.5 0 0 1 .354.146l.5.5ZM8 4a.5.5 0 0 0-.5.5V8H5a.5.5 0 0 0 0 1h2.5v3.5a.5.5 0 0 0 1 0V9H11a.5.5 0 0 0 0-1H8.5V4.5A.5.5 0 0 0 8 4Z"/>
                </svg>
                Confirmar cancelamento
            </button>
        </form>
    </div>
</div>
