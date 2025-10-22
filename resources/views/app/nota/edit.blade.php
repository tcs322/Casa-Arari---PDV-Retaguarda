@extends('app.layouts.app')

@section('breadcrumb')
    {{ Breadcrumbs::render('nota.edit', $nota) }}
@endsection

@section('title', 'Edição NFE')

<x-layouts.headers.edit-header :title="$nota->uuid.' - '.$nota->numero_nota"/>

@section('content')

@include('components.alerts.form-errors')

<form action="{{route('nota.update', $nota->uuid)}}" method="POST">
    @method('PUT')
    @include('app.nota.partials.form', ["nota" => $nota])
</form>

@endsection
