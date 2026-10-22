@extends('layouts.admin')

@section('title', ($observation ? 'Edit' : 'Tambah') . ' Observasi')

@section('content_header')
    <h1>{{ $observation ? 'Edit ' . $observation->code() : 'Tambah Observasi (OSCARD)' }}</h1>
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

<form action="/admin/safety-observations{{ $observation ? '/' . $observation->id : '' }}"
      method="POST"
      enctype="multipart/form-data">

    @csrf

    @if($observation)
        @method('PUT')
    @endif

    <div class="row">

        <div class="col-md-4 form-group mb-3">

            <label>Kapal / Office</label>

            <select name="master_location_id" class="form-control" required>
                <option value="">-- Pilih --</option>
                @foreach($locations as $location)
                    <option value="{{ $location->id }}"
                            @selected(old('master_location_id', $observation->master_location_id ?? '') == $location->id)>
                        {{ $location->name }}
                    </option>
                @endforeach
            </select>

        </div>

        <div class="col-md-4 form-group mb-3">

            <label>Tanggal Observasi</label>

            <input type="date"
                   name="observed_date"
                   value="{{ old('observed_date', $observation?->observed_date?->format('Y-m-d') ?? today()->format('Y-m-d')) }}"
                   class="form-control"
                   required>

        </div>

        <div class="col-md-4 form-group mb-3">

            <label>Kategori</label>

            <select name="category" class="form-control" required>
                @foreach(array_keys(\App\Models\SafetyObservation::CATEGORIES) as $option)
                    <option value="{{ $option }}" @selected(old('category', $observation->category ?? '') === $option)>
                        {{ $option }}
                    </option>
                @endforeach
            </select>

        </div>

    </div>

    <div class="form-group mb-3">

        <label>Deskripsi Observasi</label>

        <textarea name="description" class="form-control" rows="3" required>{{ old('description', $observation->description ?? '') }}</textarea>

    </div>

    <div class="form-group mb-3">

        <label>Tindakan Segera <small class="text-muted">(opsional)</small></label>

        <textarea name="immediate_action" class="form-control" rows="2">{{ old('immediate_action', $observation->immediate_action ?? '') }}</textarea>

    </div>

    <div class="row">

        <div class="col-md-3 form-group mb-3">

            <label>PIC <small class="text-muted">(opsional)</small></label>

            <input type="text"
                   name="pic"
                   value="{{ old('pic', $observation->pic ?? '') }}"
                   class="form-control"
                   placeholder="mis. Chief Officer">

        </div>

        <div class="col-md-3 form-group mb-3">

            <label>Due Date <small class="text-muted">(opsional)</small></label>

            <input type="date"
                   name="due_date"
                   value="{{ old('due_date', $observation?->due_date?->format('Y-m-d')) }}"
                   class="form-control">

        </div>

        <div class="col-md-3 form-group mb-3">

            <label>Status</label>

            <select name="status" class="form-control" required>
                @foreach(\App\Models\SafetyObservation::STATUSES as $option)
                    <option value="{{ $option }}" @selected(old('status', $observation->status ?? 'Open') === $option)>
                        {{ $option }}
                    </option>
                @endforeach
            </select>

        </div>

        <div class="col-md-3 form-group mb-3">

            <label>Tanggal Close <small class="text-muted">(jika Closed)</small></label>

            <input type="date"
                   name="closed_date"
                   value="{{ old('closed_date', $observation?->closed_date?->format('Y-m-d')) }}"
                   class="form-control">

        </div>

    </div>

    @include('partials.evidence-input', ['model' => $observation, 'url' => "/admin/safety-observations/{$observation?->id}/evidence"])

    <button class="btn btn-primary">Simpan</button>

    <a href="/admin/safety-observations" class="btn btn-secondary">Batal</a>

</form>

@stop
