@extends('layouts.admin')

@section('title', ($purchase ? 'Edit' : 'Buat') . ' Permintaan Pembelian')

@section('content_header')
    <h1>{{ $purchase ? 'Edit ' . $purchase->request_number : 'Buat Permintaan Pembelian' }}</h1>
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

<form action="/admin/purchase-requests{{ $purchase ? '/' . $purchase->id : '' }}"
      method="POST"
      enctype="multipart/form-data">

    @csrf

    @if($purchase)
        @method('PUT')
    @endif

    <div class="form-group mb-3">

        <label>Judul</label>

        <input type="text"
               name="title"
               value="{{ old('title', $purchase->title ?? '') }}"
               class="form-control"
               placeholder="mis. Spare part genset bulan November"
               required>

    </div>

    <div class="row">

        <div class="col-md-4 form-group mb-3">

            <label>Kapal / Lokasi</label>

            <select name="master_location_id" class="form-control" required>
                <option value="">-- Pilih --</option>
                @foreach($locations as $location)
                    <option value="{{ $location->id }}"
                            @selected(old('master_location_id', $purchase->master_location_id ?? '') == $location->id)>
                        {{ $location->name }}
                    </option>
                @endforeach
            </select>

        </div>

        <div class="col-md-4 form-group mb-3">

            <label>Kategori</label>

            <select name="category" class="form-control" required>
                @foreach(\App\Models\PurchaseRequest::CATEGORIES as $category)
                    <option value="{{ $category }}" @selected(old('category', $purchase->category ?? '') === $category)>
                        {{ $category }}
                    </option>
                @endforeach
            </select>

        </div>

        <div class="col-md-4 form-group mb-3">

            <label>Tanggal Permintaan</label>

            <input type="date"
                   name="request_date"
                   value="{{ old('request_date', $purchase?->request_date?->format('Y-m-d') ?? today()->format('Y-m-d')) }}"
                   class="form-control"
                   required>

        </div>

    </div>

    <div class="row">

        <div class="col-md-3 form-group mb-3">

            <label>Status</label>

            <select name="status" class="form-control" required>
                @foreach(array_keys(\App\Models\PurchaseRequest::STATUSES) as $option)
                    <option value="{{ $option }}" @selected(old('status', $purchase->status ?? 'Diajukan') === $option)>
                        {{ $option }}
                    </option>
                @endforeach
            </select>

        </div>

        <div class="col-md-3 form-group mb-3">

            <label>Vendor <small class="text-muted">(opsional)</small></label>

            <input type="text"
                   name="vendor"
                   value="{{ old('vendor', $purchase->vendor ?? '') }}"
                   class="form-control">

        </div>

        <div class="col-md-3 form-group mb-3">

            <label>No. PO <small class="text-muted">(opsional)</small></label>

            <input type="text"
                   name="po_number"
                   value="{{ old('po_number', $purchase->po_number ?? '') }}"
                   class="form-control">

        </div>

        <div class="col-md-3 form-group mb-3">

            <label>Tanggal Diterima <small class="text-muted">(jika Diterima)</small></label>

            <input type="date"
                   name="received_date"
                   value="{{ old('received_date', $purchase?->received_date?->format('Y-m-d')) }}"
                   class="form-control">

        </div>

    </div>

    <div class="form-group mb-3">

        <label>Lampiran <small class="text-muted">(penawaran / invoice, opsional)</small></label>

        @if($purchase?->attachment_path)
            <p class="mb-1">
                File saat ini:
                <a href="/admin/purchase-requests/{{ $purchase->id }}/attachment" target="_blank">
                    {{ $purchase->attachment_name }}
                </a>
                <br>
                <small class="text-muted">Kosongkan jika tidak ingin mengganti file.</small>
            </p>
        @endif

        <input type="file"
               name="attachment"
               class="form-control"
               accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png">

        <small class="text-muted">PDF, Word, Excel, PowerPoint, JPG/PNG — maks. 20 MB.</small>

    </div>

    <div class="form-group mb-3">

        <label>Catatan <small class="text-muted">(opsional)</small></label>

        <textarea name="remarks" class="form-control" rows="3">{{ old('remarks', $purchase->remarks ?? '') }}</textarea>

    </div>

    <button class="btn btn-primary">Simpan</button>

    <a href="/admin/purchase-requests{{ $purchase ? '/' . $purchase->id : '' }}" class="btn btn-secondary">Batal</a>

</form>

@stop
