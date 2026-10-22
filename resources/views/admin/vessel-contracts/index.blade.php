@extends('layouts.admin')

@section('title', 'Hire & Contract Status')

@section('content_header')
    <h1>Hire & Contract Status</h1>
@stop

@section('content')

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<a href="/admin/vessel-contracts/create" class="btn btn-primary mb-3">
    <i class="fas fa-plus"></i> Tambah Kontrak
</a>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Status Hire per Kapal</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>Kapal</th>
                    <th>Hire Status</th>
                    <th>Charterer Saat Ini</th>
                    <th>Periode</th>
                    <th class="text-right">Sisa Hari</th>
                    <th>Status Kontrak</th>
                    <th>Kontrak Berikutnya</th>
                    <th>Docking Plan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($fleet as $row)
                <tr>
                    <td>{{ $row['vessel']->name }}</td>
                    <td>
                        <span class="badge badge-{{ $row['current'] ? 'success' : 'secondary' }}">
                            {{ $row['current'] ? 'On hire' : 'Off hire' }}
                        </span>
                    </td>
                    <td>{{ $row['current']->charterer ?? '-' }}</td>
                    <td>
                        @if($row['current'])
                            {{ $row['current']->start_date->format('d-m-Y') }} → {{ $row['current']->end_date->format('d-m-Y') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-right">{{ $row['current']?->daysToEnd() ?? '-' }}</td>
                    <td>
                        @if($row['current'])
                            @php $contractStatus = $row['current']->status(); @endphp
                            <span class="badge badge-{{ \App\Models\VesselContract::STATUS_BADGES[$contractStatus] }}">
                                {{ $contractStatus }}
                            </span>
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if($row['next'])
                            {{ $row['next']->charterer }}
                            <br><small class="text-muted">mulai {{ $row['next']->start_date->format('d-m-Y') }}</small>
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $row['docking']?->planned_date->format('d-m-Y') ?? 'TBC' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted">Belum ada kapal di Master Data</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer text-muted small">
        Docking plan diambil dari jadwal Docking terdekat yang belum selesai di Technical → Docking Plan.
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Semua Kontrak</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-bordered table-striped mb-0">
            <thead>
                <tr>
                    <th>Kapal</th>
                    <th>Charterer</th>
                    <th>Mulai</th>
                    <th>Selesai</th>
                    <th>Status</th>
                    <th>Remarks</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($contracts as $item)
                @php $contractStatus = $item->status(); @endphp
                <tr>
                    <td>{{ $item->location->name ?? '-' }}</td>
                    <td>{{ $item->charterer }}</td>
                    <td>{{ $item->start_date->format('d-m-Y') }}</td>
                    <td>{{ $item->end_date->format('d-m-Y') }}</td>
                    <td>
                        <span class="badge badge-{{ \App\Models\VesselContract::STATUS_BADGES[$contractStatus] }}">
                            {{ $contractStatus }}
                        </span>
                    </td>
                    <td>{{ $item->remarks ?? '-' }}</td>
                    <td>
                        <a href="/admin/vessel-contracts/{{ $item->id }}/edit" class="btn btn-warning btn-sm mb-1">
                            Edit
                        </a>

                        <form action="/admin/vessel-contracts/{{ $item->id }}"
                              method="POST"
                              class="d-inline"
                              onsubmit="return confirm('Hapus kontrak ini?')">
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
                    <td colspan="7" class="text-center text-muted">Belum ada kontrak</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@stop
