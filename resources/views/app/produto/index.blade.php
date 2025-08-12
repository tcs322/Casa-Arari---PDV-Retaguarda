@extends('app.layouts.app')

@section('breadcrumb')
    {{ Breadcrumbs::render('produto') }}
@endsection

@section('title', 'Produtos')

@section('content')

<x-layouts.headers.list-header :count="$produtos->total()" :title="'Produtos'" :route="'produto/create'"/>

@include('components.alerts.form-success')

@include('app.produto.partials.filters', [
    "produtos" => $produtos,
    "filters" => $filters
])


@include('app.produto.partials.list', [
    "produtos" => $produtos,
    "filters" => $filters
])

@endsection
