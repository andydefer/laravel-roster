<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use Roster\Support\RosterMutationContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Roster\Models\Availability;
use Roster\Exceptions\ForbiddenModelMutationException;
use Tests\Support\TestSchedulable;
use Tests\TestCase;

final class ModelMutationForbiddenTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate')->run();
    }

    public function test_creation_direct_is_forbidden(): void
    {
        $this->expectException(ForbiddenModelMutationException::class);

        Availability::create([
            'schedulable_id' => 1,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => json_encode(['monday', 'tuesday']),
            'validity_start' => '2025-01-01',
            'validity_end' => '2025-12-31',
        ]);
    }

    public function test_update_direct_is_forbidden(): void
    {
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

        $this->expectException(ForbiddenModelMutationException::class);

        $availability->save();
    }

    public function test_delete_direct_is_forbidden(): void
    {
        // Crée le modèle **en DB via le context** pour bypasser le blocage de création
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

        // Ici on teste la suppression directe
        $this->expectException(ForbiddenModelMutationException::class);

        $availability->delete();
    }
}
