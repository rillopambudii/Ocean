@extends('layouts.admin')

@section('title', ($contract ? 'Edit' : 'Tambah') . ' Kontrak')

@section('content_header')
    <h1>{{ $contract ? 'Edit' : 'Tambah' }} Kontrak Charter</h1>
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

<form action="/admin/vessel-contracts{{ $contract ? '/' . $contract->id : '' }}" method="POST">

    @csrf

    @if($contract)
        @method('PUT')
    @endif

    <div class="row">

        <div class="col-md-6 form-group mb-3">

            <label>Kapal</label>

            <select name="master_location_id" class="form-control" required>
                <option value="">-- Pilih Kapal --</option>
                @foreach($locations as $location)
                    <option value="{{ $location->id }}"
                            @selected(old('master_location_id', $contract->master_location_id ?? '') == $location->id)>
                        {{ $location->name }}
                    </option>
                @endforeach
            </select>

        </div>

        <div class="col-md-6 form-group mb-3">

            <label>Charterer / Klien</label>

            <input type="text"
                   name="charterer"
                   value="{{ old('charterer', $contract->charterer ?? '') }}"
                   class="form-control"
                   required>

        </div>

    </div>

    <div class="row">

        <div class="col-md-6 form-group mb-3">

            <label>Tanggal Mulai</label>

            <input type="date"
                   name="start_date"
                   value="{{ old('start_date', $contract?->start_date?->format('Y-m-d')) }}"
                   class="form-control"
                   required>

        </div>

        <div class="col-md-6 form-group mb-3">

            <label>Tanggal Selesai</label>

            <input type="date"
                   name="end_date"
                   value="{{ old('end_date', $contract?->end_date?->format('Y-m-d')) }}"
                   class="form-control"
                   required>

        </div>

    </div>

    <div class="form-group mb-3">

        <label>Remarks / ROB <small class="text-muted">(opsional)</small></label>

        <textarea name="remarks" class="form-control" rows="3">{{ old('remarks', $contract->remarks ?? '') }}</textarea>

    </div>

    <p class="text-muted small">Untuk kontrak berikutnya (planned), cukup tambahkan kontrak baru dengan tanggal mulai di masa depan.</p>

    <button class="btn btn-primary">Simpan</button>

    <a href="/admin/vessel-contracts" class="btn btn-secondary">Batal</a>

</form>

@stop
