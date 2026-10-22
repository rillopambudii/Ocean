@extends('layouts.admin')

@section('title', 'Atur Anggaran')

@section('content_header')
    <h1>Atur Anggaran {{ $location->name }} — {{ $year }}</h1>
@stop

@section('content')

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="/admin/budgets/plan" method="GET" class="form-inline mb-3">
    <select name="location" class="form-control mr-2">
        @foreach($locations as $option)
            <option value="{{ $option->id }}" @selected($option->id == $location->id)>{{ $option->name }}</option>
        @endforeach
    </select>

    <input type="number" name="year" value="{{ $year }}" class="form-control mr-2" style="width: 110px" min="2000" max="2100">

    <button class="btn btn-secondary">Ganti</button>
</form>

<form action="/admin/budgets/plan" method="POST">

    @csrf

    <input type="hidden" name="master_location_id" value="{{ $location->id }}">
    <input type="hidden" name="year" value="{{ $year }}">

    <p class="text-muted">Nominal dalam Rupiah. Kosongkan bulan yang tidak dianggarkan.</p>

    <div class="table-responsive">
        <table class="table table-bordered table-sm budget-grid">
            <thead>
                <tr>
                    <th>Kategori</th>
                    @foreach(\App\Models\Budget::MONTHS as $label)
                        <th class="text-center">{{ $label }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach(\App\Models\PurchaseRequest::CATEGORIES as $category)
                <tr>
                    <th class="text-nowrap">{{ $category }}</th>
                    @foreach(array_keys(\App\Models\Budget::MONTHS) as $month)
                        <td>
                            <input type="number"
                                   name="amounts[{{ $category }}][{{ $month }}]"
                                   value="{{ old("amounts.{$category}.{$month}", isset($amounts[$category][$month]) ? $amounts[$category][$month] : '') }}"
                                   class="form-control form-control-sm"
                                   min="0"
                                   step="any">
                        </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <button class="btn btn-primary">Simpan Anggaran</button>

    <a href="/admin/budgets?year={{ $year }}&location={{ $location->id }}" class="btn btn-secondary">Batal</a>

</form>

@stop

@section('css')
<style>
    .budget-grid input { min-width: 110px; }
</style>
@stop
