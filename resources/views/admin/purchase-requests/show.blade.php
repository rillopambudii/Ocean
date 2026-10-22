@extends('layouts.admin')

@section('title', $purchase->request_number)

@section('content_header')
    <h1>
        {{ $purchase->request_number }}
        <span class="badge badge-{{ \App\Models\PurchaseRequest::STATUSES[$purchase->status] ?? 'secondary' }}">
            {{ $purchase->status }}
        </span>
    </h1>
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

<div class="card">
    <div class="card-body">
        <table class="table table-sm table-borderless mb-0">
            <tr><th style="width: 200px">Judul</th><td>{{ $purchase->title }}</td></tr>
            <tr><th>Kapal / Lokasi</th><td>{{ $purchase->location->name ?? '-' }}</td></tr>
            <tr><th>Kategori</th><td>{{ $purchase->category }}</td></tr>
            <tr><th>Tanggal Permintaan</th><td>{{ $purchase->request_date->format('d-m-Y') }}</td></tr>
            <tr><th>Diajukan oleh</th><td>{{ $purchase->requester->name ?? '-' }}</td></tr>
            <tr><th>Vendor</th><td>{{ $purchase->vendor ?? '-' }}</td></tr>
            <tr><th>No. PO</th><td>{{ $purchase->po_number ?? '-' }}</td></tr>
            <tr><th>Tanggal Diterima</th><td>{{ $purchase->received_date?->format('d-m-Y') ?? '-' }}</td></tr>
            <tr>
                <th>Lampiran</th>
                <td>
                    @if($purchase->attachment_path)
                        <a href="/admin/purchase-requests/{{ $purchase->id }}/attachment" target="_blank">
                            <i class="fas fa-file-download"></i> {{ $purchase->attachment_name }}
                        </a>
                    @else
                        -
                    @endif
                </td>
            </tr>
            <tr><th>Catatan</th><td>{{ $purchase->remarks ?? '-' }}</td></tr>
        </table>
    </div>
    <div class="card-footer">
        <a href="/admin/purchase-requests/{{ $purchase->id }}/edit" class="btn btn-warning btn-sm">Edit / Ubah Status</a>
        <a href="/admin/purchase-requests" class="btn btn-secondary btn-sm">Kembali</a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Item</h3>
    </div>

    <div class="card-body">

        <form action="/admin/purchase-requests/{{ $purchase->id }}/items" method="POST" class="row mb-3">
            @csrf

            <div class="col-md-4 mb-2">
                <input type="text" name="description" value="{{ old('description') }}"
                       class="form-control" placeholder="Nama barang / jasa" required>
            </div>

            <div class="col-md-2 mb-2">
                <input type="number" name="quantity" value="{{ old('quantity') }}"
                       class="form-control" placeholder="Jumlah" step="any" min="0" required>
            </div>

            <div class="col-md-2 mb-2">
                <input type="text" name="unit" value="{{ old('unit') }}"
                       class="form-control" placeholder="Satuan (pcs, ltr)" required>
            </div>

            <div class="col-md-2 mb-2">
                <input type="number" name="unit_price" value="{{ old('unit_price') }}"
                       class="form-control" placeholder="Harga satuan" step="any" min="0">
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
                    <th>Barang / Jasa</th>
                    <th class="text-right">Jumlah</th>
                    <th>Satuan</th>
                    <th class="text-right">Harga Satuan (Rp)</th>
                    <th class="text-right">Subtotal (Rp)</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                @forelse($purchase->items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->description }}</td>
                    <td class="text-right">{{ rtrim(rtrim(number_format($item->quantity, 2, ',', '.'), '0'), ',') }}</td>
                    <td>{{ $item->unit }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->quantity * $item->unit_price, 0, ',', '.') }}</td>
                    <td>
                        <form action="/admin/purchase-request-items/{{ $item->id }}"
                              method="POST"
                              onsubmit="return confirm('Hapus item ini?')">
                            @csrf
                            @method('DELETE')

                            <button class="btn btn-danger btn-sm">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted">Belum ada item</td>
                </tr>
                @endforelse
            </tbody>

            <tfoot>
                <tr>
                    <th colspan="5" class="text-right">Total</th>
                    <th class="text-right">{{ number_format($purchase->total(), 0, ',', '.') }}</th>
                    <th></th>
                </tr>
            </tfoot>
        </table>

    </div>
</div>

@stop
