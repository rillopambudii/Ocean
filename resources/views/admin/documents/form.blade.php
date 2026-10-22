@extends('layouts.admin')

@section('title', ($document ? 'Edit' : 'Upload') . ' Dokumen ' . $config['label'])

@section('content_header')
    <h1>{{ $document ? 'Edit' : 'Upload' }} Dokumen {{ $config['label'] }}</h1>
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

<form action="/admin/documents/{{ $category }}{{ $document ? '/' . $document->id : '' }}"
      method="POST"
      enctype="multipart/form-data">

    @csrf

    @if($document)
        @method('PUT')
    @endif

    <div class="form-group mb-3">

        <label>Judul Dokumen</label>

        <input type="text"
               name="title"
               value="{{ old('title', $document->title ?? '') }}"
               class="form-control"
               required>

    </div>

    <div class="form-group mb-3">

        <label>No. Dokumen <small class="text-muted">(opsional)</small></label>

        <input type="text"
               name="document_number"
               value="{{ old('document_number', $document->document_number ?? '') }}"
               class="form-control">

    </div>

    <div class="form-group mb-3">

        <label>Revisi <small class="text-muted">(opsional)</small></label>

        <input type="text"
               name="revision"
               value="{{ old('revision', $document->revision ?? '') }}"
               class="form-control">

    </div>

    <div class="form-group mb-3">

        <label>File</label>

        @if($document)
            <p class="mb-1">
                File saat ini:
                <a href="/admin/documents/{{ $category }}/{{ $document->id }}/file" target="_blank">
                    {{ $document->original_name }}
                </a>
                <br>
                <small class="text-muted">Kosongkan jika tidak ingin mengganti file.</small>
            </p>
        @endif

        <input type="file"
               name="file"
               class="form-control"
               accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png"
               {{ $document ? '' : 'required' }}>

        <small class="text-muted">PDF, Word, Excel, PowerPoint, JPG/PNG — maks. 20 MB.</small>

    </div>

    <button class="btn btn-primary">Simpan</button>

    <a href="/admin/documents/{{ $category }}" class="btn btn-secondary">Batal</a>

</form>

@stop
