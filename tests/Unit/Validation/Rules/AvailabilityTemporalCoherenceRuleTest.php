<?php

declare(strict_types=1);

namespace Tests\Unit\Validation\Rules;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Roster\Facades\Availability as AvailabilityFacade;
use Roster\Facades\Schedule as ScheduleFacade;
use Roster\Facades\Impediment as ImpedimentFacade;
use Roster\Validation\Exceptions\ValidationFailedException;
use Tests\Support\TestSchedulable;
use Tests\TestCase;

final class AvailabilityTemporalCoherenceRuleTest extends TestCase
{
    use RefreshDatabase;

    private TestSchedulable $testSchedulable;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testSchedulable = TestSchedulable::create();
    }

    public function test_cannot_shorten_availability_before_existing_future_schedule(): void
    {
        // Arrange
        $availability = AvailabilityFacade::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday', 'wednesday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        ScheduleFacade::for($this->testSchedulable)->owner($availability)->create([
            'title' => 'Future Meeting',
            'reason' => 'Future meeting',
            'start_datetime' => '2038-01-04 10:00:00', // lundi
            'end_datetime' => '2038-01-04 11:00:00',
        ]);

        // Act & Assert
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches("/Cannot set validity_start/");

        AvailabilityFacade::for($this->testSchedulable)->update($availability->id, [
            'validity_start' => '2038-01-05', // conflict avec schedule futur
        ]);
    }

    public function test_cannot_extend_availability_end_before_existing_future_schedule(): void
    {
        // Arrange
        $availability = AvailabilityFacade::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        ScheduleFacade::for($this->testSchedulable)->owner($availability)->create([
            'title' => 'Future meeting',
            'reason' => 'Future meeting',
            'start_datetime' => '2038-01-11 10:00:00', // monday ✅
            'end_datetime' => '2038-01-11 11:00:00',
        ]);

        // Act & Assert
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches("/Cannot set validity_end/");

        AvailabilityFacade::for($this->testSchedulable)->update($availability->id, [
            'validity_end' => '2038-01-05',
        ]);
    }

    public function test_cannot_remove_days_with_future_impediments(): void
    {
        // Arrange
        $availability = AvailabilityFacade::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'thursday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        ImpedimentFacade::for($this->testSchedulable)->owner($availability)->create([
            'reason' => 'Future impediment',
            'start_datetime' => '2038-01-07 10:00:00', // jeudi
            'end_datetime' => '2038-01-07 11:00:00',
        ]);

        // Act & Assert
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches(
            "/Cannot remove 'Thursday' from days because it is used by a future impediment/i"
        );


        AvailabilityFacade::for($this->testSchedulable)->update($availability->id, [
            'days' => ['monday'], // enlever jeudi
        ]);
    }

    public function test_can_update_availability_without_conflict(): void
    {
        // Arrange
        $availability = AvailabilityFacade::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Act - Mise à jour qui ne touche pas les schedules ou impediments futurs
        $result = AvailabilityFacade::for($this->testSchedulable)->update($availability->id, [
            'daily_end' => '18:00:00',
        ]);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseHas('roster_availabilities', [
            'id' => $availability->id,
            'daily_end' => '18:00:00',
        ]);
    }

    public function test_cannot_delete_availability_with_future_schedules(): void
    {
        // Arrange
        $availability = AvailabilityFacade::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        ScheduleFacade::for($this->testSchedulable)->owner($availability)->create([
            'title' => 'Future Meeting',
            'reason' => 'Future meeting',
            'start_datetime' => '2038-01-04 10:00:00', // lundi
            'end_datetime' => '2038-01-04 11:00:00',
        ]);

        // Act & Assert
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches("/Cannot delete availability with future schedules/");

        AvailabilityFacade::for($this->testSchedulable)->delete($availability->id);
    }

    public function test_cannot_delete_availability_with_future_impediments(): void
    {
        // Arrange
        $availability = AvailabilityFacade::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'thursday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        ImpedimentFacade::for($this->testSchedulable)->owner($availability)->create([
            'reason' => 'Future impediment',
            'start_datetime' => '2038-01-07 10:00:00', // jeudi
            'end_datetime' => '2038-01-07 11:00:00',
        ]);

        // Act & Assert
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches("/Cannot delete availability with future impediments/");

        AvailabilityFacade::for($this->testSchedulable)->delete($availability->id);
    }

    public function test_can_delete_availability_without_future_conflict(): void
    {
        // Arrange — disponibilité cohérente, sans futur
        $availability = AvailabilityFacade::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['friday', 'saturday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-02',
        ]);

        // Act
        $result = AvailabilityFacade::for($this->testSchedulable)->delete($availability->id);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseMissing('roster_availabilities', [
            'id' => $availability->id,
        ]);
    }
}
