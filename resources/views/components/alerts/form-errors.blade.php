<div>
    @if($errors->any())
        <div
            class="mb-4 rounded-lg bg-warning-100 px-6 py-5 text-base text-warning-800"
            role="alert">
            <b>Verifique as instruções e tente novamente.</b>
            {{-- <ul>
                @foreach($errors->all() as $error)
                    <li>{{$error}}</li>
                @endforeach
            </ul> --}}
        </div>
    @endif
</div>
