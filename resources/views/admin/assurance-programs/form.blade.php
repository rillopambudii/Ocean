@extends('layouts.admin')

@section('title', ($program ? 'Edit' : 'Tambah') . ' Assurance')

@section('content_header')
    <h1>{{ $program ? 'Edit' : 'Tambah' }} Assurance</h1>
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

<form action="/admin/assurance-programs{{ $program ? '/' . $program->id : '' }}"
      method="POST"
      enctype="multipart/form-data">

    @csrf

    @if($program)
        @method('PUT')
    @endif

    <div class="row">

        <div class="col-md-6 form-group mb-3">

            <label>Subject (Kapal / Office)</label>

            <select name="master_location_id" class="form-control" required>
                <option value="">-- Pilih --</option>
                @foreach($locations as $location)
                    <option value="{{ $location->id }}"
                            @selected(old('master_location_id', $program->master_location_id ?? '') == $location->id)>
                        {{ $location->name }}
                    </option>
                @endforeach
            </select>

        </div>

        <div class="col-md-6 form-group mb-3">

            <label>Jenis Assurance</label>

            <select name="assurance_type" class="form-control" required>
                @foreach(\App\Models\AssuranceProgram::TYPES as $option)
                    <option value="{{ $option }}" @selected(old('assurance_type', $program->assurance_type ?? '') === $option)>
                        {{ $option }}
                    </option>
                @endforeach
            </select>

        </div>

    </div>

    <div class="row">

        <div class="col-md-6 form-group mb-3">

            <label>Tanggal Rencana (Plan)</label>

            <input type="date"
                   name="planned_date"
                   value="{{ old('planned_date', $program?->planned_date?->format('Y-m-d')) }}"
                   class="form-control"
                   required>

        </div>

        <div class="col-md-6 form-group mb-3">

            <label>Tanggal Aktual <small class="text-muted">(isi jika sudah dilaksanakan)</small></label>

            <input type="date"
                   name="actual_date"
                   value="{{ old('actual_date', $program?->actual_date?->format('Y-m-d')) }}"
                   class="form-control">

        </div>

    </div>

    <div class="row">

        <div class="col-md-6 form-group mb-3">

            <label>NC Open</label>

            <input type="number"
                   name="nc_open"
                   value="{{ old('nc_open', $program->nc_open ?? 0) }}"
                   class="form-control"
                   min="0">

        </div>

        <div class="col-md-6 form-group mb-3">

            <label>NC Close</label>

            <input type="number"
                   name="nc_closed"
                   value="{{ old('nc_closed', $program->nc_closed ?? 0) }}"
                   class="form-control"
                   min="0">

        </div>

    </div>

    <div class="form-group mb-3">

        <label>Remarks <small class="text-muted">(opsional)</small></label>

        <input type="text"
               name="remarks"
               value="{{ old('remarks', $program->remarks ?? '') }}"
               class="form-control"
               placeholder="mis. Annual, Interim, Due for intermediate, 3rd anniversary">

    </div>

    @include('partials.evidence-input', ['model' => $program, 'url' => "/admin/assurance-programs/{$program?->id}/evidence"])

    <button class="btn btn-primary">Simpan</button>

    <a href="/admin/assurance-programs" class="btn btn-secondary">Batal</a>

</form>

@stop
