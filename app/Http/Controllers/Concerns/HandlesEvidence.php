<?php

namespace App\Http\Controllers\Concerns;

use App\Http\Controllers\DocumentController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Upload / unduh / hapus file bukti untuk model dengan kolom evidence_path & evidence_name.
 */
trait HandlesEvidence
{
    protected function evidenceRule(): string
    {
        return 'nullable|file|' . DocumentController::FILE_RULES;
    }

    /**
     * Simpan file baru (mengganti yang lama); kembalikan atribut untuk disimpan, atau [] jika tidak ada upload.
     */
    protected function storeEvidence(Request $request, ?Model $model, string $directory): array
    {
        if (! $request->hasFile('evidence')) {
            return [];
        }

        $this->deleteEvidence($model);

        $file = $request->file('evidence');

        return [
            'evidence_path' => $file->store($directory),
            'evidence_name' => $file->getClientOriginalName(),
        ];
    }

    protected function deleteEvidence(?Model $model): void
    {
        if ($model?->evidence_path) {
            Storage::delete($model->evidence_path);
        }
    }

    protected function evidenceResponse(Model $model)
    {
        abort_unless($model->evidence_path && Storage::exists($model->evidence_path), 404);

        return Storage::response($model->evidence_path, $model->evidence_name);
    }
}
