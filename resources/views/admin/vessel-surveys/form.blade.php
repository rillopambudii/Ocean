@extends('layouts.admin')

@section('title', ($survey ? 'Edit' : 'Tambah') . ' Jadwal Docking & Survey')

@section('content_header')
    <h1>{{ $survey ? 'Edit' : 'Tambah' }} Jadwal Docking & Survey</h1>
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

<form action="/admin/vessel-surveys{{ $survey ? '/' . $survey->id : '' }}" method="POST">

    @csrf

    @if($survey)
        @method('PUT')
    @endif

    <div class="form-group mb-3">

        <label>Kapal</label>

        <select name="master_location_id" class="form-control" required>
            <option value="">-- Pilih Kapal --</option>
            @foreach($locations as $location)
                <option value="{{ $location->id }}"
                        @selected(old('master_location_id', $survey->master_location_id ?? '') == $location->id)>
                    {{ $location->name }}
                </option>
            @endforeach
        </select>

    </div>

    <div class="form-group mb-3">

        <label>Jenis</label>

        <select name="survey_type" class="form-control" required>
            @foreach(\App\Models\VesselSurvey::TYPES as $type)
                <option value="{{ $type }}" @selected(old('survey_type', $survey->survey_type ?? '') === $type)>
                    {{ $type }}
                </option>
            @endforeach
        </select>

    </div>

    <div class="row">

        <div class="col-md-6 form-group mb-3">

            <label>Tanggal Rencana</label>

            <input type="date"
                   name="planned_date"
                   value="{{ old('planned_date', $survey?->planned_date?->format('Y-m-d')) }}"
                   class="form-control"
                   required>

        </div>

        <div class="col-md-6 form-group mb-3">

            <label>Tanggal Realisasi <small class="text-muted">(isi jika sudah selesai)</small></label>

            <input type="date"
                   name="actual_date"
                   value="{{ old('actual_date', $survey?->actual_date?->format('Y-m-d')) }}"
                   class="form-control">

        </div>

    </div>

    <div class="form-group mb-3">

        <label>Catatan <small class="text-muted">(opsional)</small></label>

        <textarea name="remarks" class="form-control" rows="3">{{ old('remarks', $survey->remarks ?? '') }}</textarea>

    </div>

    <button class="btn btn-primary">Simpan</button>

    <a href="/admin/vessel-surveys" class="btn btn-secondary">Batal</a>

</form>

@stop
