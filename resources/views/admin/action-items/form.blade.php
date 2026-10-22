@extends('layouts.admin')

@section('title', ($action ? 'Edit' : 'Tambah') . ' Action')

@section('content_header')
    <h1>{{ $action ? 'Edit ' . $action->code() : 'Tambah Action' }}</h1>
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

<form action="/admin/action-items{{ $action ? '/' . $action->id : '' }}"
      method="POST"
      enctype="multipart/form-data">

    @csrf

    @if($action)
        @method('PUT')
    @endif

    <input type="hidden" name="back" value="{{ $back }}">

    <div class="form-group mb-3">

        <label>Follow-up Item</label>

        <input type="text"
               name="title"
               value="{{ old('title', $action->title ?? '') }}"
               class="form-control"
               placeholder="mis. Docking material follow-up"
               required>

    </div>

    <div class="row">

        <div class="col-md-4 form-group mb-3">

            <label>Kapal / Office</label>

            <select name="master_location_id" class="form-control" required>
                <option value="">-- Pilih --</option>
                @foreach($locations as $location)
                    <option value="{{ $location->id }}"
                            @selected(old('master_location_id', $action->master_location_id ?? '') == $location->id)>
                        {{ $location->name }}
                    </option>
                @endforeach
            </select>

        </div>

        <div class="col-md-4 form-group mb-3">

            <label>Departemen (Lead)</label>

            <select name="department" class="form-control" required>
                @foreach(array_keys(\App\Models\ActionItem::DEPARTMENTS) as $option)
                    <option value="{{ $option }}"
                            @selected(old('department', $action->department ?? $defaultDepartment) === $option)>
                        {{ $option }}
                    </option>
                @endforeach
            </select>

        </div>

        <div class="col-md-4 form-group mb-3">

            <label>Departemen Pendukung <small class="text-muted">(opsional)</small></label>

            <select name="supporting_department" class="form-control">
                <option value="">-- Tidak ada --</option>
                @foreach(array_keys(\App\Models\ActionItem::DEPARTMENTS) as $option)
                    <option value="{{ $option }}"
                            @selected(old('supporting_department', $action->supporting_department ?? '') === $option)>
                        {{ $option }}
                    </option>
                @endforeach
            </select>

        </div>

    </div>

    <div class="row">

        <div class="col-md-4 form-group mb-3">

            <label>Lead PIC <small class="text-muted">(opsional)</small></label>

            <input type="text"
                   name="lead_pic"
                   value="{{ old('lead_pic', $action->lead_pic ?? '') }}"
                   class="form-control">

        </div>

        <div class="col-md-4 form-group mb-3">

            <label>Prioritas</label>

            <select name="priority" class="form-control" required>
                @foreach(array_keys(\App\Models\ActionItem::PRIORITIES) as $option)
                    <option value="{{ $option }}" @selected(old('priority', $action->priority ?? 'Medium') === $option)>
                        {{ $option }}
                    </option>
                @endforeach
            </select>

        </div>

        <div class="col-md-4 form-group mb-3">

            <label>Target <small class="text-muted">(opsional)</small></label>

            <input type="date"
                   name="target_date"
                   value="{{ old('target_date', $action?->target_date?->format('Y-m-d')) }}"
                   class="form-control">

        </div>

    </div>

    <div class="row">

        <div class="col-md-6 form-group mb-3">

            <label>Status</label>

            <select name="status" class="form-control" required>
                @foreach(array_keys(\App\Models\ActionItem::STATUSES) as $option)
                    <option value="{{ $option }}" @selected(old('status', $action->status ?? 'Open') === $option)>
                        {{ $option }}
                    </option>
                @endforeach
            </select>

        </div>

        <div class="col-md-6 form-group mb-3">

            <label>Tanggal Selesai <small class="text-muted">(jika Closed)</small></label>

            <input type="date"
                   name="actual_date"
                   value="{{ old('actual_date', $action?->actual_date?->format('Y-m-d')) }}"
                   class="form-control">

        </div>

    </div>

    <div class="form-group mb-3">

        <label>Next Action / Keputusan <small class="text-muted">(opsional)</small></label>

        <textarea name="next_action" class="form-control" rows="2">{{ old('next_action', $action->next_action ?? '') }}</textarea>

    </div>

    @include('partials.evidence-input', ['model' => $action, 'url' => "/admin/action-items/{$action?->id}/evidence"])

    <button class="btn btn-primary">Simpan</button>

    <a href="{{ $back }}" class="btn btn-secondary">Batal</a>

</form>

@stop
