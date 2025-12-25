<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use Tests\Support\TestSchedulable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Roster\Repositories\AvailabilityRepository;
use Roster\Models\Availability;
use Roster\Exceptions\ForbiddenModelMutationException;
use Tests\TestCase;

final class RepositoryMutationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // $this->artisan('migrate')->run();
    }


    public function test_repository_can_create(): void
    {
        $availabilityRepository = new AvailabilityRepository();

        $model = $availabilityRepository->create([
            'schedulable_id' => 1,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => json_encode(['monday', 'tuesday']),
            'validity_start' => '2025-01-01',
            'validity_end' => '2025-12-31',
        ]);

        $this->assertInstanceOf(Availability::class, $model);
    }

    public function test_repository_can_update(): void
    {
        $availabilityRepository = new AvailabilityRepository();

        $model = $availabilityRepository->create([
            'schedulable_id' => 1,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => json_encode(['monday', 'tuesday']),
            'validity_start' => '2025-01-01',
            'validity_end' => '2025-12-31',
        ]);

        $updated = $availabilityRepository->update($model->id, ['daily_start' => '10:00:00']);

        $this->assertTrue($updated);
    }

    public function test_repository_can_delete(): void
    {
        $availabilityRepository = new AvailabilityRepository();

        $model = $availabilityRepository->create([
            'schedulable_id' => 1,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => json_encode(['monday', 'tuesday']),
            'validity_start' => '2025-01-01',
            'validity_end' => '2025-12-31',
        ]);

        $deleted = $availabilityRepository->delete($model->id);

        $this->assertTrue($deleted);
    }

    public function test_direct_delete_still_forbidden(): void
    {
        $availabilityRepository = new AvailabilityRepository();

        $model = $availabilityRepository->create([
            'schedulable_id' => 1,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => json_encode(['monday', 'tuesday']),
            'validity_start' => '2025-01-01',
            'validity_end' => '2025-12-31',
        ]);

        $this->expectException(ForbiddenModelMutationException::class);

        $model->delete();
    }
}
