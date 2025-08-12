
@section('header')
    <div class="">
        <b class="uppercase">{{$count}} {{$title}}</b> |
        <x-layouts.buttons.action-button
            text="Criar"
            action="criar"
            color="success"
            :route="$route"/>
    </div>
@endsection
