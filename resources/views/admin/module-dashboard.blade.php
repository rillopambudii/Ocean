@extends('layouts.admin')

@section('title', $title)

@section('content_header')
    <h1>{{ $title }}</h1>
@stop

@section('content')

<div class="row">
    @foreach($cards as [$label, $value, $color, $icon, $url])
        <div class="col-lg-4 col-md-6">
            <div class="small-box bg-{{ $color }}">
                <div class="inner">
                    <h3>{{ $value }}</h3>
                    <p>{{ $label }}</p>
                </div>
                <div class="icon">
                    <i class="{{ $icon }}"></i>
                </div>
                <a href="{{ $url }}" class="small-box-footer">
                    Lihat detail <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    @endforeach
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ $list['title'] }}</h3>
        <a href="{{ $list['url'] }}" class="float-right small">Buka register</a>
    </div>

    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    @foreach($list['headers'] as $header)
                        <th>{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
                @forelse($list['rows'] as $row)
                <tr>
                    @foreach($row as $cell)
                        <td>
                            @if(is_array($cell))
                                <span class="badge badge-{{ $cell[1] }}">{{ $cell[0] }}</span>
                            @else
                                {{ $cell ?? '-' }}
                            @endif
                        </td>
                    @endforeach
                </tr>
                @empty
                <tr>
                    <td colspan="{{ count($list['headers']) }}" class="text-center text-muted">Tidak ada data yang perlu perhatian</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@stop
