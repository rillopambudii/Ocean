<?php

namespace Tests\Feature;

use App\Models\MasterLocation;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PurchasingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private MasterLocation $vessel;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->admin = User::factory()->create(['role' => 'Super Admin']);

        $this->vessel = MasterLocation::create([
            'area' => 'Kalimantan',
            'location' => 'Balikpapan',
            'name' => 'OPS ASTRID',
            'type' => 'Vessel',
        ]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'master_location_id' => $this->vessel->id,
            'title' => 'Spare part genset',
            'category' => 'Spare Part',
            'request_date' => '2026-10-01',
            'status' => 'Diajukan',
        ], $overrides);
    }

    public function test_full_purchase_flow(): void
    {
        $this->actingAs($this->admin)->post('/admin/purchase-requests', $this->payload())->assertRedirect();

        $purchase = PurchaseRequest::firstOrFail();
        $this->assertSame(sprintf('PR-2026-%04d', $purchase->id), $purchase->request_number);
        $this->assertSame($this->admin->id, $purchase->requested_by);

        $this->actingAs($this->admin)->post("/admin/purchase-requests/{$purchase->id}/items", [
            'description' => 'Filter oli',
            'quantity' => 4,
            'unit' => 'pcs',
            'unit_price' => 125000,
        ])->assertRedirect("/admin/purchase-requests/{$purchase->id}");

        // Harga kosong = 0, tidak gagal karena kolom NOT NULL.
        $this->actingAs($this->admin)->post("/admin/purchase-requests/{$purchase->id}/items", [
            'description' => 'Gasket',
            'quantity' => 2,
            'unit' => 'pcs',
            'unit_price' => '',
        ])->assertSessionHasNoErrors();

        $this->assertEquals(500000, $purchase->fresh()->total());

        $this->actingAs($this->admin)->put("/admin/purchase-requests/{$purchase->id}", $this->payload([
            'status' => 'Diterima',
            'vendor' => 'PT Mesin Jaya',
            'po_number' => 'PO-001',
            'attachment' => UploadedFile::fake()->create('invoice.pdf', 50, 'application/pdf'),
        ]))->assertRedirect("/admin/purchase-requests/{$purchase->id}");

        $purchase->refresh();
        $this->assertTrue($purchase->received_date->isToday());
        Storage::disk('local')->assertExists($purchase->attachment_path);

        $this->actingAs($this->admin)->get('/admin/purchase-requests')
            ->assertOk()
            ->assertSee($purchase->request_number)
            ->assertSee('500.000')
            ->assertSee('PT Mesin Jaya');

        $this->actingAs($this->admin)->get("/admin/purchase-requests/{$purchase->id}/attachment")->assertOk();
    }

    public function test_received_date_cleared_when_not_received(): void
    {
        $this->actingAs($this->admin)->post('/admin/purchase-requests', $this->payload([
            'status' => 'Dipesan',
            'received_date' => '2026-10-05',
        ]));

        $this->assertNull(PurchaseRequest::firstOrFail()->received_date);
    }

    public function test_deleting_request_removes_attachment(): void
    {
        $this->actingAs($this->admin)->post('/admin/purchase-requests', $this->payload([
            'attachment' => UploadedFile::fake()->create('penawaran.pdf', 50, 'application/pdf'),
        ]));

        $purchase = PurchaseRequest::firstOrFail();

        $this->actingAs($this->admin)->delete("/admin/purchase-requests/{$purchase->id}")
            ->assertRedirect('/admin/purchase-requests');

        Storage::disk('local')->assertMissing($purchase->attachment_path);
    }

    public function test_non_super_admin_is_forbidden(): void
    {
        $user = User::factory()->create(['role' => 'Admin QHSE']);

        $this->actingAs($user)->get('/admin/purchase-requests')->assertForbidden();
    }
}
