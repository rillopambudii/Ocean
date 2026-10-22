<?php

namespace Tests\Feature;

use App\Models\MasterLocation;
use App\Models\SafetyObservation;
use App\Models\TrainingActivity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ObservationTrainingTest extends TestCase
{
    use RefreshDatabase;

    private User $qhse;

    private MasterLocation $vessel;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->qhse = User::factory()->create(['role' => 'Admin QHSE']);

        $this->vessel = MasterLocation::create([
            'area' => 'Kalimantan',
            'location' => 'Balikpapan',
            'name' => 'OPS AORA',
            'type' => 'Vessel',
        ]);
    }

    private function observation(array $overrides = []): array
    {
        return array_merge([
            'master_location_id' => $this->vessel->id,
            'observed_date' => today()->subDays(10)->toDateString(),
            'category' => 'Unsafe Condition',
            'description' => 'Deck housekeeping follow-up',
            'pic' => 'Chief Officer',
            'due_date' => today()->subDay()->toDateString(),
            'status' => 'Open',
        ], $overrides);
    }

    public function test_observation_flow_with_overdue_and_close(): void
    {
        $this->actingAs($this->qhse)->post('/admin/safety-observations', $this->observation([
            'evidence' => UploadedFile::fake()->create('foto.jpg', 20, 'image/jpeg'),
        ]))->assertRedirect('/admin/safety-observations');

        $observation = SafetyObservation::firstOrFail();
        $this->assertTrue($observation->isOverdue());
        Storage::disk('local')->assertExists($observation->evidence_path);

        $this->actingAs($this->qhse)->get('/admin/safety-observations')
            ->assertOk()
            ->assertSee($observation->code())
            ->assertSee('1 observasi masih Open');

        $this->actingAs($this->qhse)->put("/admin/safety-observations/{$observation->id}", $this->observation(['status' => 'Closed']));

        $observation->refresh();
        $this->assertTrue($observation->closed_date->isToday());
        $this->assertFalse($observation->isOverdue());
        // Evidence lama tetap ada karena tidak diganti.
        Storage::disk('local')->assertExists($observation->evidence_path);
    }

    public function test_training_activity_code_and_status(): void
    {
        $this->actingAs($this->qhse)->post('/admin/training-activities', [
            'master_location_id' => $this->vessel->id,
            'activity_type' => 'Emergency Drill',
            'topic' => 'Fire drill',
            'planned_date' => today()->subDays(2)->toDateString(),
            'participants' => 12,
        ])->assertRedirect('/admin/training-activities');

        $activity = TrainingActivity::firstOrFail();
        $this->assertSame(sprintf('DRL-%04d', $activity->id), $activity->code());
        $this->assertSame('Overdue', $activity->status());

        $this->actingAs($this->qhse)->get('/admin/training-activities')
            ->assertOk()
            ->assertSee('Fire drill')
            ->assertSee('1 kegiatan sudah lewat');

        $this->actingAs($this->qhse)->delete("/admin/training-activities/{$activity->id}")
            ->assertRedirect('/admin/training-activities');
        $this->assertSame(0, TrainingActivity::count());
    }

    public function test_invalid_category_and_type_are_rejected(): void
    {
        $this->actingAs($this->qhse)->post('/admin/safety-observations', $this->observation(['category' => 'Asal']))
            ->assertSessionHasErrors('category');

        $this->actingAs($this->qhse)->post('/admin/training-activities', [
            'master_location_id' => $this->vessel->id,
            'activity_type' => 'Asal',
            'topic' => 'x',
            'planned_date' => '2026-11-01',
        ])->assertSessionHasErrors('activity_type');
    }

    public function test_admin_operation_is_forbidden(): void
    {
        $user = User::factory()->create(['role' => 'Admin Operation']);

        $this->actingAs($user)->get('/admin/safety-observations')->assertForbidden();
        $this->actingAs($user)->get('/admin/training-activities')->assertForbidden();
    }
}
