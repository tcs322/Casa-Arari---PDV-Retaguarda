@extends('app.layouts.app')

@section('title', 'Fornecedor {{$fornecedor->razao_social}}')

@section('content')

@dd($fornecedor)

@endsection
