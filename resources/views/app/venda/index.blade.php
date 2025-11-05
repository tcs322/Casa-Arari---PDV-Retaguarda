@extends('app.layouts.app')

@section('breadcrumb')
    {{ Breadcrumbs::render('venda') }}
@endsection

@section('title', 'Vendas')

@section('content')

<x-layouts.headers.list-header :count="$vendas->total()" :title="'Vendas'" :route="'frente-caixa'"/>

@include('components.alerts.form-success')

@include('app.venda.partials.filters', [
    "vendas" => $vendas,
    "filters" => $filters
])

@include('app.venda.partials.list', [
    "vendas" => $vendas,
    "filters" => $filters
])

@endsection
