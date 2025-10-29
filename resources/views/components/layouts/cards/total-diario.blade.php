@props(['total'])

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-16 text-center border border-gray-200 dark:border-gray-700">
    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-2">Total Diário</h3>
    <p class="text-3xl font-bold text-green-600 dark:text-green-400">
        R$ {{ number_format($total, 2, ',', '.') }}
    </p>
</div>
