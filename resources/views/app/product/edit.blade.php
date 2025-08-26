@extends('app.layouts.app')

@section('breadcrumb')
    {{ Breadcrumbs::render('produto.edit', $produto) }}
@endsection

@section('title', 'Edição Produto')

<x-layouts.headers.edit-header :title="$produto->uuid"/>

@section('content')

@include('components.alerts.form-errors')

<form action="{{route('produto.update', $produto->uuid)}}" method="POST">
    @method('PUT')
    @include('app.product.partials.form', ["product" => $produto])
</form>

@endsection
