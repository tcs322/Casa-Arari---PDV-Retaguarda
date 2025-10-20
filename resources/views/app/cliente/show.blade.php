@extends('app.layouts.app')

@section('title', 'Cliente {{$cliente->nome}}')

@section('content')

@dd($cliente)

@endsection
