<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Roster\Enums\DaysOfWeek;
use Roster\Facades\Availability;
use Roster\Validation\Exceptions\ValidationFailedException;
use Tests\Support\TestSchedulable;
use Tests\TestCase;

final class AvailabilityServiceDaysCoherenceTest extends TestCase
{
    use RefreshDatabase;

    private TestSchedulable $testSchedulable;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testSchedulable = TestSchedulable::create();
    }

    public function test_auto_adjusts_days_when_not_provided_and_period_less_than_7_days(): void
    {
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-04',
        ]);

        $this->assertEquals(
            ['thursday', 'friday', 'saturday', 'sunday'],
            $availability->days
        );
    }

    public function test_uses_provided_days_when_period_less_than_7_days(): void
    {
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['thursday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-04',
        ]);

        $this->assertEquals(['thursday', 'friday'], $availability->days);
    }

    public function test_validation_fails_when_provided_days_not_in_period(): void
    {
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches("/Day 'monday' is not within the validity period/");

        Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'thursday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-04',
        ]);
    }

    public function test_auto_adjusts_days_for_exact_week_period(): void
    {
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-07',
        ]);

        $this->assertEquals(DaysOfWeek::values(), $availability->days);
    }

    public function test_period_more_than_7_days_uses_all_days_by_default(): void
    {
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-15',
        ]);

        $this->assertEquals(DaysOfWeek::values(), $availability->days);
    }

    public function test_update_removes_days_not_in_new_period(): void
    {
        $schedulable = $this->testSchedulable;

        // Crée une availability avec plusieurs jours
        $availability = Availability::for($schedulable)->create([
            'type'           => 'consultation',
            'daily_start'    => '09:00:00',
            'daily_end'      => '17:00:00',
            'days'           => ['monday', 'wednesday', 'friday', 'sunday'],
            'validity_start' => '2038-07-05',
            'validity_end'   => '2038-07-18',
        ]);

        // Mise à jour de la fin de validité
        $result = Availability::for($schedulable)
            ->update(
                id: $availability->id,
                data: ['validity_end' => '2038-07-10']
            );

        $this->assertTrue($result);

        // Rafraîchir l'entité pour vérifier les jours
        $availability->refresh();

        // Les jours en dehors de la nouvelle période doivent disparaître
        $this->assertNotContains('sunday', $availability->days);

        // Les jours encore valides doivent rester
        $this->assertContains('monday', $availability->days);
        $this->assertContains('wednesday', $availability->days);
        $this->assertContains('friday', $availability->days);
    }


    public function test_update_does_not_add_new_days_even_if_in_period(): void
    {
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'wednesday'],
            'validity_start' => '2038-07-05',
            'validity_end' => '2038-07-11',
        ]);

        $result = Availability::for($this->testSchedulable)
            ->update($availability->id, [
                'validity_end' => '2038-07-18',
            ]);

        $this->assertTrue($result);

        $availability->refresh();
        $this->assertEquals(['monday', 'wednesday'], $availability->days);
    }

    public function test_update_validation_fails_when_new_days_not_in_new_period(): void
    {
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'wednesday', 'friday'],
            'validity_start' => '2038-07-05',
            'validity_end' => '2038-07-18',
        ]);

        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches("/Day 'saturday' is not within the validity period/");

        Availability::for($this->testSchedulable)
            ->update($availability->id, [
                'days' => ['monday', 'saturday'],
                'validity_end' => '2038-07-09',
            ]);
    }

    public function test_auto_adjusts_days_for_single_day_period(): void
    {
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-01',
        ]);

        $this->assertEquals(['thursday'], $availability->days);
    }

    public function test_no_auto_adjustment_when_days_explicitly_provided(): void
    {
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['thursday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-04',
        ]);

        $this->assertEquals(['thursday', 'friday'], $availability->days);
    }

    public function test_update_with_days_array_replaces_all_days(): void
    {
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'wednesday', 'friday'],
            'validity_start' => '2038-07-05',
            'validity_end' => '2038-07-18',
        ]);

        $result = Availability::for($this->testSchedulable)
            ->update($availability->id, [
                'days' => ['tuesday', 'thursday'],
            ]);

        $this->assertTrue($result);

        $availability->refresh();
        $this->assertEquals(['tuesday', 'thursday'], $availability->days);
    }

    public function test_period_spanning_multiple_weeks_with_auto_adjustment(): void
    {
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-10',
        ]);

        $expected = [
            'thursday',
            'friday',
            'saturday',
            'sunday',
            'monday',
            'tuesday',
            'wednesday',
        ];

        sort($expected);
        $actual = $availability->days;
        sort($actual);

        $this->assertEquals($expected, $actual);
    }
}
