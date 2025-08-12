<span
    class="bg-blue-100 text-blue-800 text-{{$textLength ?? 'sm'}} font-medium px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">
    {{Akaunting\Money\Money::BRL($value ?? 0, $convert ?? true)}}
</span>
