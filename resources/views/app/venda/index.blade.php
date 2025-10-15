@extends('app.layouts.app')

@section('breadcrumb')
    {{ Breadcrumbs::render('venda') }}
@endsection

@section('title', 'Vendas')

@section('content')

<x-layouts.headers.list-header :count="$vendas->total()" :title="'Vendas'" :route="'venda/create'"/>

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
