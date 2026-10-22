@extends('layouts.admin')

@section('title', 'Detail Kru')

@section('content_header')
    <h1>{{ $crew->name }}</h1>
@stop

@section('content')

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php $contractStatus = $crew->contractStatus(); @endphp

<div class="card">
    <div class="card-body">
        <table class="table table-sm table-borderless mb-0">
            <tr><th style="width: 200px">Jabatan</th><td>{{ $crew->rank }}</td></tr>
            <tr><th>Kapal</th><td>{{ $crew->location->name ?? 'Darat / Standby' }}</td></tr>
            <tr><th>No. Seaman Book</th><td>{{ $crew->seaman_book_number ?? '-' }}</td></tr>
            <tr><th>Sign On</th><td>{{ $crew->sign_on_date?->format('d-m-Y') ?? '-' }}</td></tr>
            <tr>
                <th>Rencana Sign Off</th>
                <td>
                    {{ $crew->sign_off_date?->format('d-m-Y') ?? '-' }}
                    @if($contractStatus)
                        <span class="badge badge-{{ \App\Models\Crew::STATUS_BADGES[$contractStatus] }}">
                            {{ $contractStatus }}
                        </span>
                    @endif
                </td>
            </tr>
            <tr><th>Catatan</th><td>{{ $crew->remarks ?? '-' }}</td></tr>
        </table>
    </div>
    <div class="card-footer">
        <a href="/admin/crews/{{ $crew->id }}/edit" class="btn btn-warning btn-sm">Edit</a>
        <a href="/admin/crews" class="btn btn-secondary btn-sm">Kembali</a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Sertifikat (COC / COP)</h3>
    </div>

    <div class="card-body">

        <form action="/admin/crews/{{ $crew->id }}/certificates" method="POST" class="row mb-3">
            @csrf

            <div class="col-md-4 mb-2">
                <input type="text" name="name" value="{{ old('name') }}"
                       class="form-control" placeholder="Nama sertifikat (mis. COC ANT-III)" required>
            </div>

            <div class="col-md-3 mb-2">
                <input type="text" name="certificate_number" value="{{ old('certificate_number') }}"
                       class="form-control" placeholder="No. sertifikat">
            </div>

            <div class="col-md-3 mb-2">
                <input type="date" name="expired_date" value="{{ old('expired_date') }}"
                       class="form-control" title="Tanggal kedaluwarsa">
            </div>

            <div class="col-md-2 mb-2">
                <button class="btn btn-primary btn-block">
                    <i class="fas fa-plus"></i> Tambah
                </button>
            </div>
        </form>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Sertifikat</th>
                    <th>No. Sertifikat</th>
                    <th>Kedaluwarsa</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                @forelse($crew->certificates as $certificate)
                @php $status = $certificate->status(); @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $certificate->name }}</td>
                    <td>{{ $certificate->certificate_number ?? '-' }}</td>
                    <td>{{ $certificate->expired_date?->format('d-m-Y') ?? 'Seumur hidup' }}</td>
                    <td>
                        @if($status)
                            <span class="badge badge-{{ \App\Models\Crew::STATUS_BADGES[$status] }}">
                                {{ $status }}
                            </span>
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        <form action="/admin/crew-certificates/{{ $certificate->id }}"
                              method="POST"
                              onsubmit="return confirm('Hapus sertifikat ini?')">
                            @csrf
                            @method('DELETE')

                            <button class="btn btn-danger btn-sm">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">Belum ada sertifikat</td>
                </tr>
                @endforelse
            </tbody>
        </table>

    </div>
</div>

@stop
