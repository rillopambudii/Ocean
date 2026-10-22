@extends('layouts.admin')

@section('title', 'Defect Register')

@section('content_header')
    <h1>Defect Register</h1>
@stop

@section('content')

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="d-flex flex-wrap justify-content-between mb-3">

    <a href="/admin/damage-reports/create" class="btn btn-primary mb-2">
        <i class="fas fa-plus"></i> Tambah Laporan
    </a>

    <form action="/admin/damage-reports" method="GET" class="form-inline mb-2">
        <select name="location" class="form-control mr-2">
            <option value="">Semua Kapal</option>
            @foreach($locations as $location)
                <option value="{{ $location->id }}" @selected($locationId == $location->id)>
                    {{ $location->name }}
                </option>
            @endforeach
        </select>

        <select name="status" class="form-control mr-2">
            <option value="">Semua Status</option>
            @foreach(\App\Models\DamageReport::STATUSES as $option)
                <option value="{{ $option }}" @selected($status === $option)>{{ $option }}</option>
            @endforeach
        </select>

        <button class="btn btn-secondary">Filter</button>
    </form>

</div>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>Kapal</th>
            <th>Kerusakan</th>
            <th>Prioritas</th>
            <th>Status</th>
            <th>Tindakan</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody>
        @forelse($data as $item)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $item->reported_date->format('d-m-Y') }}</td>
            <td>{{ $item->location->name ?? '-' }}</td>
            <td>
                <strong>{{ $item->title }}</strong>
                @if($item->description)
                    <br><small class="text-muted">{{ $item->description }}</small>
                @endif
            </td>
            <td>
                <span class="badge badge-{{ \App\Models\DamageReport::PRIORITIES[$item->priority] ?? 'secondary' }}">
                    {{ $item->priority }}
                </span>
            </td>
            <td>
                @if($item->status === 'Closed')
                    <span class="badge badge-success">Closed</span>
                    <br><small class="text-muted">{{ $item->closed_date?->format('d-m-Y') }}</small>
                @else
                    <span class="badge badge-danger">Open</span>
                    <br><small class="text-muted">{{ (int) $item->reported_date->diffInDays(today()) }} hari</small>
                @endif
            </td>
            <td>{{ $item->action_taken ?? '-' }}</td>
            <td>
                <a href="/admin/damage-reports/{{ $item->id }}/edit" class="btn btn-warning btn-sm mb-1">
                    Edit
                </a>

                <form action="/admin/damage-reports/{{ $item->id }}"
                      method="POST"
                      class="d-inline"
                      onsubmit="return confirm('Hapus laporan ini?')">
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
            <td colspan="8" class="text-center text-muted">Belum ada laporan</td>
        </tr>
        @endforelse
    </tbody>
</table>

@stop
