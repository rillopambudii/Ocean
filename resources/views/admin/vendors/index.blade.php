@extends('layouts.admin')

@section('title', 'Vendor Evaluation')

@section('content_header')
    <h1>Vendor Evaluation</h1>
@stop

@section('content')

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="d-flex flex-wrap justify-content-between mb-3">

    <a href="/admin/vendors/create" class="btn btn-primary mb-2">
        <i class="fas fa-plus"></i> Tambah Vendor
    </a>

    <form action="/admin/vendors" method="GET" class="form-inline mb-2">
        <select name="category" class="form-control mr-2 mb-1">
            <option value="">Semua Kategori</option>
            @foreach(\App\Models\PurchaseRequest::CATEGORIES as $option)
                <option value="{{ $option }}" @selected($category === $option)>{{ $option }}</option>
            @endforeach
        </select>

        <select name="status" class="form-control mr-2 mb-1">
            <option value="">Semua Status</option>
            @foreach(array_keys(\App\Models\Vendor::APPROVAL_STATUSES) as $option)
                <option value="{{ $option }}" @selected($status === $option)>{{ $option }}</option>
            @endforeach
        </select>

        <input type="text"
               name="q"
               value="{{ $search }}"
               class="form-control mr-2 mb-1"
               placeholder="Cari nama supplier">

        <button class="btn btn-secondary mb-1">Cari</button>
    </form>

</div>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Supplier</th>
            <th>Kategori</th>
            <th>Approval Status</th>
            <th>Review Terakhir</th>
            <th class="text-right">Skor</th>
            <th>Hasil</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody>
        @forelse($data as $item)
        @php $latest = $item->latestEvaluation; @endphp
        <tr>
            <td>{{ $item->code() }}</td>
            <td>
                {{ $item->name }}
                @if($item->contact)
                    <br><small class="text-muted">{{ $item->contact }}</small>
                @endif
            </td>
            <td>{{ $item->category }}</td>
            <td>
                <span class="badge badge-{{ \App\Models\Vendor::APPROVAL_STATUSES[$item->approval_status] ?? 'secondary' }}">
                    {{ $item->approval_status }}
                </span>
            </td>
            <td>{{ $latest?->review_date->format('d-m-Y') ?? '-' }}</td>
            <td class="text-right">{{ $latest ? number_format($latest->score(), 2, ',', '.') : '-' }}</td>
            <td>
                @if($latest)
                    <span class="badge badge-{{ \App\Models\VendorEvaluation::RESULT_BADGES[$latest->result()] }}">
                        {{ $latest->result() }}
                    </span>
                @else
                    -
                @endif
            </td>
            <td>
                <a href="/admin/vendors/{{ $item->id }}" class="btn btn-info btn-sm mb-1">
                    Detail
                </a>

                <a href="/admin/vendors/{{ $item->id }}/edit" class="btn btn-warning btn-sm mb-1">
                    Edit
                </a>

                <form action="/admin/vendors/{{ $item->id }}"
                      method="POST"
                      class="d-inline"
                      onsubmit="return confirm('Hapus vendor ini beserta evaluasinya?')">
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
            <td colspan="8" class="text-center text-muted">Belum ada vendor</td>
        </tr>
        @endforelse
    </tbody>
</table>

@stop
