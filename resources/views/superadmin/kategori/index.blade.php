@extends('layouts.app')

@section('icon', 'M15 1.943v12.114a1 1 0 0 1-1.581.814L8 11V5l5.419-3.871A1 1 0 0 1 15 1.943ZM7 4H2a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2v5a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2V4ZM4 17v-5h1v5H4ZM16 5.183v5.634a2.984 2.984 0 0 0 0-5.634Z')

@section('title', 'Category Management')
@section('menuSuperAdminCategory', 'bg-gray-200 dark:bg-gray-800 hidden:hover')

@section('content')
    @livewire('superadmin.kategori.index')
@endsection
