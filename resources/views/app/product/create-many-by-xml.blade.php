@extends('app.layouts.app')

@section('title', 'Adicionar Produtos')

<x-layouts.headers.create-header :title="'Adicionar Produtos'"/>

@section('content')

@include('components.alerts.form-errors')

@csrf

@livewire('components.app.product-item')
    
@endsection
