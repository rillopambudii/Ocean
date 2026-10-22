<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorEvaluation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VendorEvaluationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->admin = User::factory()->create(['role' => 'Super Admin']);
    }

    public function test_result_thresholds(): void
    {
        $evaluation = fn (int $q, int $d, int $p, int $s) => new VendorEvaluation(['quality' => $q, 'delivery' => $d, 'price' => $p, 'service' => $s]);

        $this->assertSame('Baik', $evaluation(4, 4, 4, 4)->result());
        $this->assertSame('Cukup', $evaluation(4, 3, 3, 2)->result());   // 3,00
        $this->assertSame('Kurang', $evaluation(3, 3, 3, 2)->result());  // 2,75
    }

    public function test_vendor_with_evaluations_shows_latest_result(): void
    {
        $this->actingAs($this->admin)->post('/admin/vendors', [
            'name' => 'PT Mesin Jaya',
            'category' => 'Spare Part',
            'approval_status' => 'Approved',
        ])->assertRedirect();

        $vendor = Vendor::firstOrFail();
        $this->assertSame(sprintf('VEN-%04d', $vendor->id), $vendor->code());

        $url = "/admin/vendors/{$vendor->id}/evaluations";
        $scores = ['quality' => 2, 'delivery' => 2, 'price' => 3, 'service' => 2];

        $this->actingAs($this->admin)->post($url, $scores + ['review_date' => '2026-01-10'])->assertRedirect("/admin/vendors/{$vendor->id}");
        $this->actingAs($this->admin)->post($url, [
            'quality' => 5, 'delivery' => 4, 'price' => 4, 'service' => 5,
            'review_date' => '2026-07-10',
            'evidence' => UploadedFile::fake()->create('evaluasi.pdf', 30, 'application/pdf'),
        ]);

        $this->assertSame('2026-07-10', $vendor->fresh()->latestEvaluation->review_date->toDateString());

        $this->actingAs($this->admin)->get('/admin/vendors')
            ->assertOk()
            ->assertSee('PT Mesin Jaya')
            ->assertSee('10-07-2026')
            ->assertSee('4,50')
            ->assertSee('Baik');

        $this->actingAs($this->admin)->get("/admin/vendors/{$vendor->id}")
            ->assertOk()
            ->assertSee('Kurang')
            ->assertSee('evaluasi.pdf');
    }

    public function test_score_must_be_between_one_and_five(): void
    {
        $vendor = Vendor::create(['name' => 'X', 'category' => 'Kantor']);

        $this->actingAs($this->admin)->post("/admin/vendors/{$vendor->id}/evaluations", [
            'quality' => 6, 'delivery' => 0, 'price' => 3, 'service' => 3,
            'review_date' => '2026-07-10',
        ])->assertSessionHasErrors(['quality', 'delivery']);
    }

    public function test_deleting_vendor_removes_evaluation_evidence(): void
    {
        $vendor = Vendor::create(['name' => 'X', 'category' => 'Kantor']);

        $this->actingAs($this->admin)->post("/admin/vendors/{$vendor->id}/evaluations", [
            'quality' => 3, 'delivery' => 3, 'price' => 3, 'service' => 3,
            'review_date' => '2026-07-10',
            'evidence' => UploadedFile::fake()->create('bukti.pdf', 30, 'application/pdf'),
        ]);

        $path = VendorEvaluation::firstOrFail()->evidence_path;

        $this->actingAs($this->admin)->delete("/admin/vendors/{$vendor->id}")->assertRedirect('/admin/vendors');

        Storage::disk('local')->assertMissing($path);
        $this->assertDatabaseCount('vendor_evaluations', 0);
    }

    public function test_non_super_admin_is_forbidden(): void
    {
        $user = User::factory()->create(['role' => 'Admin Operation']);

        $this->actingAs($user)->get('/admin/vendors')->assertForbidden();
    }
}
