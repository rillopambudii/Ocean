<?php

namespace Tests\Feature;

use App\Models\Crew;
use App\Models\CrewCertificate;
use App\Models\MasterLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use JeroenNoten\LaravelAdminLte\AdminLte;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    private MasterLocation $astrid;

    private MasterLocation $bima;

    protected function setUp(): void
    {
        parent::setUp();

        $this->astrid = MasterLocation::create(['area' => 'Kalimantan', 'location' => 'Balikpapan', 'name' => 'OPS ASTRID', 'type' => 'Vessel']);
        $this->bima = MasterLocation::create(['area' => 'Jawa', 'location' => 'Surabaya', 'name' => 'OPS BIMA', 'type' => 'Vessel']);
    }

    private function user(string $role, ?MasterLocation $vessel = null): User
    {
        return User::factory()->create(['role' => $role, 'master_location_id' => $vessel?->id]);
    }

    private function menuTexts(User $user): array
    {
        $this->actingAs($user);

        // AdminLte menyimpan menu yang sudah dibangun; bangun ulang untuk user ini.
        app()->forgetInstance(AdminLte::class);

        $texts = [];
        $menu = app(AdminLte::class)->menu('sidebar');
        array_walk_recursive($menu, function ($value, $key) use (&$texts) {
            if ($key === 'text') {
                $texts[] = $value;
            }
        });

        return $texts;
    }

    public function test_management_sees_dashboards_only(): void
    {
        $management = $this->user('Management');

        $this->actingAs($management)->get('/admin/dashboard')->assertOk();
        $this->actingAs($management)->get('/admin/dashboards/crewing')->assertOk()->assertDontSee('Lihat detail');

        foreach (['/admin/crews', '/admin/purchase-requests', '/admin/assurance-programs', '/admin/follow-up', '/admin/documents/manual'] as $url) {
            $this->actingAs($management)->get($url)->assertForbidden();
        }

        $menu = $this->menuTexts($management);
        $this->assertContains('Crewing Dashboard', $menu);
        $this->assertNotContains('Crew Register', $menu);
        $this->assertNotContains('Weekly Action Tracker', $menu);
    }

    public function test_department_admins_reach_only_their_module(): void
    {
        $technical = $this->user('Admin Technical');
        $this->actingAs($technical)->get('/admin/damage-reports')->assertOk();
        $this->actingAs($technical)->get('/admin/budgets')->assertOk();
        $this->actingAs($technical)->get('/admin/maintenance-checklists')->assertOk();
        $this->actingAs($technical)->get('/admin/documents/technical')->assertOk();
        $this->actingAs($technical)->get('/admin/crews')->assertForbidden();

        $crewing = $this->user('Admin Crewing');
        $this->actingAs($crewing)->get('/admin/crew-rotation')->assertOk();
        $this->actingAs($crewing)->get('/admin/purchase-requests')->assertForbidden();

        $purchasing = $this->user('Admin Purchasing');
        $this->actingAs($purchasing)->get('/admin/vendors')->assertOk();
        $this->actingAs($purchasing)->get('/admin/vessel-surveys')->assertForbidden();
        $this->assertContains('Vendor Evaluation', $this->menuTexts($purchasing));
        $this->assertNotContains('Crewing', $this->menuTexts($purchasing));
    }

    public function test_vessel_admin_is_limited_to_own_vessel(): void
    {
        $own = Crew::create(['master_location_id' => $this->astrid->id, 'name' => 'Kru Astrid', 'rank' => 'AB']);
        $other = Crew::create(['master_location_id' => $this->bima->id, 'name' => 'Kru Bima', 'rank' => 'AB']);
        $otherCertificate = $other->certificates()->create(['name' => 'BST']);

        $admin = $this->user('Admin Kapal', $this->astrid);

        $this->actingAs($admin)->get('/admin/crews')->assertOk()->assertSee('Kru Astrid')->assertDontSee('Kru Bima');
        $this->actingAs($admin)->get('/admin/crews/create')->assertOk()->assertSee('OPS ASTRID')->assertDontSee('OPS BIMA');
        $this->actingAs($admin)->get("/admin/crews/{$own->id}")->assertOk();
        $this->actingAs($admin)->get("/admin/crews/{$other->id}")->assertNotFound();

        // Kapal lain yang dikirim lewat form diganti kapal milik akun.
        $this->actingAs($admin)->post('/admin/crews', ['name' => 'Kru Baru', 'rank' => 'Oiler', 'master_location_id' => $this->bima->id]);
        $this->assertSame($this->astrid->id, Crew::where('name', 'Kru Baru')->value('master_location_id'));

        $this->actingAs($admin)->post("/admin/crews/{$other->id}/certificates", ['name' => 'X'])->assertNotFound();
        $this->actingAs($admin)->delete("/admin/crew-certificates/{$otherCertificate->id}")->assertNotFound();
        $this->assertDatabaseHas('crew_certificates', ['id' => $otherCertificate->id]);

        // Data kantor tetap tertutup.
        $this->actingAs($admin)->get('/admin/vendors')->assertForbidden();
        $this->actingAs($admin)->get('/admin/budgets')->assertForbidden();
        $this->actingAs($admin)->get('/admin/documents/crewing')->assertForbidden();

        $this->actingAs($admin)->get('/admin/dashboards/crewing')->assertOk()->assertViewHas('cards', fn ($cards) => $cards[0][1] === 2);
    }

    public function test_vessel_admin_without_vessel_sees_nothing(): void
    {
        Crew::create(['master_location_id' => $this->astrid->id, 'name' => 'Kru Astrid', 'rank' => 'AB']);

        $this->actingAs($this->user('Admin Kapal'));

        $this->assertSame(0, Crew::count());
        $this->assertSame(0, MasterLocation::count());
        $this->assertSame(0, CrewCertificate::count());
    }

    public function test_vessel_admin_account_requires_vessel(): void
    {
        $super = $this->user('Super Admin');

        $data = ['name' => 'Admin Astrid', 'email' => 'astrid@example.com', 'password' => 'rahasia123', 'role' => 'Admin Kapal'];

        $this->actingAs($super)->post('/admin/users', $data)->assertSessionHasErrors('master_location_id');
        $this->actingAs($super)->post('/admin/users', $data + ['master_location_id' => $this->astrid->id])->assertRedirect('/admin/users');
        $this->actingAs($super)->post('/admin/users', ['role' => 'Raja'] + $data)->assertSessionHasErrors('role');

        $this->assertSame($this->astrid->id, User::where('email', 'astrid@example.com')->value('master_location_id'));
    }

    public function test_super_admin_can_edit_user_role_vessel_and_password(): void
    {
        $super = $this->user('Super Admin');
        $user = $this->user('Admin Kapal', $this->astrid);
        $oldPassword = $user->password;

        $this->actingAs($super)->get("/admin/users/{$user->id}/edit")->assertOk()->assertSee($user->email);

        $data = ['name' => 'Admin Bima', 'email' => $user->email, 'password' => '', 'role' => 'Admin Kapal', 'master_location_id' => $this->bima->id];

        $this->actingAs($super)->put("/admin/users/{$user->id}", $data)->assertRedirect('/admin/users');
        $user->refresh();
        $this->assertSame([$this->bima->id, 'Admin Bima', $oldPassword], [$user->master_location_id, $user->name, $user->password]);

        // Pindah role: kapal dilepas, password baru di-hash.
        $this->actingAs($super)->put("/admin/users/{$user->id}", ['role' => 'Management', 'password' => 'passwordbaru'] + $data);
        $user->refresh();
        $this->assertSame('Management', $user->role);
        $this->assertNull($user->master_location_id);
        $this->assertTrue(Hash::check('passwordbaru', $user->password));

        // Email tidak boleh dipakai user lain.
        $this->actingAs($super)->put("/admin/users/{$user->id}", ['email' => $super->email] + $data)->assertSessionHasErrors('email');
    }

    public function test_super_admin_cannot_change_own_role(): void
    {
        $super = $this->user('Super Admin');

        $this->actingAs($super)->put("/admin/users/{$super->id}", [
            'name' => 'Super', 'email' => $super->email, 'role' => 'Management',
        ])->assertRedirect('/admin/users');

        $this->assertSame('Super Admin', $super->fresh()->role);
    }
}
