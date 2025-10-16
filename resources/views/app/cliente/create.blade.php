@extends('app.layouts.app')

@section('breadcrumb')
    {{ Breadcrumbs::render('cliente.create') }}
@endsection

@section('title', 'Novo Cliente')

<x-layouts.headers.create-header :title="'Novo Cliente'"/>

@section('content')

@include('components.alerts.form-errors')

<form action="{{ route('usuario.store') }}" method="POST">
    @include('app.cliente.partials.form', compact('formData'))
</form>
@endsection
