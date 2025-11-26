@extends('app.layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Buscar Vendas por Período</h2>

    <form action="{{ route('vendas.by_period') }}" method="GET" class="mt-3">
        <div class="row">
            <div class="col-md-3">
                <label>Data Início</label>
                <input type="date" name="data_inicio" class="form-control text-gray-900" required>
            </div>

            <div class="col-md-3">
                <label>Data Fim</label>
                <input type="date" name="data_fim" class="form-control text-gray-900" required>
            </div>

            <div class="col-md-3">
                <label>Usuário</label>
                <select name="usuario_uuid" class="form-control text-gray-900">
                    <option value="">Todos</option>
                    @foreach($users as $u)
                        <option value="{{ $u->uuid }}">{{ $u->name ?? $u->nome ?? $u->email }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label>Forma de Pagamento</label>
                <select name="forma_pagamento" class="form-control text-gray-900">
                    <option value="">Todos</option>
                    @foreach($formas as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-12 mt-3">
                <button type="submit" class="btn w-100"
                    style="background-color: #145A32; border-color: #145A32; color: #fff; border-radius:6px; padding:10px; font-size:16px;"
                    onmouseover="this.style.backgroundColor='#1D8348'"
                    onmouseout="this.style.backgroundColor='#145A32'">
                    Buscar
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
