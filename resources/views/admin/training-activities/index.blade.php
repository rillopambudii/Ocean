@extends('layouts.admin')

@section('title', 'Training & Drills')

@section('content_header')
    <h1>Training & Drills</h1>
@stop

@section('content')

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if($overdue)
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i>
        {{ $overdue }} kegiatan sudah lewat tanggal rencana dan belum dilaksanakan.
    </div>
@endif

<div class="d-flex flex-wrap justify-content-between mb-3">

    <a href="/admin/training-activities/create" class="btn btn-primary mb-2">
        <i class="fas fa-plus"></i> Tambah Kegiatan
    </a>

    <form action="/admin/training-activities" method="GET" class="form-inline mb-2">
        <select name="location" class="form-control mr-2 mb-1">
            <option value="">Semua Kapal / Office</option>
            @foreach($locations as $location)
                <option value="{{ $location->id }}" @selected($locationId == $location->id)>
                    {{ $location->name }}
                </option>
            @endforeach
        </select>

        <select name="type" class="form-control mr-2 mb-1">
            <option value="">Semua Jenis</option>
            @foreach(array_keys(\App\Models\TrainingActivity::TYPES) as $option)
                <option value="{{ $option }}" @selected($type === $option)>{{ $option }}</option>
            @endforeach
        </select>

        <input type="number" name="year" value="{{ $year }}" class="form-control mr-2 mb-1"
               style="width: 110px" placeholder="Tahun" min="2000" max="2100">

        <button class="btn btn-secondary mb-1">Filter</button>
    </form>

</div>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Jenis</th>
            <th>Topik</th>
            <th>Kapal / Office</th>
            <th>Plan</th>
            <th>Actual</th>
            <th class="text-right">Peserta</th>
            <th>Instruktur</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody>
        @forelse($data as $item)
        @php $itemStatus = $item->status(); @endphp
        <tr>
            <td>{{ $item->code() }}</td>
            <td>{{ $item->activity_type }}</td>
            <td>
                {{ $item->topic }}
                @if($item->evidence_path)
                    <br>
                    <a href="/admin/training-activities/{{ $item->id }}/evidence" target="_blank">
                        <i class="fas fa-paperclip"></i> {{ $item->evidence_name }}
                    </a>
                @endif
            </td>
            <td>{{ $item->location->name ?? '-' }}</td>
            <td>{{ $item->planned_date->format('d-m-Y') }}</td>
            <td>{{ $item->actual_date?->format('d-m-Y') ?? '-' }}</td>
            <td class="text-right">{{ $item->participants ?? '-' }}</td>
            <td>{{ $item->instructor ?? '-' }}</td>
            <td>
                <span class="badge badge-{{ \App\Models\TrainingActivity::STATUS_BADGES[$itemStatus] }}">
                    {{ $itemStatus }}
                </span>
            </td>
            <td>
                <a href="/admin/training-activities/{{ $item->id }}/edit" class="btn btn-warning btn-sm mb-1">
                    Edit
                </a>

                <form action="/admin/training-activities/{{ $item->id }}"
                      method="POST"
                      class="d-inline"
                      onsubmit="return confirm('Hapus kegiatan ini?')">
                    @csrf
                    @method('DELETE')

                    <button class="btn btn-danger btn-sm mb-1">
                        Hapus
                    </button>
                </form>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="10" class="text-center text-muted">Belum ada kegiatan</td>
        </tr>
        @endforelse
    </tbody>
</table>

@stop
