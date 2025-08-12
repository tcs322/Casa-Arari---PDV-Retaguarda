<div>
    @if(Session::has('message'))
        <div
            class="mb-4 rounded-lg bg-success-100 px-6 py-5 text-base text-success-800"
            role="alert">
            {{Session::get('message')}}
        </div>
    @endif
</div>
