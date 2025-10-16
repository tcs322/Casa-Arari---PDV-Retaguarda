@extends('app.layouts.app')

@section('title', 'Usuario {{$user->name}}')

@section('content')

@dd($user)

@endsection
