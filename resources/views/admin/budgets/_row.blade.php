{{-- Satu baris anggaran vs realisasi. Butuh: $label, $budget, $actual, $tag ('td'|'th'). --}}
@php
    $tag ??= 'td';
    $difference = $budget - $actual;
    $absorption = $budget > 0 ? $actual / $budget * 100 : null;
@endphp
<tr>
    <{{ $tag }}>{{ $label }}</{{ $tag }}>
    <{{ $tag }} class="text-right">{{ number_format($budget, 0, ',', '.') }}</{{ $tag }}>
    <{{ $tag }} class="text-right">{{ number_format($actual, 0, ',', '.') }}</{{ $tag }}>
    <{{ $tag }} class="text-right {{ $difference < 0 ? 'text-danger' : '' }}">{{ number_format($difference, 0, ',', '.') }}</{{ $tag }}>
    <{{ $tag }} class="text-right">
        @if($absorption === null)
            {!! $actual > 0 ? '<span class="badge badge-danger">Tanpa anggaran</span>' : '-' !!}
        @else
            <span class="badge badge-{{ $absorption > 100 ? 'danger' : ($absorption >= 90 ? 'warning' : 'success') }}">
                {{ number_format($absorption, 1, ',', '.') }}%
            </span>
        @endif
    </{{ $tag }}>
</tr>
