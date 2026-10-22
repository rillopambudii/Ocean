@extends('layouts.admin')

@section('title', 'Laporan')

@section('content_header')
    <h1>Laporan</h1>
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

<div class="card">
    <div class="card-body">
        <form action="/admin/laporan/export" method="GET">
            <div class="row">
                <div class="col-md-4 mb-2">
                    <label class="small mb-0">Laporan</label>
                    <select name="report" class="form-control" required>
                        @foreach($reports->groupBy('module') as $module => $items)
                            <optgroup label="{{ $module }}">
                                @foreach($items as $key => $report)
                                    <option value="{{ $key }}" @selected(old('report') === $key)>{{ $report['title'] }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 mb-2">
                    <label class="small mb-0">Kapal</label>
                    <select name="location" class="form-control">
                        <option value="">Semua Kapal</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 mb-2">
                    <label class="small mb-0">Tahun</label>
                    <select name="year" class="form-control">
                        <option value="">Semua</option>
                        @for($year = today()->year + 1; $year >= today()->year - 5; $year--)
                            <option value="{{ $year }}" @selected($year === today()->year)>{{ $year }}</option>
                        @endfor
                    </select>
                </div>

                <div class="col-md-3 mb-2 d-flex align-items-end">
                    <button name="format" value="excel" class="btn btn-success mr-2">
                        <i class="fas fa-file-excel"></i> Excel
                    </button>
                    <button name="format" value="pdf" class="btn btn-danger">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>
                </div>
            </div>
        </form>

        <small class="text-muted">
            Filter tahun memakai tanggal utama tiap register (mis. tanggal rencana, tanggal laporan, atau tanggal expired).
        </small>
    </div>
</div>

@stop
