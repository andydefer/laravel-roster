<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use Tests\Support\TestSchedulable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Roster\Models\Availability;
use Roster\Support\RosterMutationContext;
use Tests\TestCase;

final class MutationContextAllowsMutationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate')->run();
    }

    public function test_creation_inside_context_is_allowed(): void
    {
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

        $this->assertNotNull($availability->id);
    }

    public function test_update_inside_context_is_allowed(): void
    {
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

        $updated = RosterMutationContext::allow(function () use ($availability) {
            return $availability->update(['daily_start' => '10:00:00']);
        });

        $this->assertTrue($updated);
    }

    public function test_delete_inside_context_is_allowed(): void
    {
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

        $deleted = RosterMutationContext::allow(function () use ($availability) {
            return $availability->delete();
        });

        $this->assertTrue($deleted);
    }
}
