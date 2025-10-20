@extends('app.layouts.app')

@section('breadcrumb')
    {{ Breadcrumbs::render('nota.create-without-xml') }}
@endsection

@section('title', 'Nova NFE')

<x-layouts.headers.create-header :title="'Nova NFE'"/>

@section('content')

@include('components.alerts.form-errors')

<form action="{{route('nota.store')}}" method="POST">
    @include('app.nota.partials.form', compact('formData'))
</form>

@endsection
