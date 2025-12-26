<?php

declare(strict_types=1);

namespace Tests\Unit\Validation\Rules;


use Illuminate\Foundation\Testing\RefreshDatabase;
use Roster\Facades\Availability as AvailabilityFacade;
use Roster\Facades\Impediment as ImpedimentFacade;
use Roster\Facades\Schedule as ScheduleFacade;
use Roster\Validation\Exceptions\ValidationFailedException;
use Tests\Support\TestSchedulable;
use Tests\TestCase;

final class ImpedimentScheduleDaysCoherenceRuleTest extends TestCase
{
    use RefreshDatabase;

    private TestSchedulable $testSchedulable;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testSchedulable = TestSchedulable::create();
    }

    public function test_cannot_create_impediment_on_non_availability_day(): void
    {
        // Arrange - Disponibilité uniquement mardi et jeudi
        $availability = AvailabilityFacade::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['tuesday', 'thursday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Expect - impediment sur un jour interdit
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/failed for Impediment.*not allowed.*following days/i');


        // Act
        ImpedimentFacade::for($this->testSchedulable)
            ->owner($availability)
            ->create([
                'reason' => 'Impediment lundi',
                'start_datetime' => '2038-01-04 10:00:00', // lundi
                'end_datetime' => '2038-01-04 11:00:00',
            ]);
    }

    public function test_cannot_create_schedule_on_non_availability_day(): void
    {
        // Arrange - Même disponibilité
        $availability = AvailabilityFacade::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['tuesday', 'thursday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Expect - schedule sur un jour interdit
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches(
            "/failed for Schedule.*not allowed because this availability only permits/"
        );


        // Act
        ScheduleFacade::for($this->testSchedulable)
            ->owner($availability)
            ->create([
                'reason' => 'Schedule mercredi',
                'start_datetime' => '2038-01-06 10:00:00', // mercredi
                'end_datetime' => '2038-01-06 11:00:00',
            ]);
    }

    public function test_can_create_impediment_on_allowed_day(): void
    {
        // Arrange - disponibilité mardi et jeudi
        $availability = AvailabilityFacade::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['tuesday', 'thursday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Act
        $impediment = ImpedimentFacade::for($this->testSchedulable)
            ->owner($availability)
            ->create([
                'reason' => 'Impediment mardi',
                'start_datetime' => '2038-01-05 10:00:00', // mardi
                'end_datetime' => '2038-01-05 11:00:00',
            ]);

        // Assert
        $this->assertNotNull($impediment);
        $this->assertDatabaseHas('roster_impediments', ['id' => $impediment->id]);
    }
}
