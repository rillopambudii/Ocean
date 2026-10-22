@extends('layouts.admin')

@section('title', ($vendor ? 'Edit' : 'Tambah') . ' Vendor')

@section('content_header')
    <h1>{{ $vendor ? 'Edit ' . $vendor->code() : 'Tambah Vendor' }}</h1>
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

<form action="/admin/vendors{{ $vendor ? '/' . $vendor->id : '' }}" method="POST">

    @csrf

    @if($vendor)
        @method('PUT')
    @endif

    <div class="form-group mb-3">

        <label>Nama Supplier</label>

        <input type="text"
               name="name"
               value="{{ old('name', $vendor->name ?? '') }}"
               class="form-control"
               required>

    </div>

    <div class="row">

        <div class="col-md-4 form-group mb-3">

            <label>Kategori</label>

            <select name="category" class="form-control" required>
                @foreach(\App\Models\PurchaseRequest::CATEGORIES as $option)
                    <option value="{{ $option }}" @selected(old('category', $vendor->category ?? '') === $option)>
                        {{ $option }}
                    </option>
                @endforeach
            </select>

        </div>

        <div class="col-md-4 form-group mb-3">

            <label>Approval Status</label>

            <select name="approval_status" class="form-control" required>
                @foreach(array_keys(\App\Models\Vendor::APPROVAL_STATUSES) as $option)
                    <option value="{{ $option }}" @selected(old('approval_status', $vendor->approval_status ?? 'To Evaluate') === $option)>
                        {{ $option }}
                    </option>
                @endforeach
            </select>

        </div>

        <div class="col-md-4 form-group mb-3">

            <label>Kontak <small class="text-muted">(opsional)</small></label>

            <input type="text"
                   name="contact"
                   value="{{ old('contact', $vendor->contact ?? '') }}"
                   class="form-control"
                   placeholder="Nama / telepon / email">

        </div>

    </div>

    <div class="form-group mb-3">

        <label>Catatan <small class="text-muted">(opsional)</small></label>

        <textarea name="remarks" class="form-control" rows="3">{{ old('remarks', $vendor->remarks ?? '') }}</textarea>

    </div>

    <button class="btn btn-primary">Simpan</button>

    <a href="/admin/vendors{{ $vendor ? '/' . $vendor->id : '' }}" class="btn btn-secondary">Batal</a>

</form>

@stop
