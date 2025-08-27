@extends('app.layouts.app')

@section('title', 'Produto {{$produto->uuid}}')

@section('content')

@dd($produto)

@endsection
