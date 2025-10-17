@extends('app.layouts.app')

@section('breadcrumb')
    {{ Breadcrumbs::render('cliente') }}
@endsection

@section('title', 'Clientes')

@section('content')

<x-layouts.headers.list-header :count="$clientes->total()" :title="'Clientes'" :route="'cliente/create'"/>

@include('components.alerts.form-success')

@include('app.cliente.partials.filters', [
    "clientes" => $clientes,
    "filters" => $filters
])

@include('app.cliente.partials.list', [
    "clientes" => $clientes,
    "filters" => $filters
])

@endsection
