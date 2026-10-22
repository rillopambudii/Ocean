@extends('layouts.admin')

@section('title', ($user ? 'Edit' : 'Tambah') . ' User')

@section('content_header')
    <h1>{{ $user ? 'Edit User' : 'Tambah User' }}</h1>
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

<form action="/admin/users{{ $user ? '/' . $user->id : '' }}" method="POST">
    @csrf

    @if($user)
        @method('PUT')
    @endif

    <div class="form-group mb-3">
        <label>Nama</label>
        <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" class="form-control" required>
    </div>

    <div class="form-group mb-3">
        <label>Email</label>
        <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" class="form-control" required>
    </div>

    <div class="form-group mb-3">
        <label>
            Password
            @if($user)
                <small class="text-muted">(kosongkan jika tidak diganti)</small>
            @endif
        </label>
        <input type="password" name="password" class="form-control" minlength="8" @required(! $user)>
    </div>

    <div class="form-group mb-3">
        <label>Role</label>
        @if($user?->is(auth()->user()))
            {{-- Role akun sendiri dikunci supaya Super Admin tidak mengunci dirinya keluar. --}}
            <input type="text" value="{{ $user->role }}" class="form-control" disabled>
            <small class="text-muted">Role akun sendiri tidak bisa diubah.</small>
        @else
            <select name="role" class="form-control">
                @foreach(\App\Models\User::ROLES as $role)
                    <option value="{{ $role }}" @selected(old('role', $user->role ?? '') === $role)>{{ $role }}</option>
                @endforeach
            </select>
            <small class="text-muted">
                Admin departemen mengelola modulnya. Admin Kapal hanya melihat dan mengisi data kapalnya.
                Management hanya melihat dashboard.
            </small>
        @endif
    </div>

    <div class="form-group mb-3">
        <label>Kapal <small class="text-muted">(wajib untuk Admin Kapal)</small></label>
        <select name="master_location_id" class="form-control">
            <option value="">-</option>
            @foreach($locations as $location)
                <option value="{{ $location->id }}" @selected(old('master_location_id', $user->master_location_id ?? '') == $location->id)>{{ $location->name }}</option>
            @endforeach
        </select>
    </div>

    <button class="btn btn-success">
        Simpan
    </button>

    <a href="/admin/users" class="btn btn-secondary">Batal</a>
</form>

@stop
