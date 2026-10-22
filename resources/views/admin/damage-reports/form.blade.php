@extends('layouts.admin')

@section('title', ($report ? 'Edit' : 'Tambah') . ' Laporan Kerusakan')

@section('content_header')
    <h1>{{ $report ? 'Edit' : 'Tambah' }} Laporan Kerusakan</h1>
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

<form action="/admin/damage-reports{{ $report ? '/' . $report->id : '' }}" method="POST">

    @csrf

    @if($report)
        @method('PUT')
    @endif

    <div class="row">

        <div class="col-md-6 form-group mb-3">

            <label>Kapal</label>

            <select name="master_location_id" class="form-control" required>
                <option value="">-- Pilih Kapal --</option>
                @foreach($locations as $location)
                    <option value="{{ $location->id }}"
                            @selected(old('master_location_id', $report->master_location_id ?? '') == $location->id)>
                        {{ $location->name }}
                    </option>
                @endforeach
            </select>

        </div>

        <div class="col-md-6 form-group mb-3">

            <label>Tanggal Laporan</label>

            <input type="date"
                   name="reported_date"
                   value="{{ old('reported_date', $report?->reported_date?->format('Y-m-d') ?? today()->format('Y-m-d')) }}"
                   class="form-control"
                   required>

        </div>

    </div>

    <div class="form-group mb-3">

        <label>Kerusakan</label>

        <input type="text"
               name="title"
               value="{{ old('title', $report->title ?? '') }}"
               class="form-control"
               placeholder="mis. Main engine kiri overheat"
               required>

    </div>

    <div class="form-group mb-3">

        <label>Deskripsi <small class="text-muted">(opsional)</small></label>

        <textarea name="description" class="form-control" rows="3">{{ old('description', $report->description ?? '') }}</textarea>

    </div>

    <div class="row">

        <div class="col-md-4 form-group mb-3">

            <label>Prioritas</label>

            <select name="priority" class="form-control" required>
                @foreach(array_keys(\App\Models\DamageReport::PRIORITIES) as $priority)
                    <option value="{{ $priority }}" @selected(old('priority', $report->priority ?? 'Medium') === $priority)>
                        {{ $priority }}
                    </option>
                @endforeach
            </select>

        </div>

        <div class="col-md-4 form-group mb-3">

            <label>Status</label>

            <select name="status" class="form-control" required>
                @foreach(\App\Models\DamageReport::STATUSES as $option)
                    <option value="{{ $option }}" @selected(old('status', $report->status ?? 'Open') === $option)>
                        {{ $option }}
                    </option>
                @endforeach
            </select>

        </div>

        <div class="col-md-4 form-group mb-3">

            <label>Tanggal Selesai <small class="text-muted">(jika Closed)</small></label>

            <input type="date"
                   name="closed_date"
                   value="{{ old('closed_date', $report?->closed_date?->format('Y-m-d')) }}"
                   class="form-control">

        </div>

    </div>

    <div class="form-group mb-3">

        <label>Tindakan Perbaikan <small class="text-muted">(opsional)</small></label>

        <textarea name="action_taken" class="form-control" rows="3">{{ old('action_taken', $report->action_taken ?? '') }}</textarea>

    </div>

    <button class="btn btn-primary">Simpan</button>

    <a href="/admin/damage-reports" class="btn btn-secondary">Batal</a>

</form>

@stop
