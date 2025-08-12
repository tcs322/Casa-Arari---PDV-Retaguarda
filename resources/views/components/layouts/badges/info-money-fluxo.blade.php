@if($value > 0)
<span
    class="bg-green-100 text-green-800 text-{{$textLength ?? 'sm'}} font-medium px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">
    &#11014;
    {{Akaunting\Money\Money::BRL($value ?? 0, $convert ?? true)}}
</span>
@elseif($value < 0)
<span
    class="bg-red-100 text-red-800 text-{{$textLength ?? 'sm'}} font-medium px-2.5 py-0.5 rounded dark:bg-red-900 dark:text-red-300">
    &#11015;
    {{Akaunting\Money\Money::BRL(abs($value) ?? 0, $convert ?? true)}}
</span>
@else
    <span class="m-1 bg-gray-100 text-gray-800 text-{{$textLength ?? 'sm'}} font-medium me-2 px-2.5 py-0.5 rounded dark:bg-gray-700 dark:text-gray-300">
    {{Akaunting\Money\Money::BRL(0, $convert ?? true)}}
</span>
@endif
