<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use App\Models\Document;

class DocumentController extends Controller
{
    private const FILE_RULES = 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png|max:20480';

    public function index(Request $request, string $category)
    {
        $config = $this->authorizeCategory($category);

        $search = $request->query('q');

        $data = Document::with('uploader')
            ->where('category', $category)
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('document_number', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get();

        return view(
            'admin.documents.index',
            compact('data', 'category', 'config', 'search')
        );
    }

    public function create(string $category)
    {
        $config = $this->authorizeCategory($category);

        $document = null;

        return view(
            'admin.documents.form',
            compact('category', 'config', 'document')
        );
    }

    public function store(Request $request, string $category)
    {
        $this->authorizeCategory($category);

        $validated = $this->validateDocument($request, true);

        $file = $request->file('file');

        Document::create([

            'category' => $category,

            'title' => $validated['title'],

            'document_number' => $validated['document_number'] ?? null,

            'revision' => $validated['revision'] ?? null,

            'file_path' => $file->store("documents/{$category}"),

            'original_name' => $file->getClientOriginalName(),

            'uploaded_by' => auth()->id(),
        ]);

        return redirect("/admin/documents/{$category}")
            ->with('success', 'Dokumen berhasil diunggah');
    }

    public function edit(string $category, int $id)
    {
        $config = $this->authorizeCategory($category);

        $document = $this->findDocument($category, $id);

        return view(
            'admin.documents.form',
            compact('category', 'config', 'document')
        );
    }

    public function update(Request $request, string $category, int $id)
    {
        $this->authorizeCategory($category);

        $document = $this->findDocument($category, $id);

        $validated = $this->validateDocument($request, false);

        $attributes = [

            'title' => $validated['title'],

            'document_number' => $validated['document_number'] ?? null,

            'revision' => $validated['revision'] ?? null,
        ];

        if ($request->hasFile('file')) {

            $file = $request->file('file');

            Storage::delete($document->file_path);

            $attributes['file_path'] = $file->store("documents/{$category}");

            $attributes['original_name'] = $file->getClientOriginalName();
        }

        $document->update($attributes);

        return redirect("/admin/documents/{$category}")
            ->with('success', 'Dokumen berhasil diperbarui');
    }

    public function destroy(string $category, int $id)
    {
        $this->authorizeCategory($category);

        $document = $this->findDocument($category, $id);

        Storage::delete($document->file_path);

        $document->delete();

        return redirect("/admin/documents/{$category}")
            ->with('success', 'Dokumen berhasil dihapus');
    }

    public function download(string $category, int $id)
    {
        $this->authorizeCategory($category);

        $document = $this->findDocument($category, $id);

        abort_unless(Storage::exists($document->file_path), 404);

        $extension = strtolower(pathinfo($document->original_name, PATHINFO_EXTENSION));

        // PDF & gambar dibuka langsung di browser, format lain diunduh.
        $disposition = in_array($extension, ['pdf', 'jpg', 'jpeg', 'png'])
            ? 'inline'
            : 'attachment';

        return Storage::response(
            $document->file_path,
            $document->original_name,
            [],
            $disposition
        );
    }

    private function authorizeCategory(string $category): array
    {
        $config = Document::CATEGORIES[$category] ?? abort(404);

        Gate::authorize($config['gate']);

        return $config;
    }

    private function findDocument(string $category, int $id): Document
    {
        return Document::where('category', $category)->findOrFail($id);
    }

    private function validateDocument(Request $request, bool $fileRequired): array
    {
        return $request->validate([

            'title' => 'required|string|max:255',

            'document_number' => 'nullable|string|max:100',

            'revision' => 'nullable|string|max:100',

            'file' => ($fileRequired ? 'required' : 'nullable') . '|file|' . self::FILE_RULES,
        ]);
    }
}
