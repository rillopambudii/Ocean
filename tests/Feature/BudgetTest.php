<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\MasterLocation;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private MasterLocation $vessel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'Super Admin']);

        $this->vessel = MasterLocation::create([
            'area' => 'Kalimantan',
            'location' => 'Balikpapan',
            'name' => 'OPS ALHENA',
            'type' => 'Vessel',
        ]);
    }

    private function receivedPurchase(string $category, string $receivedDate, float $price, string $status = 'Diterima'): void
    {
        $purchase = PurchaseRequest::create([
            'master_location_id' => $this->vessel->id,
            'title' => 'Pembelian',
            'category' => $category,
            'request_date' => '2026-01-01',
            'status' => $status,
            'received_date' => $receivedDate,
        ]);

        $purchase->items()->create(['description' => 'Barang', 'quantity' => 2, 'unit' => 'pcs', 'unit_price' => $price]);
    }

    public function test_save_plan_replaces_existing_budget(): void
    {
        $url = '/admin/budgets/plan';

        $this->actingAs($this->admin)->post($url, [
            'master_location_id' => $this->vessel->id,
            'year' => 2026,
            'amounts' => ['Spare Part' => [1 => 1000000, 2 => 2000000]],
        ])->assertRedirect("/admin/budgets?year=2026&location={$this->vessel->id}");

        $this->assertSame(2, Budget::count());

        // Simpan ulang: Feb dikosongkan, Bahan Bakar ditambah.
        $this->actingAs($this->admin)->post($url, [
            'master_location_id' => $this->vessel->id,
            'year' => 2026,
            'amounts' => [
                'Spare Part' => [1 => 1500000, 2 => ''],
                'Bahan Bakar & Pelumas' => [3 => 750000],
            ],
        ]);

        $this->assertSame(2, Budget::count());
        $this->assertEquals(1500000, Budget::where('category', 'Spare Part')->value('amount'));
        $this->assertSame(1, Budget::where('category', 'Bahan Bakar & Pelumas')->where('month', 3)->count());

        $this->actingAs($this->admin)->get("{$url}?year=2026&location={$this->vessel->id}")
            ->assertOk()
            ->assertSee('value="1500000"', false);
    }

    public function test_unknown_category_is_rejected(): void
    {
        $this->actingAs($this->admin)->post('/admin/budgets/plan', [
            'master_location_id' => $this->vessel->id,
            'year' => 2026,
            'amounts' => ['Gaji' => [1 => 100]],
        ])->assertStatus(422);

        $this->assertSame(0, Budget::count());
    }

    public function test_monitoring_uses_received_purchases_only(): void
    {
        Budget::create(['master_location_id' => $this->vessel->id, 'category' => 'Spare Part', 'year' => 2026, 'month' => 3, 'amount' => 1000000]);

        $this->receivedPurchase('Spare Part', '2026-03-15', 300000);           // 600.000 masuk Maret
        $this->receivedPurchase('Spare Part', '2025-03-15', 999999);           // tahun lain
        $this->receivedPurchase('Provision', '2026-04-01', 50000);             // tanpa anggaran
        $this->receivedPurchase('Spare Part', '2026-03-20', 777777, 'Dipesan'); // belum diterima

        $response = $this->actingAs($this->admin)->get('/admin/budgets?year=2026')->assertOk();

        $monthly = $response->viewData('monthly');
        $byCategory = $response->viewData('byCategory');

        $this->assertEquals(['budget' => 1000000, 'actual' => 600000], $monthly[3]);
        $this->assertEquals(100000, $monthly[4]['actual']);
        $this->assertEquals(['budget' => 1000000, 'actual' => 600000], $byCategory['Spare Part']);
        $response->assertSee('60,0%')->assertSee('Tanpa anggaran');
    }

    public function test_non_super_admin_is_forbidden(): void
    {
        $user = User::factory()->create(['role' => 'Admin Operation']);

        $this->actingAs($user)->get('/admin/budgets')->assertForbidden();
    }
}
