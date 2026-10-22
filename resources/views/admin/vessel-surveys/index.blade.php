@extends('layouts.admin')

@section('title', 'Docking Plan')

@section('content_header')
    <h1>Docking Plan</h1>
@stop

@section('content')

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if($alerts)
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i>
        {{ $alerts }} jadwal terlambat atau jatuh tempo dalam {{ \App\Models\Crew::WARNING_DAYS }} hari.
    </div>
@endif

<div class="d-flex flex-wrap justify-content-between mb-3">

    <a href="/admin/vessel-surveys/create" class="btn btn-primary mb-2">
        <i class="fas fa-plus"></i> Tambah Jadwal
    </a>

    <form action="/admin/vessel-surveys" method="GET" class="form-inline mb-2">
        <select name="location" class="form-control mr-2">
            <option value="">Semua Kapal</option>
            @foreach($locations as $location)
                <option value="{{ $location->id }}" @selected($locationId == $location->id)>
                    {{ $location->name }}
                </option>
            @endforeach
        </select>

        <button class="btn btn-secondary">Filter</button>
    </form>

</div>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>No</th>
            <th>Kapal</th>
            <th>Jenis</th>
            <th>Rencana</th>
            <th>Realisasi</th>
            <th>Status</th>
            <th>Catatan</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody>
        @forelse($data as $item)
        @php $status = $item->status(); @endphp
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $item->location->name ?? '-' }}</td>
            <td>{{ $item->survey_type }}</td>
            <td>{{ $item->planned_date->format('d-m-Y') }}</td>
            <td>{{ $item->actual_date?->format('d-m-Y') ?? '-' }}</td>
            <td>
                <span class="badge badge-{{ \App\Models\VesselSurvey::STATUS_BADGES[$status] }}">
                    {{ $status }}
                </span>
            </td>
            <td>{{ $item->remarks ?? '-' }}</td>
            <td>
                <a href="/admin/vessel-surveys/{{ $item->id }}/edit" class="btn btn-warning btn-sm mb-1">
                    Edit
                </a>

                <form action="/admin/vessel-surveys/{{ $item->id }}"
                      method="POST"
                      class="d-inline"
                      onsubmit="return confirm('Hapus jadwal ini?')">
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
            <td colspan="8" class="text-center text-muted">Belum ada jadwal</td>
        </tr>
        @endforelse
    </tbody>
</table>

@stop
