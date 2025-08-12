@extends('app.layouts.app')

@section('breadcrumb')
    {{ Breadcrumbs::render('usuario.create') }}
@endsection

@section('title', 'Novo Usuário')

<x-layouts.headers.create-header :title="'Novo Usuário'"/>

@section('content')

@include('components.alerts.form-errors')

<form action="{{ route('usuario.store') }}" method="POST">
    @include('app.usuario.partials.form')
</form>
@endsection
