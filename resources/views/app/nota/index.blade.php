@extends('app.layouts.app')

@section('breadcrumb')
    {{ Breadcrumbs::render('nota') }}
@endsection

@section('title', 'Notas')

@section('content')

<x-layouts.headers.list-header :count="$notas->total()" :title="'Notas'" :route="'produto/create-many'"/>

@include('components.alerts.form-success')

@include('app.nota.partials.filters', [
    "notas" => $notas,
    "filters" => $filters
])

@include('app.nota.partials.list', [
    "notas" => $notas,
    "filters" => $filters
])

@endsection
