@extends('auth.layouts.auth')

@section('content-auth')
<form action="{{route('auth.login')}}" method="POST" class="max-w-sm m-4 p-10 bg-white bg-opacity-25 rounded shadow-xl">
    @csrf
    <p class="text-white font-medium text-center text-lg font-bold">LOGIN</p>
    @include('components.alerts.form-errors')
    <div class="">
        <label class="block text-sm text-white" for="email">E-mail</label>
        <input name="email" class="w-full px-5 py-1 text-gray-700 bg-gray-300 rounded focus:outline-none focus:bg-white" type="email" id="email"  placeholder="Digite o e-mail" aria-label="email" required>
    </div>
    <div class="mt-2">
        <label class="block  text-sm text-white">Senha</label>
        <input name="password" class="w-full px-5 py-1 text-gray-700 bg-gray-300 rounded focus:outline-none focus:bg-white"
        type="password" id="password" placeholder="Digite a sua senha" arial-label="password" required>
    </div>
    <div class="mt-4 items-center flex justify-between">
        <button class="px-4 py-1 text-white font-light tracking-wider bg-gray-900 hover:bg-gray-800 rounded"
        type="submit">Entrar</button>
        <a class="inline-block right-0 align-baseline font-bold text-sm text-500 text-white hover:text-red-400"
        href="#">Esqueceu a senha ?</a>
    </div>
    <div class="text-center">
        <a class="inline-block right-0 align-baseline font-light text-sm text-500 hover:text-red-400">
            Recuperar senha
        </a>
    </div>
</form>
@endsection
