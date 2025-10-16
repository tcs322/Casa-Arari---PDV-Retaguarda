@extends('app.layouts.app')

@section('breadcrumb')
    {{ Breadcrumbs::render('usuario') }}
@endsection

@section('title', 'Usuários')

@section('content')

<x-layouts.headers.list-header :count="$user->total()" :title="'Usuários'" :route="'usuario/create'"/>

@include('components.alerts.form-success')

@include('app.usuario.partials.filters', [
    "users" => $user,
    "filters" => $filters
])

@include('app.usuario.partials.list', [
    "users" => $user,
    "filters" => $filters
])

@endsection
