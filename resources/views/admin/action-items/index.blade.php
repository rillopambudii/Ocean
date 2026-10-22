@extends('layouts.admin')

@section('title', $title)

@section('content_header')
    <h1>{{ $title }}</h1>
    @if($department)
        <small class="text-muted">
            Tampilan {{ $department }} dari Weekly Action Tracker — aksi yang dipimpin atau didukung {{ $department }}.
        </small>
    @endif
@stop

@section('content')

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if($overdue)
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i>
        {{ $overdue }} action belum selesai dan sudah melewati target.
    </div>
@endif

<div class="d-flex flex-wrap justify-content-between mb-3">

    <a href="/admin/action-items/create?back={{ urlencode($back) }}" class="btn btn-primary mb-2">
        <i class="fas fa-plus"></i> Tambah Action
    </a>

    <form action="{{ $back }}" method="GET" class="form-inline mb-2">
        <select name="location" class="form-control mr-2 mb-1">
            <option value="">Semua Kapal / Office</option>
            @foreach($locations as $location)
                <option value="{{ $location->id }}" @selected($locationId == $location->id)>
                    {{ $location->name }}
                </option>
            @endforeach
        </select>

        <select name="status" class="form-control mr-2 mb-1">
            <option value="">Semua Status</option>
            @foreach(array_keys(\App\Models\ActionItem::STATUSES) as $option)
                <option value="{{ $option }}" @selected($status === $option)>{{ $option }}</option>
            @endforeach
        </select>

        <button class="btn btn-secondary mb-1">Filter</button>
    </form>

</div>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Kapal / Office</th>
            <th>Follow-up Item</th>
            <th>Lead / Support</th>
            <th>Prioritas</th>
            <th>Target</th>
            <th>Status</th>
            <th>Last Update</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody>
        @forelse($data as $item)
        <tr>
            <td>{{ $item->code() }}</td>
            <td>{{ $item->location->name ?? '-' }}</td>
            <td>
                {{ $item->title }}
                @if($item->next_action)
                    <br><small class="text-muted">Next: {{ $item->next_action }}</small>
                @endif
                @if($item->evidence_path)
                    <br>
                    <a href="/admin/action-items/{{ $item->id }}/evidence" target="_blank">
                        <i class="fas fa-paperclip"></i> {{ $item->evidence_name }}
                    </a>
                @endif
            </td>
            <td>
                {{ $item->department }}{{ $item->supporting_department ? ' / ' . $item->supporting_department : '' }}
                @if($item->lead_pic)
                    <br><small class="text-muted">{{ $item->lead_pic }}</small>
                @endif
            </td>
            <td>
                <span class="badge badge-{{ \App\Models\ActionItem::PRIORITIES[$item->priority] ?? 'secondary' }}">
                    {{ $item->priority }}
                </span>
            </td>
            <td class="{{ $item->isOverdue() ? 'text-danger font-weight-bold' : '' }}">
                {{ $item->target_date?->format('d-m-Y') ?? '-' }}
            </td>
            <td>
                <span class="badge badge-{{ \App\Models\ActionItem::STATUSES[$item->status] ?? 'secondary' }}">
                    {{ $item->status }}
                </span>
                @if($item->actual_date)
                    <br><small class="text-muted">{{ $item->actual_date->format('d-m-Y') }}</small>
                @endif
            </td>
            <td>{{ $item->updated_at->format('d-m-Y') }}</td>
            <td>
                <a href="/admin/action-items/{{ $item->id }}/edit?back={{ urlencode($back) }}" class="btn btn-warning btn-sm mb-1">
                    Edit
                </a>

                <form action="/admin/action-items/{{ $item->id }}"
                      method="POST"
                      class="d-inline"
                      onsubmit="return confirm('Hapus action ini?')">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="back" value="{{ $back }}">

                    <button class="btn btn-danger btn-sm mb-1">
                        Hapus
                    </button>
                </form>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="9" class="text-center text-muted">Belum ada action</td>
        </tr>
        @endforelse
    </tbody>
</table>

@stop
