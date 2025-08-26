@extends('app.layouts.app')

@section('breadcrumb')
    {{ Breadcrumbs::render('produto') }}
@endsection

@section('title', 'Produtos')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Produtos ({{ $products->total() }})</h1>

    <div class="d-flex gap-2">
        <a href="{{ route('produto.create-many') }}"
           style="background-color: #28a745; color: #fff; padding: 8px 16px; border: none; border-radius: 4px; text-decoration: none; display: inline-block;">
            Carregar via XML
        </a>

        <a href="{{ route('produto.create') }}"
           style="background-color: #28a745; color: #fff; padding: 8px 16px; border: none; border-radius: 4px; text-decoration: none; display: inline-block;">
            Novo Produto
        </a>
    </div>
</div>

@include('components.alerts.form-success')

@include('app.product.partials.filters', [
    "products" => $products,
    "filters" => $filters
])


@include('app.product.partials.list', [
    "products" => $products,
    "filters" => $filters
])

@endsection
