@extends('layouts.admin')

@section('title', 'Crew Rotation Plan')

@section('content_header')
    <h1>Crew Rotation Plan</h1>
@stop

@section('content')

<p class="text-muted small">
    Kru yang sedang onboard atau akan join, per kapal. Sign off dalam
    {{ \App\Models\Crew::WARNING_DAYS }} hari ditandai perlu relief.
</p>

@forelse($groups as $vessel => $crews)
<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ $vessel }}</h3>
        <span class="float-right text-muted small">{{ $crews->count() }} kru</span>
    </div>

    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Rank</th>
                    <th>Sign On</th>
                    <th>Sign Off</th>
                    <th class="text-right">Sisa Hari</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                @foreach($crews as $crew)
                @php
                    $joining = $crew->sign_on_date?->isAfter(today());
                    $relief = ! $joining && $crew->contractStatus() === 'Segera Habis';
                @endphp
                <tr>
                    <td><a href="/admin/crews/{{ $crew->id }}">{{ $crew->name }}</a></td>
                    <td>{{ $crew->rank }}</td>
                    <td>{{ $crew->sign_on_date?->format('d-m-Y') ?? '-' }}</td>
                    <td>{{ $crew->sign_off_date?->format('d-m-Y') ?? '-' }}</td>
                    <td class="text-right">
                        {{ $crew->sign_off_date ? (int) today()->diffInDays($crew->sign_off_date, false) : '-' }}
                    </td>
                    <td>
                        @if($joining)
                            <span class="badge badge-info">Akan Join</span>
                        @elseif($relief)
                            <span class="badge badge-warning">Perlu Relief</span>
                        @else
                            <span class="badge badge-success">Onboard</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@empty
<div class="alert alert-light text-center">Belum ada kru onboard atau yang akan join</div>
@endforelse

@stop
