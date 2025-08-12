<!-- resources/views/components/layouts/tables/simple-table.blade.php -->
{{--
<style>
    .styled-table {
        border-collapse: collapse;
        margin: 6px 0;
        font-size: 0.9em;
        font-family: sans-serif;
        min-width: 400px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
    }

    .styled-table thead tr {
        background-color: rgb(59, 113, 202);
        color: #ffffff;
        text-align: left;
    }

    .styled-table th,
    .styled-table td {
        padding: 5px 5px 5px 5px;
    }

    .styled-table tbody tr {
        border-bottom: 1px solid #dddddd;
    }

    .styled-table tbody tr:nth-of-type(even) {
        background-color: #f3f3f3;
    }

    .styled-table tbody tr:last-of-type {
        border-bottom: 2px solid rgb(59, 113, 202);
    }

    .styled-table tbody tr.active-row {
        font-weight: bold;
        color: rgb(59, 113, 202);
    }
</style> --}}


    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr>
                @foreach ($headers as $header)
                    <th scope="col" class="px-6 py-3">{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @yield('table-content')
        </tbody>
    </table>
    @isset($paginator, $appends)
        <x-pagination.simple-pagination :paginator="$paginator" :appends="$appends" />
    @endisset

