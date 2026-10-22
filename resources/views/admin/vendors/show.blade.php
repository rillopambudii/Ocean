@extends('layouts.admin')

@section('title', $vendor->name)

@section('content_header')
    <h1>
        {{ $vendor->code() }} — {{ $vendor->name }}
        <span class="badge badge-{{ \App\Models\Vendor::APPROVAL_STATUSES[$vendor->approval_status] ?? 'secondary' }}">
            {{ $vendor->approval_status }}
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
            <tr><th style="width: 200px">Kategori</th><td>{{ $vendor->category }}</td></tr>
            <tr><th>Kontak</th><td>{{ $vendor->contact ?? '-' }}</td></tr>
            <tr><th>Catatan</th><td>{{ $vendor->remarks ?? '-' }}</td></tr>
        </table>
    </div>
    <div class="card-footer">
        <a href="/admin/vendors/{{ $vendor->id }}/edit" class="btn btn-warning btn-sm">Edit / Ubah Status</a>
        <a href="/admin/vendors" class="btn btn-secondary btn-sm">Kembali</a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Evaluasi Kinerja</h3>
    </div>

    <div class="card-body">

        <form action="/admin/vendors/{{ $vendor->id }}/evaluations" method="POST" enctype="multipart/form-data" class="mb-4">
            @csrf

            <div class="row">
                <div class="col-md-3 mb-2">
                    <label class="small mb-0">Tanggal Review</label>
                    <input type="date" name="review_date" value="{{ old('review_date', today()->format('Y-m-d')) }}"
                           class="form-control" required>
                </div>

                <div class="col-md-3 mb-2">
                    <label class="small mb-0">Reviewer</label>
                    <input type="text" name="reviewer" value="{{ old('reviewer') }}" class="form-control">
                </div>

                @foreach(\App\Models\VendorEvaluation::CRITERIA as $column => $label)
                    <div class="col-md-3 col-6 mb-2">
                        <label class="small mb-0">{{ $label }}</label>
                        <select name="{{ $column }}" class="form-control" required>
                            @foreach([5, 4, 3, 2, 1] as $score)
                                <option value="{{ $score }}" @selected(old($column, 3) == $score)>{{ $score }}</option>
                            @endforeach
                        </select>
                    </div>
                @endforeach
            </div>

            <div class="row">
                <div class="col-md-6 mb-2">
                    <input type="text" name="remarks" value="{{ old('remarks') }}"
                           class="form-control" placeholder="Catatan hasil evaluasi (opsional)">
                </div>

                <div class="col-md-4 mb-2">
                    <input type="file" name="evidence" class="form-control"
                           accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png" title="Evidence (opsional)">
                </div>

                <div class="col-md-2 mb-2">
                    <button class="btn btn-primary btn-block">
                        <i class="fas fa-plus"></i> Tambah
                    </button>
                </div>
            </div>

            <small class="text-muted">Skor 1 (sangat kurang) – 5 (sangat baik). Hasil: rata-rata ≥ 4 Baik, ≥ 3 Cukup, &lt; 3 Kurang.</small>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Reviewer</th>
                        @foreach(\App\Models\VendorEvaluation::CRITERIA as $label)
                            <th class="text-center">{{ $label }}</th>
                        @endforeach
                        <th class="text-right">Rata-rata</th>
                        <th>Hasil</th>
                        <th>Catatan</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($vendor->evaluations as $evaluation)
                    <tr>
                        <td>{{ $evaluation->review_date->format('d-m-Y') }}</td>
                        <td>{{ $evaluation->reviewer ?? '-' }}</td>
                        @foreach(array_keys(\App\Models\VendorEvaluation::CRITERIA) as $column)
                            <td class="text-center">{{ $evaluation->{$column} }}</td>
                        @endforeach
                        <td class="text-right">{{ number_format($evaluation->score(), 2, ',', '.') }}</td>
                        <td>
                            <span class="badge badge-{{ \App\Models\VendorEvaluation::RESULT_BADGES[$evaluation->result()] }}">
                                {{ $evaluation->result() }}
                            </span>
                        </td>
                        <td>
                            {{ $evaluation->remarks ?? '-' }}
                            @if($evaluation->evidence_path)
                                <br>
                                <a href="/admin/vendor-evaluations/{{ $evaluation->id }}/evidence" target="_blank">
                                    <i class="fas fa-paperclip"></i> {{ $evaluation->evidence_name }}
                                </a>
                            @endif
                        </td>
                        <td>
                            <form action="/admin/vendor-evaluations/{{ $evaluation->id }}"
                                  method="POST"
                                  onsubmit="return confirm('Hapus evaluasi ini?')">
                                @csrf
                                @method('DELETE')

                                <button class="btn btn-danger btn-sm">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted">Belum ada evaluasi</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>

@stop
