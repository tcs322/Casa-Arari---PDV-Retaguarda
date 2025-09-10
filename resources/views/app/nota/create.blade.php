@extends('app.layouts.app')

@section('title', 'Adicionar Nfe')

<x-layouts.headers.create-header :title="'Adicionar Nfe'"/>

@section('content')

@include('components.alerts.form-errors')

@csrf

@livewire('components.app.nota-produtos')
    
@endsection
