@extends('layouts.admin')

@section('title', 'OSCARD & Observations')

@section('content_header')
    <h1>OSCARD & Observations</h1>
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
        {{ $overdue }} observasi masih Open dan sudah melewati due date.
    </div>
@endif

<div class="d-flex flex-wrap justify-content-between mb-3">

    <a href="/admin/safety-observations/create" class="btn btn-primary mb-2">
        <i class="fas fa-plus"></i> Tambah Observasi
    </a>

    <form action="/admin/safety-observations" method="GET" class="form-inline mb-2">
        <select name="location" class="form-control mr-2 mb-1">
            <option value="">Semua Kapal / Office</option>
            @foreach($locations as $location)
                <option value="{{ $location->id }}" @selected($locationId == $location->id)>
                    {{ $location->name }}
                </option>
            @endforeach
        </select>

        <select name="category" class="form-control mr-2 mb-1">
            <option value="">Semua Kategori</option>
            @foreach(array_keys(\App\Models\SafetyObservation::CATEGORIES) as $option)
                <option value="{{ $option }}" @selected($category === $option)>{{ $option }}</option>
            @endforeach
        </select>

        <select name="status" class="form-control mr-2 mb-1">
            <option value="">Semua Status</option>
            @foreach(\App\Models\SafetyObservation::STATUSES as $option)
                <option value="{{ $option }}" @selected($status === $option)>{{ $option }}</option>
            @endforeach
        </select>

        <button class="btn btn-secondary mb-1">Filter</button>
    </form>

</div>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Tanggal</th>
            <th>Kapal / Office</th>
            <th>Kategori</th>
            <th>Observasi</th>
            <th>PIC</th>
            <th>Due Date</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody>
        @forelse($data as $item)
        <tr>
            <td>{{ $item->code() }}</td>
            <td>{{ $item->observed_date->format('d-m-Y') }}</td>
            <td>{{ $item->location->name ?? '-' }}</td>
            <td>
                <span class="badge badge-{{ \App\Models\SafetyObservation::CATEGORIES[$item->category] ?? 'secondary' }}">
                    {{ $item->category }}
                </span>
            </td>
            <td>
                {{ $item->description }}
                @if($item->immediate_action)
                    <br><small class="text-muted">Tindakan: {{ $item->immediate_action }}</small>
                @endif
                @if($item->evidence_path)
                    <br>
                    <a href="/admin/safety-observations/{{ $item->id }}/evidence" target="_blank">
                        <i class="fas fa-paperclip"></i> {{ $item->evidence_name }}
                    </a>
                @endif
            </td>
            <td>{{ $item->pic ?? '-' }}</td>
            <td class="{{ $item->isOverdue() ? 'text-danger font-weight-bold' : '' }}">
                {{ $item->due_date?->format('d-m-Y') ?? '-' }}
            </td>
            <td>
                @if($item->status === 'Closed')
                    <span class="badge badge-success">Closed</span>
                    <br><small class="text-muted">{{ $item->closed_date?->format('d-m-Y') }}</small>
                @else
                    <span class="badge badge-{{ $item->isOverdue() ? 'danger' : 'warning' }}">Open</span>
                @endif
            </td>
            <td>
                <a href="/admin/safety-observations/{{ $item->id }}/edit" class="btn btn-warning btn-sm mb-1">
                    Edit
                </a>

                <form action="/admin/safety-observations/{{ $item->id }}"
                      method="POST"
                      class="d-inline"
                      onsubmit="return confirm('Hapus observasi ini?')">
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
            <td colspan="9" class="text-center text-muted">Belum ada observasi</td>
        </tr>
        @endforelse
    </tbody>
</table>

@stop
