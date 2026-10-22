@extends('layouts.admin')

@section('title', 'Assurance Program')

@section('content_header')
    <h1>Assurance Program</h1>
@stop

@section('content')

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if($summary['overdue'] || $summary['dueSoon'])
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i>
        {{ $summary['overdue'] }} assurance overdue, {{ $summary['dueSoon'] }} jatuh tempo dalam {{ \App\Models\Crew::WARNING_DAYS }} hari.
        Total NC open: {{ $summary['ncOpen'] }}.
    </div>
@endif

<div class="d-flex flex-wrap justify-content-between mb-3">

    <a href="/admin/assurance-programs/create" class="btn btn-primary mb-2">
        <i class="fas fa-plus"></i> Tambah Assurance
    </a>

    <form action="/admin/assurance-programs" method="GET" class="form-inline mb-2">
        <select name="location" class="form-control mr-2 mb-1">
            <option value="">Semua Subject</option>
            @foreach($locations as $location)
                <option value="{{ $location->id }}" @selected($locationId == $location->id)>
                    {{ $location->name }}
                </option>
            @endforeach
        </select>

        <select name="type" class="form-control mr-2 mb-1">
            <option value="">Semua Jenis</option>
            @foreach(\App\Models\AssuranceProgram::TYPES as $option)
                <option value="{{ $option }}" @selected($type === $option)>{{ $option }}</option>
            @endforeach
        </select>

        <select name="status" class="form-control mr-2 mb-1">
            <option value="">Semua Status</option>
            @foreach(array_keys(\App\Models\AssuranceProgram::STATUS_BADGES) as $option)
                <option value="{{ $option }}" @selected($status === $option)>{{ $option }}</option>
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
            <th>No</th>
            <th>Subject</th>
            <th>Assurance</th>
            <th>Plan</th>
            <th>Year</th>
            <th>Actual</th>
            <th class="text-right">Due (hari)</th>
            <th>Status</th>
            <th class="text-center">NC Open / Close</th>
            <th>Remarks</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody>
        @forelse($data as $item)
        @php $itemStatus = $item->status(); @endphp
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $item->location->name ?? '-' }}</td>
            <td>{{ $item->assurance_type }}</td>
            <td>{{ $item->planned_date->format('d-m-Y') }}</td>
            <td>{{ $item->planned_date->year }}</td>
            <td>{{ $item->actual_date?->format('d-m-Y') ?? '-' }}</td>
            <td class="text-right {{ $itemStatus === 'Overdue' ? 'text-danger' : '' }}">
                {{ $item->actual_date ? '-' : $item->daysToDue() }}
            </td>
            <td>
                <span class="badge badge-{{ \App\Models\AssuranceProgram::STATUS_BADGES[$itemStatus] }}">
                    {{ $itemStatus }}
                </span>
            </td>
            <td class="text-center">
                <span class="{{ $item->nc_open ? 'text-danger font-weight-bold' : '' }}">{{ $item->nc_open }}</span>
                / {{ $item->nc_closed }}
            </td>
            <td>
                {{ $item->remarks ?? '-' }}
                @if($item->evidence_path)
                    <br>
                    <a href="/admin/assurance-programs/{{ $item->id }}/evidence" target="_blank">
                        <i class="fas fa-paperclip"></i> {{ $item->evidence_name }}
                    </a>
                @endif
            </td>
            <td>
                <a href="/admin/assurance-programs/{{ $item->id }}/edit" class="btn btn-warning btn-sm mb-1">
                    Edit
                </a>

                <form action="/admin/assurance-programs/{{ $item->id }}"
                      method="POST"
                      class="d-inline"
                      onsubmit="return confirm('Hapus assurance ini?')">
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
            <td colspan="11" class="text-center text-muted">Belum ada assurance program</td>
        </tr>
        @endforelse
    </tbody>
</table>

@stop
