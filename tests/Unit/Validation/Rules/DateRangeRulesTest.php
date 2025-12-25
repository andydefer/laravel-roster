<?php

declare(strict_types=1);

namespace Tests\Unit\Validation\Rules;

use stdClass;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Models\Availability;
use Roster\Validation\Context\ValidationContext;
use Roster\Validation\Rules\AvailabilityDateRangeRule;
use Roster\Validation\Rules\TimeSlotDateTimeRule;
use Tests\Support\TestSchedulable;
use Tests\TestCase;
use Roster\Facades\Availability as AvailabilityFacade;
use Roster\Support\RosterMutationContext;

final class DateRangeRulesTest extends TestCase
{
    private AvailabilityDateRangeRule $availabilityDateRangeRule;
    private TimeSlotDateTimeRule $timeSlotDateTimeRule;
    private TestSchedulable $testSchedulable;

    protected function setUp(): void
    {
        parent::setUp();

        $this->availabilityDateRangeRule = new AvailabilityDateRangeRule();
        $this->timeSlotDateTimeRule = new TimeSlotDateTimeRule();
        $this->testSchedulable = TestSchedulable::create();
    }

    // Tests pour AvailabilityDateRangeRule

    public function test_availability_validate_create_success(): void
    {
        $data = [
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
        ];

        $validationContext = new ValidationContext(
            OperationType::CREATE,
            EntityType::AVAILABILITY,
            $data,
            $this->testSchedulable
        );

        $this->availabilityDateRangeRule->validate($validationContext);

        $this->assertFalse($validationContext->hasViolations());
    }

    public function test_availability_validate_create_fails_when_end_before_start(): void
    {
        $data = [
            'validity_start' => '2038-07-31',
            'validity_end' => '2038-07-01',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
        ];

        $validationContext = new ValidationContext(
            OperationType::CREATE,
            EntityType::AVAILABILITY,
            $data,
            $this->testSchedulable
        );

        $this->availabilityDateRangeRule->validate($validationContext);

        $this->assertTrue($validationContext->hasViolations());
        $this->assertArrayHasKey('validity_date_range', $validationContext->getViolations());
    }

    public function test_availability_validate_update_partial_date(): void
    {
        // Créer une disponibilité existante via le service autorisé
        $availability = RosterMutationContext::allow(function () {
            // Créer directement dans le contexte de mutation autorisé
            return Availability::create([
                'schedulable_id' => $this->testSchedulable->id,
                'schedulable_type' => TestSchedulable::class,
                'type' => 'consultation',
                'daily_start' => '09:00:00',
                'daily_end' => '17:00:00',
                'days' => ['monday'],
                'validity_start' => '2038-07-01',
                'validity_end' => '2038-07-31',
            ]);
        });

        // Mise à jour avec seulement la date de fin modifiée (valide)
        $data = [
            'validity_end' => '2038-08-15',
        ];

        $validationContext = new ValidationContext(
            OperationType::UPDATE,
            EntityType::AVAILABILITY,
            $data,
            $this->testSchedulable,
            $availability
        );

        $this->availabilityDateRangeRule->validate($validationContext);

        $this->assertFalse($validationContext->hasViolations());
    }

    public function test_availability_validate_update_partial_date_fails(): void
    {
        // Créer une disponibilité existante via le service autorisé
        $availability = RosterMutationContext::allow(function () {
            return Availability::create([
                'schedulable_id' => $this->testSchedulable->id,
                'schedulable_type' => TestSchedulable::class,
                'type' => 'consultation',
                'daily_start' => '09:00:00',
                'daily_end' => '17:00:00',
                'days' => ['monday'],
                'validity_start' => '2038-07-01',
                'validity_end' => '2038-07-31',
            ]);
        });

        // Mise à jour invalide : date de fin avant date de début existante
        $data = [
            'validity_end' => '2038-06-30',
        ];

        $validationContext = new ValidationContext(
            OperationType::UPDATE,
            EntityType::AVAILABILITY,
            $data,
            $this->testSchedulable,
            $availability
        );

        $this->availabilityDateRangeRule->validate($validationContext);

        $this->assertTrue($validationContext->hasViolations());
        $this->assertArrayHasKey('validity_date_range', $validationContext->getViolations());
    }

    public function test_availability_validate_update_skip_when_no_dates_changed(): void
    {
        // Créer une disponibilité existante via le service autorisé
        $availability = RosterMutationContext::allow(function () {
            return Availability::create([
                'schedulable_id' => $this->testSchedulable->id,
                'schedulable_type' => TestSchedulable::class,
                'type' => 'consultation',
                'daily_start' => '09:00:00',
                'daily_end' => '17:00:00',
                'days' => ['monday'],
                'validity_start' => '2038-07-01',
                'validity_end' => '2038-07-31',
            ]);
        });

        // Mise à jour sans toucher aux dates
        $data = [
            'type' => 'training', // Seulement le type change
        ];

        $validationContext = new ValidationContext(
            OperationType::UPDATE,
            EntityType::AVAILABILITY,
            $data,
            $this->testSchedulable,
            $availability
        );

        $this->availabilityDateRangeRule->validate($validationContext);

        // Ne devrait pas avoir d'erreur car les dates ne sont pas modifiées
        $this->assertFalse($validationContext->hasViolations());
    }

    public function test_availability_validate_period_too_long(): void
    {
        $data = [
            'validity_start' => '2038-01-01',
            'validity_end' => '2039-02-01', // 397 jours
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
        ];

        $validationContext = new ValidationContext(
            OperationType::CREATE,
            EntityType::AVAILABILITY,
            $data,
            $this->testSchedulable
        );

        $this->availabilityDateRangeRule->validate($validationContext);

        $this->assertTrue($validationContext->hasViolations());
        $this->assertArrayHasKey('max_duration', $validationContext->getViolations());
        $this->assertStringContainsString('cannot exceed 365 days', (string) $validationContext->getViolations()['max_duration']);
    }

    public function test_availability_validate_time_too_short(): void
    {
        $data = [
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
            'daily_start' => '09:00:00',
            'daily_end' => '09:04:00', // 4 minutes
        ];

        $validationContext = new ValidationContext(
            OperationType::CREATE,
            EntityType::AVAILABILITY,
            $data,
            $this->testSchedulable
        );

        $this->availabilityDateRangeRule->validate($validationContext);

        $this->assertTrue($validationContext->hasViolations());
        $this->assertArrayHasKey('min_duration', $validationContext->getViolations());
    }

    // Tests pour TimeSlotDateTimeRule

    public function test_schedule_validate_create_success(): void
    {
        $data = [
            'start_datetime' => '2038-07-01 10:00:00',
            'end_datetime' => '2038-07-01 11:00:00',
        ];

        $validationContext = new ValidationContext(
            OperationType::CREATE,
            EntityType::SCHEDULE,
            $data,
            $this->testSchedulable
        );

        $this->timeSlotDateTimeRule->validate($validationContext);

        $this->assertFalse($validationContext->hasViolations());
    }

    public function test_schedule_validate_create_fails(): void
    {
        $data = [
            'start_datetime' => '2038-07-01 11:00:00',
            'end_datetime' => '2038-07-01 10:00:00',
        ];

        $validationContext = new ValidationContext(
            OperationType::CREATE,
            EntityType::SCHEDULE,
            $data,
            $this->testSchedulable
        );

        $this->timeSlotDateTimeRule->validate($validationContext);

        $this->assertTrue($validationContext->hasViolations());
        $this->assertArrayHasKey('datetime_range', $validationContext->getViolations());
    }

    public function test_impediment_validate_create_success(): void
    {
        $data = [
            'start_datetime' => '2038-07-01 11:00:00',
            'end_datetime' => '2038-07-01 12:00:00',
        ];

        $validationContext = new ValidationContext(
            OperationType::CREATE,
            EntityType::IMPEDIMENT,
            $data,
            $this->testSchedulable
        );

        $this->timeSlotDateTimeRule->validate($validationContext);

        $this->assertFalse($validationContext->hasViolations());
    }

    public function test_schedule_validate_update_partial(): void
    {
        // Simuler un schedule existant (pas besoin de le créer réellement pour le test)
        $schedule = new stdClass();
        $schedule->start_datetime = '2038-07-01 10:00:00';
        $schedule->end_datetime = '2038-07-01 11:00:00';

        // Mise à jour avec seulement l'heure de fin modifiée (valide)
        $data = [
            'end_datetime' => '2038-07-01 12:00:00',
        ];

        $validationContext = new ValidationContext(
            OperationType::UPDATE,
            EntityType::SCHEDULE,
            $data,
            $this->testSchedulable,
            $schedule
        );

        $this->timeSlotDateTimeRule->validate($validationContext);

        $this->assertFalse($validationContext->hasViolations());
    }

    public function test_schedule_validate_update_partial_fails(): void
    {
        // Simuler un schedule existant
        $schedule = new stdClass();
        $schedule->start_datetime = '2038-07-01 10:00:00';
        $schedule->end_datetime = '2038-07-01 11:00:00';

        // Mise à jour invalide : heure de début après heure de fin existante
        $data = [
            'start_datetime' => '2038-07-01 12:00:00',
        ];

        $validationContext = new ValidationContext(
            OperationType::UPDATE,
            EntityType::SCHEDULE,
            $data,
            $this->testSchedulable,
            $schedule
        );

        $this->timeSlotDateTimeRule->validate($validationContext);

        $this->assertTrue($validationContext->hasViolations());
        $this->assertArrayHasKey('datetime_range', $validationContext->getViolations());
    }

    public function test_schedule_validate_update_skip_when_no_datetimes_changed(): void
    {
        // Simuler un schedule existant
        $schedule = new stdClass();
        $schedule->start_datetime = '2038-07-01 10:00:00';
        $schedule->end_datetime = '2038-07-01 11:00:00';

        // Mise à jour sans toucher aux datetime
        $data = [
            'title' => 'New Title',
        ];

        $validationContext = new ValidationContext(
            OperationType::UPDATE,
            EntityType::SCHEDULE,
            $data,
            $this->testSchedulable,
            $schedule
        );

        $this->timeSlotDateTimeRule->validate($validationContext);

        // Ne devrait pas avoir d'erreur car les datetime ne sont pas modifiés
        $this->assertFalse($validationContext->hasViolations());
    }
}
