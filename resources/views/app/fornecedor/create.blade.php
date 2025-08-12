@extends('app.layouts.app')

@section('breadcrumb')
    {{ Breadcrumbs::render('fornecedor.create') }}
@endsection

@section('title', 'Novo Fornecedor')

<x-layouts.headers.create-header :title="'Novo Fornecedor'"/>

@section('content')

@include('components.alerts.form-errors')

<form action="{{route('fornecedor.store')}}" method="POST">
    @include('app.fornecedor.partials.form', compact('formData'))
</form>

@endsection
