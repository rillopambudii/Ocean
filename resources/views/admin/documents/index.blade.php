@extends('layouts.admin')

@section('title', $config['label'])

@section('content_header')
    <h1>{{ $config['label'] }}</h1>
@stop

@section('content')

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="d-flex flex-wrap justify-content-between mb-3">

    <a href="/admin/documents/{{ $category }}/create" class="btn btn-primary mb-2">
        <i class="fas fa-upload"></i> Upload Dokumen
    </a>

    <form action="/admin/documents/{{ $category }}" method="GET" class="form-inline mb-2">
        <input type="text"
               name="q"
               value="{{ $search }}"
               class="form-control mr-2"
               placeholder="Cari judul / no. dokumen">

        <button class="btn btn-secondary">Cari</button>
    </form>

</div>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>No</th>
            <th>Judul</th>
            <th>No. Dokumen</th>
            <th>Revisi</th>
            <th>File</th>
            <th>Diunggah oleh</th>
            <th>Tanggal</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody>
        @forelse($data as $item)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $item->title }}</td>
            <td>{{ $item->document_number ?? '-' }}</td>
            <td>{{ $item->revision ?? '-' }}</td>
            <td>
                <a href="/admin/documents/{{ $category }}/{{ $item->id }}/file" target="_blank">
                    <i class="fas fa-file-download"></i> {{ $item->original_name }}
                </a>
            </td>
            <td>{{ $item->uploader->name ?? '-' }}</td>
            <td>{{ $item->updated_at->format('d-m-Y') }}</td>
            <td>
                <a href="/admin/documents/{{ $category }}/{{ $item->id }}/edit"
                   class="btn btn-warning btn-sm mb-1">
                    Edit
                </a>

                <form action="/admin/documents/{{ $category }}/{{ $item->id }}"
                      method="POST"
                      class="d-inline"
                      onsubmit="return confirm('Hapus dokumen ini?')">
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
            <td colspan="8" class="text-center text-muted">Belum ada dokumen</td>
        </tr>
        @endforelse
    </tbody>
</table>

@stop
