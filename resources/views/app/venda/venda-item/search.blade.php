@extends('app.layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Buscar Vendas por Período</h2>

    <form action="{{ route('venda_itens.by_period') }}" method="GET" class="mt-3">

        <div class="row">
            <div class="col-md-4">
                <label for="data_inicio">Data Início:</label>
                <input type="date" name="data_inicio" class="form-control text-gray-900" required>
            </div>

            <div class="col-md-4">
                <label for="data_fim">Data Fim:</label>
                <input type="date" name="data_fim" class="form-control  text-gray-900" required>
            </div>

            <div class="col-md-4">
                <label for="tipo">Tipo do Produto:</label>
                <select name="tipo" class="form-control  text-gray-900">
                    <option value="">Todos</option>
                    <option value="CAFETERIA">Cafeteria</option>
                    <option value="LIVRARIA">Livraria</option>
                </select>
            </div>

            <div class="col-md-12 mt-3">
                <button type="submit" class="btn w-100" 
                    style="
                        background-color: #145A32; 
                        border-color: #145A32; 
                        color: white; 
                        border-radius: 6px; 
                        padding: 10px;
                        font-size: 16px;
                    "
                    onmouseover="this.style.backgroundColor='#1D8348'"
                    onmouseout="this.style.backgroundColor='#145A32'"
                >
                    Buscar
                </button>
            </div>
        </div>

    </form>
</div>
@endsection
