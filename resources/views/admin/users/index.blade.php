@extends('layouts.admin')

@section('title', 'User Management')

@section('content_header')
    <h1>User Management</h1>
@stop

@section('content')

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<a href="/admin/users/create" class="btn btn-primary mb-3">
    Tambah User
</a>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>No</th>
            <th>Nama</th>
            <th>Email</th>
            <th>Dibuat</th>
            <th>Role</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody>
        @foreach($data as $item)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $item->name }}</td>
            <td>{{ $item->email }}</td>
            <td>{{ $item->created_at }}</td>
            <td>
                {{ $item->role }}
                @if($item->location)
                    <br><small class="text-muted">{{ $item->location->name }}</small>
                @endif
            </td>
            <td>
                <a href="/admin/users/{{ $item->id }}/edit" class="btn btn-warning btn-sm mb-1">
                    Edit
                </a>

                <form action="/admin/users/{{ $item->id }}"
                      method="POST"
                      class="d-inline"
                      onsubmit="return confirm('Hapus user ini?')">
                    @csrf
                    @method('DELETE')

                    <button class="btn btn-danger btn-sm">
                        Hapus
                    </button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

@stop