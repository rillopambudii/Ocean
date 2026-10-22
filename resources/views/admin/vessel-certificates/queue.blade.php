@extends('layouts.admin')

@section('title', 'Renewal & Survey Queue')

@section('content_header')
    <h1>Renewal &amp; Survey Queue</h1>
@stop

@section('content')

@php
    $badges = ['Overdue' => 'danger', 'Urgent' => 'warning', 'Plan' => 'info'];
@endphp

<div class="row">
    @foreach($badges as $priority => $badge)
        <div class="col-md-4">
            <div class="small-box bg-{{ $badge }}">
                <div class="inner">
                    <h3>{{ $queue->where('priority', $priority)->count() }}</h3>
                    <p>{{ $priority }}</p>
                </div>
            </div>
        </div>
    @endforeach
</div>

<p class="text-muted small">
    Sertifikat yang habis dan survey yang belum dilaksanakan sampai 90 hari ke depan.
    Overdue: sudah lewat, Urgent: ≤ 30 hari, Plan: 31–90 hari.
</p>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Prioritas</th>
            <th>Kapal</th>
            <th>Jenis</th>
            <th>Item</th>
            <th>Due Date</th>
            <th class="text-right">Sisa Hari</th>
        </tr>
    </thead>

    <tbody>
        @forelse($queue as $row)
        <tr>
            <td><span class="badge badge-{{ $badges[$row['priority']] }}">{{ $row['priority'] }}</span></td>
            <td>{{ $row['vessel'] ?? '-' }}</td>
            <td>{{ $row['type'] }}</td>
            <td>{{ $row['item'] }}</td>
            <td>{{ $row['due']->format('d-m-Y') }}</td>
            <td class="text-right">{{ $row['days'] }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="text-center text-muted">Tidak ada renewal atau survey dalam 90 hari ke depan</td>
        </tr>
        @endforelse
    </tbody>
</table>

@stop
