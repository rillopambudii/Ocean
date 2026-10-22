@extends('layouts.admin')

@section('title', 'Vessel Requisitions')

@section('content_header')
    <h1>Vessel Requisitions</h1>
@stop

@section('content')

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="d-flex flex-wrap justify-content-between mb-3">

    <a href="/admin/purchase-requests/create" class="btn btn-primary mb-2">
        <i class="fas fa-plus"></i> Buat Permintaan
    </a>

    <form action="/admin/purchase-requests" method="GET" class="form-inline mb-2">
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
            @foreach(array_keys(\App\Models\PurchaseRequest::STATUSES) as $option)
                <option value="{{ $option }}" @selected($status === $option)>{{ $option }}</option>
            @endforeach
        </select>

        <input type="text"
               name="q"
               value="{{ $search }}"
               class="form-control mr-2"
               placeholder="Cari no. PR / judul / PO / vendor">

        <button class="btn btn-secondary">Cari</button>
    </form>

</div>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>No. PR</th>
            <th>Tanggal</th>
            <th>Kapal</th>
            <th>Judul</th>
            <th>Kategori</th>
            <th class="text-right">Total (Rp)</th>
            <th>Status</th>
            <th>Vendor / PO</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody>
        @forelse($data as $item)
        <tr>
            <td>{{ $item->request_number }}</td>
            <td>{{ $item->request_date->format('d-m-Y') }}</td>
            <td>{{ $item->location->name ?? '-' }}</td>
            <td>{{ $item->title }} <small class="text-muted">({{ $item->items->count() }} item)</small></td>
            <td>{{ $item->category }}</td>
            <td class="text-right">{{ number_format($item->total(), 0, ',', '.') }}</td>
            <td>
                <span class="badge badge-{{ \App\Models\PurchaseRequest::STATUSES[$item->status] ?? 'secondary' }}">
                    {{ $item->status }}
                </span>
            </td>
            <td>
                {{ $item->vendor ?? '-' }}
                @if($item->po_number)
                    <br><small class="text-muted">{{ $item->po_number }}</small>
                @endif
            </td>
            <td>
                <a href="/admin/purchase-requests/{{ $item->id }}" class="btn btn-info btn-sm mb-1">
                    Detail
                </a>

                <a href="/admin/purchase-requests/{{ $item->id }}/edit" class="btn btn-warning btn-sm mb-1">
                    Edit
                </a>

                <form action="/admin/purchase-requests/{{ $item->id }}"
                      method="POST"
                      class="d-inline"
                      onsubmit="return confirm('Hapus permintaan ini beserta itemnya?')">
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
            <td colspan="9" class="text-center text-muted">Belum ada permintaan pembelian</td>
        </tr>
        @endforelse
    </tbody>

    @if($data->isNotEmpty())
    <tfoot>
        <tr>
            <th colspan="5" class="text-right">Total</th>
            <th class="text-right">{{ number_format($data->sum(fn ($item) => $item->total()), 0, ',', '.') }}</th>
            <th colspan="3"></th>
        </tr>
    </tfoot>
    @endif
</table>

@stop
