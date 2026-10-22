@extends('layouts.admin')

@section('title', ($crew ? 'Edit' : 'Tambah') . ' Kru')

@section('content_header')
    <h1>{{ $crew ? 'Edit' : 'Tambah' }} Kru</h1>
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

<form action="/admin/crews{{ $crew ? '/' . $crew->id : '' }}" method="POST">

    @csrf

    @if($crew)
        @method('PUT')
    @endif

    <div class="form-group mb-3">

        <label>Nama</label>

        <input type="text"
               name="name"
               value="{{ old('name', $crew->name ?? '') }}"
               class="form-control"
               required>

    </div>

    <div class="form-group mb-3">

        <label>Jabatan (Rank)</label>

        <input type="text"
               name="rank"
               value="{{ old('rank', $crew->rank ?? '') }}"
               class="form-control"
               placeholder="Master, Chief Officer, Chief Engineer, AB, ..."
               required>

    </div>

    <div class="form-group mb-3">

        <label>Kapal</label>

        <select name="master_location_id" class="form-control">
            <option value="">Darat / Standby</option>
            @foreach($locations as $location)
                <option value="{{ $location->id }}"
                        @selected(old('master_location_id', $crew->master_location_id ?? '') == $location->id)>
                    {{ $location->name }}
                </option>
            @endforeach
        </select>

    </div>

    <div class="form-group mb-3">

        <label>No. Seaman Book <small class="text-muted">(opsional)</small></label>

        <input type="text"
               name="seaman_book_number"
               value="{{ old('seaman_book_number', $crew->seaman_book_number ?? '') }}"
               class="form-control">

    </div>

    <div class="row">

        <div class="col-md-6 form-group mb-3">

            <label>Tanggal Sign On</label>

            <input type="date"
                   name="sign_on_date"
                   value="{{ old('sign_on_date', $crew?->sign_on_date?->format('Y-m-d')) }}"
                   class="form-control">

        </div>

        <div class="col-md-6 form-group mb-3">

            <label>Rencana Sign Off</label>

            <input type="date"
                   name="sign_off_date"
                   value="{{ old('sign_off_date', $crew?->sign_off_date?->format('Y-m-d')) }}"
                   class="form-control">

        </div>

    </div>

    <div class="form-group mb-3">

        <label>Catatan <small class="text-muted">(opsional)</small></label>

        <textarea name="remarks" class="form-control" rows="3">{{ old('remarks', $crew->remarks ?? '') }}</textarea>

    </div>

    <button class="btn btn-primary">Simpan</button>

    <a href="/admin/crews{{ $crew ? '/' . $crew->id : '' }}" class="btn btn-secondary">Batal</a>

</form>

@stop
