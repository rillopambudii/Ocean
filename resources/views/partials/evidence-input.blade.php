{{-- Input file bukti. Butuh: $model (null saat tambah), $url (link unduh file saat ini). --}}
<div class="form-group mb-3">

    <label>Evidence <small class="text-muted">(opsional)</small></label>

    @if($model?->evidence_path)
        <p class="mb-1">
            File saat ini:
            <a href="{{ $url }}" target="_blank">{{ $model->evidence_name }}</a>
            <br>
            <small class="text-muted">Kosongkan jika tidak ingin mengganti file.</small>
        </p>
    @endif

    <input type="file"
           name="evidence"
           class="form-control"
           accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png">

    <small class="text-muted">PDF, Word, Excel, PowerPoint, JPG/PNG — maks. 20 MB.</small>

</div>
