<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Roster\Models\Availability;
use Roster\Support\RosterMutationContext;
use Tests\TestCase;
use Tests\Support\TestSchedulable;

/**
 * Unit tests for RosterMutationContext functionality.
 *
 * Validates that database mutations (create, update, delete) are allowed
 * within the mutation context while maintaining proper constraints.
 */
final class MutationContextAllowsMutationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Set up test environment and run migrations.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate')->run();
    }

    /**
     * Test that creation inside mutation context is allowed.
     */
    public function test_creation_inside_context_is_allowed(): void
    {
        // Arrange: Prepare test data
        $testData = [
            'schedulable_id' => 1,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday'],
            'validity_start' => '2025-01-01',
            'validity_end' => '2025-12-31',
        ];

        // Act: Create availability within mutation context
        $availability = RosterMutationContext::allow(function () use ($testData) {
            return $this->createAvailability($testData);
        });

        // Assert: Availability was successfully created
        $this->assertNotNull($availability->id);
        $this->assertEquals('consultation', $availability->type);
        $this->assertEquals(['monday', 'tuesday'], json_decode($availability->days, true));
    }

    /**
     * Test that update inside mutation context is allowed.
     */
    public function test_update_inside_context_is_allowed(): void
    {
        // Arrange: Create initial availability
        $availability = RosterMutationContext::allow(function () {
            return $this->createAvailability([
                'schedulable_id' => 1,
                'schedulable_type' => TestSchedulable::class,
                'type' => 'consultation',
                'daily_start' => '09:00:00',
                'daily_end' => '17:00:00',
                'days' => ['monday', 'tuesday'],
                'validity_start' => '2025-01-01',
                'validity_end' => '2025-12-31',
            ]);
        });

        // Act: Update availability within mutation context
        $updateResult = RosterMutationContext::allow(function () use ($availability) {
            return $availability->update(['daily_start' => '10:00:00']);
        });

        // Assert: Update was successful
        $this->assertTrue($updateResult);
        $availability->refresh();
        $this->assertEquals('10:00:00', $availability->daily_start->format('H:i:s'));
        $this->assertEquals('17:00:00', $availability->daily_end->format('H:i:s'));
    }

    /**
     * Test that deletion inside mutation context is allowed.
     */
    public function test_delete_inside_context_is_allowed(): void
    {
        // Arrange: Create availability
        $availability = RosterMutationContext::allow(function () {
            return $this->createAvailability([
                'schedulable_id' => 1,
                'schedulable_type' => TestSchedulable::class,
                'type' => 'consultation',
                'daily_start' => '09:00:00',
                'daily_end' => '17:00:00',
                'days' => ['monday', 'tuesday'],
                'validity_start' => '2025-01-01',
                'validity_end' => '2025-12-31',
            ]);
        });

        $availabilityId = $availability->id;

        // Act: Delete availability within mutation context
        $deleteResult = RosterMutationContext::allow(function () use ($availability) {
            return $availability->delete();
        });

        // Assert: Deletion was successful
        $this->assertTrue($deleteResult);
        $this->assertSoftDeleted('roster_availabilities', [
            'id' => $availabilityId,
        ]);
        $this->assertNull(Availability::find($availabilityId));
    }

    /**
     * Create an availability with the given data.
     *
     * @param array<string, mixed> $data
     * @return Availability
     */
    private function createAvailability(array $data): Availability
    {
        $availabilityData = $data;
        $availabilityData['days'] = json_encode($availabilityData['days']);

        return Availability::create($availabilityData);
    }
}
