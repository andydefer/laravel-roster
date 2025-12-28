<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Roster\Exceptions\ForbiddenModelMutationException;
use Roster\Models\Availability;
use Roster\Support\RosterMutationContext;
use Tests\TestCase;
use Tests\Support\TestSchedulable;

/**
 * Unit tests for model mutation protection mechanisms.
 *
 * Validates that direct model operations (create, update, delete)
 * are properly restricted and can only be performed through authorized contexts.
 */
final class ModelMutationForbiddenTest extends TestCase
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
     * Test that direct creation of Availability model is forbidden.
     */
    public function test_creation_direct_is_forbidden(): void
    {
        // Arrange: Attempt to create Availability directly
        $availabilityData = [
            'schedulable_id' => 1,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => json_encode(['monday', 'tuesday']),
            'validity_start' => '2025-01-01',
            'validity_end' => '2025-12-31',
        ];

        // Assert: Should throw forbidden mutation exception
        $this->expectException(ForbiddenModelMutationException::class);

        // Act: Attempt direct creation
        Availability::create($availabilityData);
    }

    /**
     * Test that direct update of Availability model is forbidden.
     */
    public function test_update_direct_is_forbidden(): void
    {
        // Arrange: Create a model instance without saving
        $availability = Availability::make([
            'schedulable_id' => 1,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => json_encode(['monday', 'tuesday']),
            'validity_start' => '2025-01-01',
            'validity_end' => '2025-12-31',
        ]);

        // Assert: Should throw forbidden mutation exception
        $this->expectException(ForbiddenModelMutationException::class);

        // Act: Attempt to save the model directly
        $availability->save();
    }

    /**
     * Test that direct deletion of Availability model is forbidden.
     */
    public function test_delete_direct_is_forbidden(): void
    {
        // Arrange: Create model through authorized context
        $availability = RosterMutationContext::allow(function () {
            return Availability::create([
                'schedulable_id' => 1,
                'schedulable_type' => TestSchedulable::class,
                'type' => 'consultation',
                'daily_start' => '09:00:00',
                'daily_end' => '17:00:00',
                'days' => json_encode(['monday', 'tuesday']),
                'validity_start' => '2025-01-01',
                'validity_end' => '2025-12-31',
            ]);
        });

        // Assert: Should throw forbidden mutation exception
        $this->expectException(ForbiddenModelMutationException::class);

        // Act: Attempt direct deletion
        $availability->delete();
    }
}
