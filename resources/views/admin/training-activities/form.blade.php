@extends('layouts.admin')

@section('title', ($activity ? 'Edit' : 'Tambah') . ' Training / Drill')

@section('content_header')
    <h1>{{ $activity ? 'Edit ' . $activity->code() : 'Tambah Training / Drill' }}</h1>
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

<form action="/admin/training-activities{{ $activity ? '/' . $activity->id : '' }}"
      method="POST"
      enctype="multipart/form-data">

    @csrf

    @if($activity)
        @method('PUT')
    @endif

    <div class="row">

        <div class="col-md-6 form-group mb-3">

            <label>Jenis Kegiatan</label>

            <select name="activity_type" class="form-control" required>
                @foreach(array_keys(\App\Models\TrainingActivity::TYPES) as $option)
                    <option value="{{ $option }}" @selected(old('activity_type', $activity->activity_type ?? '') === $option)>
                        {{ $option }}
                    </option>
                @endforeach
            </select>

        </div>

        <div class="col-md-6 form-group mb-3">

            <label>Kapal / Office</label>

            <select name="master_location_id" class="form-control" required>
                <option value="">-- Pilih --</option>
                @foreach($locations as $location)
                    <option value="{{ $location->id }}"
                            @selected(old('master_location_id', $activity->master_location_id ?? '') == $location->id)>
                        {{ $location->name }}
                    </option>
                @endforeach
            </select>

        </div>

    </div>

    <div class="form-group mb-3">

        <label>Topik</label>

        <input type="text"
               name="topic"
               value="{{ old('topic', $activity->topic ?? '') }}"
               class="form-control"
               placeholder="mis. Fire drill, Abandon ship drill, Sharing knowledge ISM Code"
               required>

    </div>

    <div class="row">

        <div class="col-md-3 form-group mb-3">

            <label>Tanggal Rencana</label>

            <input type="date"
                   name="planned_date"
                   value="{{ old('planned_date', $activity?->planned_date?->format('Y-m-d')) }}"
                   class="form-control"
                   required>

        </div>

        <div class="col-md-3 form-group mb-3">

            <label>Tanggal Aktual <small class="text-muted">(jika sudah)</small></label>

            <input type="date"
                   name="actual_date"
                   value="{{ old('actual_date', $activity?->actual_date?->format('Y-m-d')) }}"
                   class="form-control">

        </div>

        <div class="col-md-3 form-group mb-3">

            <label>Jumlah Peserta <small class="text-muted">(opsional)</small></label>

            <input type="number"
                   name="participants"
                   value="{{ old('participants', $activity->participants ?? '') }}"
                   class="form-control"
                   min="0">

        </div>

        <div class="col-md-3 form-group mb-3">

            <label>Instruktur <small class="text-muted">(opsional)</small></label>

            <input type="text"
                   name="instructor"
                   value="{{ old('instructor', $activity->instructor ?? '') }}"
                   class="form-control">

        </div>

    </div>

    @include('partials.evidence-input', ['model' => $activity, 'url' => "/admin/training-activities/{$activity?->id}/evidence"])

    <button class="btn btn-primary">Simpan</button>

    <a href="/admin/training-activities" class="btn btn-secondary">Batal</a>

</form>

@stop
