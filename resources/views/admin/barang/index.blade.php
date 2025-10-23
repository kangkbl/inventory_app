@extends('layouts.app')

@section('title', 'Admin - Barang')
@section('menuAdminBarang', 'bg-gray-100 text-gray-900 dark:bg-white/10')

@section('content')
    @livewire('admin.barang.index')
@endsection