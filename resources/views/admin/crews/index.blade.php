@extends('layouts.admin')

@section('title', 'Crew Register')

@section('content_header')
    <h1>Crew Register</h1>
@stop

@section('content')

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if($warnings['contracts'] || $warnings['certificates'])
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i>
        {{ $warnings['contracts'] }} kontrak dan {{ $warnings['certificates'] }} sertifikat
        sudah habis atau habis dalam {{ \App\Models\Crew::WARNING_DAYS }} hari.
    </div>
@endif

<div class="d-flex flex-wrap justify-content-between mb-3">

    <a href="/admin/crews/create" class="btn btn-primary mb-2">
        <i class="fas fa-plus"></i> Tambah Kru
    </a>

    <form action="/admin/crews" method="GET" class="form-inline mb-2">
        <select name="location" class="form-control mr-2">
            <option value="">Semua Kapal</option>
            @foreach($locations as $location)
                <option value="{{ $location->id }}" @selected($locationId == $location->id)>
                    {{ $location->name }}
                </option>
            @endforeach
        </select>

        <input type="text"
               name="q"
               value="{{ $search }}"
               class="form-control mr-2"
               placeholder="Cari nama / jabatan / seaman book">

        <button class="btn btn-secondary">Cari</button>
    </form>

</div>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>No</th>
            <th>Nama</th>
            <th>Jabatan</th>
            <th>Kapal</th>
            <th>Sign On</th>
            <th>Sign Off</th>
            <th>Kontrak</th>
            <th>Sertifikat</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody>
        @forelse($data as $item)
        @php
            $contractStatus = $item->contractStatus();
            $alertCertificates = $item->certificates
                ->filter(fn ($certificate) => in_array($certificate->status(), ['Expired', 'Segera Habis']))
                ->count();
        @endphp
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $item->name }}</td>
            <td>{{ $item->rank }}</td>
            <td>{{ $item->location->name ?? 'Darat / Standby' }}</td>
            <td>{{ $item->sign_on_date?->format('d-m-Y') ?? '-' }}</td>
            <td>{{ $item->sign_off_date?->format('d-m-Y') ?? '-' }}</td>
            <td>
                @if($contractStatus)
                    <span class="badge badge-{{ \App\Models\Crew::STATUS_BADGES[$contractStatus] }}">
                        {{ $contractStatus }}
                    </span>
                @else
                    -
                @endif
            </td>
            <td>
                {{ $item->certificates->count() }}
                @if($alertCertificates)
                    <span class="badge badge-danger">{{ $alertCertificates }} perlu diperbarui</span>
                @endif
            </td>
            <td>
                <a href="/admin/crews/{{ $item->id }}" class="btn btn-info btn-sm mb-1">
                    Detail
                </a>

                <a href="/admin/crews/{{ $item->id }}/edit" class="btn btn-warning btn-sm mb-1">
                    Edit
                </a>

                <form action="/admin/crews/{{ $item->id }}"
                      method="POST"
                      class="d-inline"
                      onsubmit="return confirm('Hapus data kru ini beserta sertifikatnya?')">
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
            <td colspan="9" class="text-center text-muted">Belum ada data kru</td>
        </tr>
        @endforelse
    </tbody>
</table>

@stop
