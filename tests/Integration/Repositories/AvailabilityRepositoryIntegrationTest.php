<?php

declare(strict_types=1);

namespace Tests\Integration\Repositories;

use Illuminate\Database\Eloquent\Model;
use Roster\Services\Core\ValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
use Roster\Models\Availability;
use Roster\Repositories\AvailabilityRepository;

final class AvailabilityRepositoryIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private AvailabilityRepository $availabilityRepository;

    private $schedulable;

    protected function setUp(): void
    {
        parent::setUp();

        // Créez un modèle schedulable fictif
        $this->schedulable = new class extends Model {
            protected $table = 'test_schedulables';

            public $timestamps = false;

            protected $guarded = [];
        };

        // Créez la table pour les tests
        if (Schema::hasTable('test_schedulables')) {
            Schema::create('test_schedulables', function ($table): void {
                $table->id();
            });
        }

        $this->schedulable->create(['id' => 1]);
        $this->schedulable = $this->schedulable->find(1);

        $validationService = new ValidationService();
        $this->availabilityRepository = new AvailabilityRepository($validationService);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('test_schedulables');
        parent::tearDown();
    }

    public function test_find_for_time_slot_returns_availability(): void
    {
        $availability = Availability::create([
            'schedulable_id' => $this->schedulable->id,
            'schedulable_type' => get_class($this->schedulable),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['monday'],
        ]);

        $start = Carbon::parse('2024-01-01 10:00:00'); // Monday
        $end = Carbon::parse('2024-01-01 11:00:00');

        $result = $this->availabilityRepository->findForTimeSlot($this->schedulable, $start, $end);

        $this->assertNotNull($result);
        $this->assertSame($availability->id, $result->id);
        $this->assertSame('consultation', $result->type);
    }

    public function test_find_for_time_slot_with_type_filter(): void
    {
        Availability::create([
            'schedulable_id' => $this->schedulable->id,
            'schedulable_type' => get_class($this->schedulable),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['monday'],
        ]);

        Availability::create([
            'schedulable_id' => $this->schedulable->id,
            'schedulable_type' => get_class($this->schedulable),
            'type' => 'training',
            'start_time' => '14:00:00',
            'end_time' => '17:00:00',
            'days' => ['monday'],
        ]);

        $start = Carbon::parse('2024-01-01 15:00:00');
        $end = Carbon::parse('2024-01-01 16:00:00');

        $result = $this->availabilityRepository->findForTimeSlot($this->schedulable, $start, $end, 'training');

        $this->assertNotNull($result);
        $this->assertSame('training', $result->type);
    }

    public function test_is_available_at_returns_correct_value(): void
    {
        Availability::create([
            'schedulable_id' => $this->schedulable->id,
            'schedulable_type' => get_class($this->schedulable),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['monday'],
        ]);

        // Available time
        $availableTime = Carbon::parse('2024-01-01 10:00:00');
        $this->assertTrue($this->availabilityRepository->isAvailableAt($this->schedulable, $availableTime));

        // Unavailable time (wrong day)
        $unavailableDay = Carbon::parse('2024-01-02 10:00:00'); // Tuesday
        $this->assertFalse($this->availabilityRepository->isAvailableAt($this->schedulable, $unavailableDay));

        // Unavailable time (outside hours)
        $unavailableTime = Carbon::parse('2024-01-01 08:00:00');
        $this->assertFalse($this->availabilityRepository->isAvailableAt($this->schedulable, $unavailableTime));
    }
}
