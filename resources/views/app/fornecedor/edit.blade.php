@extends('app.layouts.app')

@section('breadcrumb')
    {{ Breadcrumbs::render('fornecedor.edit', $fornecedor) }}
@endsection

@section('title', 'Edição Fornecedor')

<x-layouts.headers.edit-header :title="$fornecedor->uuid.' - '.$fornecedor->razao_social"/>

@section('content')

@include('components.alerts.form-errors')

<form action="{{route('fornecedor.update', $fornecedor->uuid)}}" method="POST">
    @method('PUT')
    @include('app.fornecedor.partials.form', ["fornecedor" => $fornecedor])
</form>

@endsection
