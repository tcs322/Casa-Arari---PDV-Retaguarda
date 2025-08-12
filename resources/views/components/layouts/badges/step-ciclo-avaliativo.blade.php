{{--@if ($step == App\Enums\CicloAvaliativoStepsEnum::PERIODICIDADE)--}}
{{--    <span class="bg-gray-800 text-white text-xs font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">--}}
{{--        {{App\Enums\CicloAvaliativoStepsEnum::getKey($step)}}--}}
{{--    </span>--}}
{{--@elseif ($step == App\Enums\CicloAvaliativoStepsEnum::INCIDENCIA)--}}
{{--    <span class="bg-red-100 text-red-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-red-900 dark:text-red-300">--}}
{{--        {{App\Enums\CicloAvaliativoStepsEnum::getKey($step)}}--}}
{{--    </span>--}}
{{--@elseif ($step == App\Enums\CicloAvaliativoStepsEnum::TEMPLATE)--}}
{{--    <span class="bg-orange-300 text-white text-xs font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-orange-900 dark:text-white-100">--}}
{{--        {{App\Enums\CicloAvaliativoStepsEnum::getKey($step)}}--}}
{{--    </span>--}}
{{--@else--}}
{{--    <span class="bg-orange-300 text-white text-xs font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-orange-900 dark:text-white-100">--}}
{{--        {{App\Enums\CicloAvaliativoStepsEnum::getKey($step)}}--}}
{{--    </span>--}}
{{--@endif--}}

<span class="bg-gray-800 text-white text-xs font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">
    {{App\Enums\CicloAvaliativoStepsEnum::getKey($step)}}
</span>
