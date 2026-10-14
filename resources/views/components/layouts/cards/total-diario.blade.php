@props(['total'])

<div
    class="relative bg-white dark:bg-gray-800 rounded-lg shadow-md p-16 text-center border border-gray-200 dark:border-gray-700"
>
    {{-- Botão para ocultar/exibir o valor --}}
    <button
        type="button"
        class="toggle-total-diario absolute top-3 right-3 z-10
               flex items-center justify-center
               w-9 h-9 rounded-full
               text-gray-500 dark:text-gray-400
               hover:bg-gray-100 dark:hover:bg-gray-700
               hover:text-gray-800 dark:hover:text-white
               transition-colors cursor-pointer"
        aria-label="Ocultar valor"
        title="Ocultar valor"
        aria-pressed="false"
    >
        {{-- Ícone de olho aberto --}}
        <svg
            class="icone-olho-aberto w-5 h-5"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.8"
            stroke="currentColor"
            aria-hidden="true"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M2.25 12s3.75-7.5 9.75-7.5 9.75 7.5 9.75 7.5-3.75 7.5-9.75 7.5S2.25 12 2.25 12Z"
            />

            <circle cx="12" cy="12" r="3" />
        </svg>

        {{-- Ícone de olho fechado --}}
        <svg
            class="icone-olho-fechado w-5 h-5"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.8"
            stroke="currentColor"
            aria-hidden="true"
            hidden
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M3 3l18 18M10.6 10.6a2 2 0 0 0 2.8 2.8M9.9 5.2A10.5 10.5 0 0 1 12 5c6 0 9.75 7 9.75 7a16 16 0 0 1-3.1 3.9M6.2 6.2C3.6 8 2.25 12 2.25 12S6 19 12 19c1.5 0 2.9-.4 4.1-1.1"
            />
        </svg>
    </button>

    {{-- Título --}}
    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-2">
        Total Diário
    </h3>

    {{-- Valor total --}}
    <p class="text-3xl font-bold text-green-600 dark:text-green-400">
        <span class="valor-total-diario">
            R$ {{ number_format($total, 2, ',', '.') }}
        </span>
    </p>
</div>

{{-- JavaScript nativo, sem dependência do Alpine --}}
<script>
(function () {
    function inicializarCardsTotalDiario() {
        document.querySelectorAll('.toggle-total-diario').forEach(function (botao) {
            // Evita registrar o mesmo evento mais de uma vez.
            if (botao.dataset.inicializado === 'true') {
                return;
            }

            botao.dataset.inicializado = 'true';

            botao.addEventListener('click', function () {
                const card = botao.closest('.relative');

                if (!card) {
                    return;
                }

                const valor = card.querySelector('.valor-total-diario');
                const olhoAberto = botao.querySelector('.icone-olho-aberto');
                const olhoFechado = botao.querySelector('.icone-olho-fechado');

                if (!valor || !olhoAberto || !olhoFechado) {
                    return;
                }

                const ocultar = botao.getAttribute('aria-pressed') !== 'true';

                if (ocultar) {
                    valor.textContent = 'R$ ••••••';
                } else {
                    valor.textContent = valor.dataset.valorOriginal;
                }

                botao.setAttribute('aria-pressed', String(ocultar));
                botao.setAttribute(
                    'aria-label',
                    ocultar ? 'Exibir valor' : 'Ocultar valor'
                );
                botao.setAttribute(
                    'title',
                    ocultar ? 'Exibir valor' : 'Ocultar valor'
                );

                olhoAberto.hidden = ocultar;
                olhoFechado.hidden = !ocultar;
            });
        });
    }

    // Guarda o valor original antes de permitir a ocultação.
    document.querySelectorAll('.valor-total-diario').forEach(function (valor) {
        if (!valor.dataset.valorOriginal) {
            valor.dataset.valorOriginal = valor.textContent.trim();
        }
    });

    inicializarCardsTotalDiario();
})();
</script>