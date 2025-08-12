@extends('app.layouts.app')

@section('breadcrumb')
    {{ Breadcrumbs::render('product.create') }}
@endsection

@section('title', 'Novo Produto')

<x-layouts.headers.create-header :title="'Novo Produto'"/>

@section('content')

@include('components.alerts.form-errors')

<form action="{{ route('product.store') }}" method="POST">
    @csrf
    @include('app.product.partials.form')
</form>

@endsection
