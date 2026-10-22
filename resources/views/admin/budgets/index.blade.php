@extends('layouts.admin')

@section('title', 'Budget Monitoring')

@section('content_header')
    <h1>Budget Monitoring {{ $year }}</h1>
@stop

@section('content')

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@php
    $totalBudget = array_sum(array_column($monthly, 'budget'));
    $totalActual = array_sum(array_column($monthly, 'actual'));
@endphp

<div class="d-flex flex-wrap justify-content-between mb-3">

    <a href="/admin/budgets/plan?year={{ $year }}&location={{ $locationId }}" class="btn btn-primary mb-2">
        <i class="fas fa-edit"></i> Atur Anggaran
    </a>

    <form action="/admin/budgets" method="GET" class="form-inline mb-2">
        <select name="location" class="form-control mr-2">
            <option value="">Semua Kapal</option>
            @foreach($locations as $location)
                <option value="{{ $location->id }}" @selected($locationId == $location->id)>
                    {{ $location->name }}
                </option>
            @endforeach
        </select>

        <input type="number" name="year" value="{{ $year }}" class="form-control mr-2" style="width: 110px" min="2000" max="2100">

        <button class="btn btn-secondary">Tampilkan</button>
    </form>

</div>

<div class="row">
    <div class="col-md-4">
        <div class="small-box bg-info">
            <div class="inner">
                <h4>Rp {{ number_format($totalBudget, 0, ',', '.') }}</h4>
                <p>Total Anggaran</p>
            </div>
            <div class="icon"><i class="fas fa-wallet"></i></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="small-box bg-warning">
            <div class="inner">
                <h4>Rp {{ number_format($totalActual, 0, ',', '.') }}</h4>
                <p>Realisasi (Purchasing diterima)</p>
            </div>
            <div class="icon"><i class="fas fa-receipt"></i></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="small-box {{ $totalActual > $totalBudget ? 'bg-danger' : 'bg-success' }}">
            <div class="inner">
                <h4>Rp {{ number_format($totalBudget - $totalActual, 0, ',', '.') }}</h4>
                <p>
                    Sisa Anggaran
                    @if($totalBudget > 0)
                        ({{ number_format($totalActual / $totalBudget * 100, 1, ',', '.') }}% terserap)
                    @endif
                </p>
            </div>
            <div class="icon"><i class="fas fa-balance-scale"></i></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Anggaran vs Realisasi per Bulan</h3>
    </div>
    <div class="card-body">
        <div class="chart-wrap"><canvas id="budgetChart"></canvas></div>
    </div>
</div>

<div class="row">

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Per Kategori</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Kategori</th>
                            <th class="text-right">Anggaran</th>
                            <th class="text-right">Realisasi</th>
                            <th class="text-right">Selisih</th>
                            <th class="text-right">Serapan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($byCategory as $category => $row)
                            @include('admin.budgets._row', ['label' => $category, 'budget' => $row['budget'], 'actual' => $row['actual']])
                        @endforeach
                    </tbody>
                    <tfoot>
                        @include('admin.budgets._row', ['label' => 'Total', 'budget' => $totalBudget, 'actual' => $totalActual, 'tag' => 'th'])
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Per Bulan</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Bulan</th>
                            <th class="text-right">Anggaran</th>
                            <th class="text-right">Realisasi</th>
                            <th class="text-right">Selisih</th>
                            <th class="text-right">Serapan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($monthly as $month => $row)
                            @include('admin.budgets._row', ['label' => \App\Models\Budget::MONTHS[$month], 'budget' => $row['budget'], 'actual' => $row['actual']])
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

@stop

@section('css')
<style>
    .chart-wrap { position: relative; height: 300px; width: 100%; }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script src="{{ asset('js/chart-helpers.js') }}"></script>
<script>
    OceanCharts.compare('budgetChart', @json(array_values(\App\Models\Budget::MONTHS)), [
        { label: 'Anggaran', data: @json(array_column($monthly, 'budget')), color: '#0057A8' },
        { label: 'Realisasi', data: @json(array_column($monthly, 'actual')), color: '#F59E0B' }
    ]);
</script>
@stop
