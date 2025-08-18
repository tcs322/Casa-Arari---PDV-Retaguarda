@extends('auth.layouts.auth')

@section('content-auth')
<form method="POST" action="{{ route('auth.password.change') }}" class="max-w-sm m-4 p-10 bg-white bg-opacity-25 rounded shadow-xl">
    @csrf
    <p class="text-white font-medium text-center text-lg font-bold">ALTERAR SENHA</p>
    @include('components.alerts.form-errors')

    <div class="mt-2">
        <label class="block text-sm text-white" for="password">Nova Senha</label>
        <input name="password" id="password"
            class="w-full px-5 py-1 text-gray-700 bg-gray-300 rounded focus:outline-none focus:bg-white"
            type="password" placeholder="Digite a nova senha" required>
    </div>

    <div class="mt-2">
        <label class="block text-sm text-white" for="password_confirmation">Confirmar Nova Senha</label>
        <input name="password_confirmation" id="password_confirmation"
            class="w-full px-5 py-1 text-gray-700 bg-gray-300 rounded focus:outline-none focus:bg-white"
            type="password" placeholder="Confirme a nova senha" required>
    </div>

    <div class="mt-4 flex justify-center">
        <button type="submit"
            class="px-4 py-1 text-white font-light tracking-wider bg-gray-900 hover:bg-gray-800 rounded">
            Alterar Senha
        </button>
    </div>
</form>
@endsection
