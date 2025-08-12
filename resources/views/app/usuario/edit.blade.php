@extends('app.layouts.app')

@section('breadcrumb')
    {{ Breadcrumbs::render('usuario.edit', $user) }}
@endsection

@section('title', 'Edição Usuario')

<x-layouts.headers.edit-header :title="$user->uuid.' - '.$user->name"/>

@section('content')

@include('components.alerts.form-errors')

<form action="{{ route('usuario.update', $user->uuid) }}" method="POST">
    @csrf
    @method('PUT')
    @include('app.usuario.partials.form')
</form>

@endsection
