@extends('app.layouts.app')

@section('breadcrumb')
    {{ Breadcrumbs::render('produto.create') }}
@endsection

@section('title', 'Novo Produto')

<x-layouts.headers.create-header :title="'Novo Produto'"/>

@section('content')

@include('components.alerts.form-errors')

<form action="{{ route('produto.store') }}" method="POST">
    @csrf
    @include('app.product.partials.form')
</form>

@endsection
