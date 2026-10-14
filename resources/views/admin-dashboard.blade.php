@extends('layouts.app')

@section('title', 'Executive Dashboard')

@section('content')
    {{-- Executive control centre for administrators. Every figure comes from
         the database (see App\Services\BusinessMetrics for the definitions). --}}
    @include('dashboard.admin._header')
    @include('dashboard.admin._kpis')
    @include('dashboard.admin._analytics')

    <div class="ccc-dash-grid">
        @include('dashboard.admin._insights')
        @include('dashboard.admin._notifications')
    </div>

    @include('dashboard.admin._reports')
    @include('dashboard.admin._staff')
@endsection
