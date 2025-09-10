@extends('app.layouts.app')

@section('title', 'Casa Arari PDV')

<x-layouts.headers.create-header :title="'Nova Venda'"/>

@section('content')

@include('components.alerts.form-errors')

@csrf

@livewire('components.app.frente-caixa')
    
@endsection
