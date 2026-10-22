<?php

namespace Tests\Feature;

use App\Models\Crew;
use App\Models\MasterLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrewTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'Super Admin']);
    }

    private function vessel(): MasterLocation
    {
        return MasterLocation::create([
            'area' => 'Kalimantan',
            'location' => 'Balikpapan',
            'name' => 'OPS ANDRA',
            'type' => 'Vessel',
        ]);
    }

    public function test_expiry_status_boundaries(): void
    {
        $this->assertNull(Crew::expiryStatus(null));
        $this->assertSame('Expired', Crew::expiryStatus(today()->subDay()));
        $this->assertSame('Segera Habis', Crew::expiryStatus(today()));
        $this->assertSame('Segera Habis', Crew::expiryStatus(today()->addDays(Crew::WARNING_DAYS)));
        $this->assertSame('Aktif', Crew::expiryStatus(today()->addDays(Crew::WARNING_DAYS + 1)));
    }

    public function test_super_admin_can_create_crew_with_certificate(): void
    {
        $admin = $this->admin();
        $vessel = $this->vessel();

        $this->actingAs($admin)->post('/admin/crews', [
            'name' => 'Budi Santoso',
            'rank' => 'Chief Officer',
            'master_location_id' => $vessel->id,
            'sign_on_date' => today()->subMonths(5)->toDateString(),
            'sign_off_date' => today()->addDays(10)->toDateString(),
        ])->assertRedirect();

        $crew = Crew::firstOrFail();
        $this->assertSame('Segera Habis', $crew->contractStatus());

        $this->actingAs($admin)->post("/admin/crews/{$crew->id}/certificates", [
            'name' => 'COC ANT-III',
            'expired_date' => today()->subDay()->toDateString(),
        ])->assertRedirect("/admin/crews/{$crew->id}");

        $this->actingAs($admin)->get('/admin/crews')
            ->assertOk()
            ->assertSee('Budi Santoso')
            ->assertSee('OPS ANDRA')
            ->assertSee('1 kontrak dan 1 sertifikat');

        $this->actingAs($admin)->get("/admin/crews/{$crew->id}")
            ->assertOk()
            ->assertSee('COC ANT-III')
            ->assertSee('Expired');
    }

    public function test_sign_off_cannot_precede_sign_on(): void
    {
        $this->actingAs($this->admin())->post('/admin/crews', [
            'name' => 'Andi',
            'rank' => 'AB',
            'sign_on_date' => '2026-05-01',
            'sign_off_date' => '2026-04-01',
        ])->assertSessionHasErrors('sign_off_date');

        $this->assertSame(0, Crew::count());
    }

    public function test_deleting_crew_removes_certificates(): void
    {
        $admin = $this->admin();
        $crew = Crew::create(['name' => 'Andi', 'rank' => 'AB']);
        $crew->certificates()->create(['name' => 'BST']);

        $this->actingAs($admin)->delete("/admin/crews/{$crew->id}")->assertRedirect('/admin/crews');

        $this->assertDatabaseCount('crew_certificates', 0);
    }

    public function test_non_super_admin_is_forbidden(): void
    {
        $user = User::factory()->create(['role' => 'Admin QHSE']);

        $this->actingAs($user)->get('/admin/crews')->assertForbidden();
    }
}
