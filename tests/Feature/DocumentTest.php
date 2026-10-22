<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    private function user(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    private function upload(User $user, string $category, array $overrides = [])
    {
        return $this->actingAs($user)->post("/admin/documents/{$category}", array_merge([
            'title' => 'Prosedur Bongkar Muat',
            'document_number' => 'OPS-PRO-001',
            'revision' => '00',
            'file' => UploadedFile::fake()->create('prosedur.pdf', 100, 'application/pdf'),
        ], $overrides));
    }

    public function test_admin_qhse_can_upload_and_list_document(): void
    {
        $user = $this->user('Admin QHSE');

        $this->upload($user, 'procedure')->assertRedirect('/admin/documents/procedure');

        $document = Document::firstOrFail();
        $this->assertSame('procedure', $document->category);
        $this->assertSame('prosedur.pdf', $document->original_name);
        $this->assertSame($user->id, $document->uploaded_by);
        Storage::disk('local')->assertExists($document->file_path);

        $this->actingAs($user)->get('/admin/documents/procedure')
            ->assertOk()
            ->assertSee('Prosedur Bongkar Muat')
            ->assertSee('OPS-PRO-001');
    }

    public function test_index_search_filters_documents(): void
    {
        $user = $this->user('Admin QHSE');
        $this->upload($user, 'manual', ['title' => 'Manual Keselamatan']);
        $this->upload($user, 'manual', ['title' => 'Manual Mutu', 'document_number' => 'QM-01']);

        $this->actingAs($user)->get('/admin/documents/manual?q=Mutu')
            ->assertOk()
            ->assertSee('Manual Mutu')
            ->assertDontSee('Manual Keselamatan');
    }

    public function test_document_can_be_downloaded(): void
    {
        $user = $this->user('Admin QHSE');
        $this->upload($user, 'manual');
        $document = Document::firstOrFail();

        $this->actingAs($user)->get("/admin/documents/manual/{$document->id}/file")->assertOk();
    }

    public function test_update_replaces_file_and_deletes_old_one(): void
    {
        $user = $this->user('Admin QHSE');
        $this->upload($user, 'form-checklist');
        $document = Document::firstOrFail();
        $oldPath = $document->file_path;

        $this->actingAs($user)->put("/admin/documents/form-checklist/{$document->id}", [
            'title' => 'Checklist Revisi',
            'revision' => '01',
            'file' => UploadedFile::fake()->create('baru.xlsx', 50),
        ])->assertRedirect('/admin/documents/form-checklist');

        $document->refresh();
        $this->assertSame('Checklist Revisi', $document->title);
        $this->assertSame('baru.xlsx', $document->original_name);
        Storage::disk('local')->assertMissing($oldPath);
        Storage::disk('local')->assertExists($document->file_path);
    }

    public function test_update_without_file_keeps_existing_file(): void
    {
        $user = $this->user('Admin QHSE');
        $this->upload($user, 'work-instruction');
        $document = Document::firstOrFail();

        $this->actingAs($user)->put("/admin/documents/work-instruction/{$document->id}", [
            'title' => 'Judul Baru',
        ])->assertRedirect('/admin/documents/work-instruction');

        Storage::disk('local')->assertExists($document->fresh()->file_path);
    }

    public function test_delete_removes_record_and_file(): void
    {
        $user = $this->user('Super Admin');
        $this->upload($user, 'technical');
        $document = Document::firstOrFail();

        $this->actingAs($user)->delete("/admin/documents/technical/{$document->id}")
            ->assertRedirect('/admin/documents/technical');

        $this->assertDatabaseCount('documents', 0);
        Storage::disk('local')->assertMissing($document->file_path);
    }

    public function test_department_documents_are_super_admin_only(): void
    {
        foreach (['technical', 'crewing', 'purchasing', 'budget'] as $category) {
            $this->actingAs($this->user('Admin QHSE'))->get("/admin/documents/{$category}")->assertForbidden();
            $this->actingAs($this->user('Admin Operation'))->get("/admin/documents/{$category}")->assertForbidden();
            $this->actingAs($this->user('Super Admin'))->get("/admin/documents/{$category}")->assertOk();
        }
    }

    public function test_admin_operation_cannot_access_qhse_documents(): void
    {
        $this->actingAs($this->user('Admin Operation'))->get('/admin/documents/procedure')->assertForbidden();
        $this->upload($this->user('Admin Operation'), 'procedure')->assertForbidden();
        $this->assertDatabaseCount('documents', 0);
    }

    public function test_document_from_other_category_is_not_found(): void
    {
        $admin = $this->user('Super Admin');
        $this->upload($admin, 'technical');
        $document = Document::firstOrFail();

        // Admin QHSE tidak boleh mengunduh dokumen Technical lewat URL kategori QHSE.
        $this->actingAs($this->user('Admin QHSE'))
            ->get("/admin/documents/manual/{$document->id}/file")
            ->assertNotFound();
    }

    public function test_unknown_category_returns_404(): void
    {
        $this->actingAs($this->user('Super Admin'))->get('/admin/documents/unknown')->assertNotFound();
    }

    public function test_rejects_disallowed_file_type(): void
    {
        $this->upload($this->user('Admin QHSE'), 'manual', [
            'file' => UploadedFile::fake()->create('script.exe', 10),
        ])->assertSessionHasErrors('file');

        $this->assertDatabaseCount('documents', 0);
    }
}
