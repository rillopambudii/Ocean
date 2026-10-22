@extends('layouts.admin')

@section('title', 'Tambah User')

@section('content_header')
    <h1>Tambah User</h1>
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

<form action="/admin/users" method="POST">
    @csrf

    <div class="form-group mb-3">
        <label>Nama</label>
        <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
    </div>

    <div class="form-group mb-3">
        <label>Email</label>
        <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
    </div>

    <div class="form-group mb-3">
        <label>Password</label>
        <input type="password" name="password" class="form-control" minlength="8" required>
    </div>

    <div class="form-group mb-3">
        <label>Role</label>
        <select name="role" class="form-control">
            @foreach(\App\Models\User::ROLES as $role)
                <option value="{{ $role }}" @selected(old('role') === $role)>{{ $role }}</option>
            @endforeach
        </select>
        <small class="text-muted">
            Admin departemen mengelola modulnya. Admin Kapal hanya melihat dan mengisi data kapalnya.
            Management hanya melihat dashboard.
        </small>
    </div>

    <div class="form-group mb-3">
        <label>Kapal <small class="text-muted">(wajib untuk Admin Kapal)</small></label>
        <select name="master_location_id" class="form-control">
            <option value="">-</option>
            @foreach($locations as $location)
                <option value="{{ $location->id }}" @selected(old('master_location_id') == $location->id)>{{ $location->name }}</option>
            @endforeach
        </select>
    </div>

    <button class="btn btn-success">
        Simpan
    </button>
</form>

@stop
