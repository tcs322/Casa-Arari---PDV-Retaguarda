
<div class="relative max-w-sm md:w-{{$lenght}} px-3 mb-6 md:mb-0">
    <label class="block uppercase tracking-wide text-xs font-bold mb-2" for="grid-city">
        {{$label}}
    </label>
    <input datepicker datepicker-autohide datepicker-format="yyyy-mm-dd" name="{{$name}}" type="text" class="bg-gray-200 border border-gray-200 text-gray-900 text-sm rounded-lg focus:outline-none focus:bg-white focus:border-gray-500 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="{{$label}}">
    @error($name)
        <small class="text-red-500">{{ $message }}</small>
    @enderror
</div>
