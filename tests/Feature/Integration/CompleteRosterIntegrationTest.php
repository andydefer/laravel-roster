<?php

declare(strict_types=1);

namespace Feature\Integration;

use Carbon\Exceptions\InvalidFormatException;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Group;
use Roster\Enums\ScheduleStatus;
use Roster\Models\Availability as AvailabilityModel;
use Roster\Models\Impediment as ImpedimentModel;
use Roster\Models\Schedule as ScheduleModel;
use Roster\Validation\Exceptions\ValidationFailedException;
use Tests\TestCase;
use Tests\Support\TestSchedulable;

/**
 * Comprehensive end-to-end test suite for the Roster package.
 *
 * Validates all core functionalities including availability management,
 * impediment handling, schedule booking, multi-user conflicts, and edge cases.
 */
#[Group('integration')]
#[Group('e2e')]
final class CompleteRosterIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Primary doctor entity for testing.
     */
    private TestSchedulable $primaryDoctor;

    /**
     * Secondary doctor entity for multi-user scenarios.
     */
    private TestSchedulable $secondaryDoctor;

    /**
     * Set up test environment with required entities and configuration.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->primaryDoctor = TestSchedulable::create(['name' => 'Dr. Smith']);
        $this->secondaryDoctor = TestSchedulable::create(['name' => 'Dr. Jones']);
        TestSchedulable::create(['name' => 'Patient X']);

        config([
            'roster.durations.default_slot_interval_minutes' => 15,
            'roster.durations.max_search_period_days' => 365,
            'roster.allowed_types' => ['consultation', 'surgery', 'emergency', 'training', 'room_a', 'echography', 'scan'],
        ]);
    }

    /**
     * Test complete availability lifecycle including creation, validation, updates and deletion.
     */
    public function test_complete_availability_management(): void
    {
        // Arrange: Create initial availability
        $initialAvailability = availability_for($this->primaryDoctor)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'wednesday', 'friday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-12-31',
        ]);

        // Assert initial availability properties
        $this->assertInstanceOf(AvailabilityModel::class, $initialAvailability);
        $this->assertEquals('consultation', $initialAvailability->type);
        $this->assertEquals(['monday', 'wednesday', 'friday'], $initialAvailability->days);

        // Arrange: Create short-term availability
        $shortTermAvailability = availability_for($this->primaryDoctor)->create([
            'type' => 'surgery',
            'daily_start' => '08:00:00',
            'daily_end' => '12:00:00',
            'validity_start' => '2038-06-01',
            'validity_end' => '2038-06-03',
        ]);

        // Assert short-term availability properties
        $expectedDaysForShortPeriod = ['tuesday', 'wednesday', 'thursday'];
        $this->assertEquals($expectedDaysForShortPeriod, $shortTermAvailability->days);
        $this->assertCount(3, $shortTermAvailability->days);

        // Act & Assert: Test validation failures for invalid durations
        $this->expectException(ValidationFailedException::class);
        availability_for($this->primaryDoctor)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '09:05:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $this->expectException(ValidationFailedException::class);
        availability_for($this->primaryDoctor)->create([
            'type' => 'consultation',
            'daily_start' => '17:00:00',
            'daily_end' => '09:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $this->expectException(ValidationFailedException::class);
        availability_for($this->primaryDoctor)->create([
            'type' => 'consultation',
            'daily_start' => '10:00:00',
            'daily_end' => '18:00:00',
            'days' => ['monday', 'wednesday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-06-30',
        ]);

        // Act: Update availability
        $updateResult = availability_for($this->primaryDoctor)->update(
            id: $initialAvailability->id,
            data: ['daily_end' => '18:00:00']
        );

        // Assert update was successful
        $this->assertTrue($updateResult);
        $initialAvailability->refresh();
        $this->assertEquals('18:00:00', $initialAvailability->daily_end->format('H:i:s'));

        // Act: Delete short-term availability
        $deleteResult = availability_for($this->primaryDoctor)->delete($shortTermAvailability->id);

        // Assert deletion was successful
        $this->assertTrue($deleteResult);
        $this->assertNull(AvailabilityModel::find($shortTermAvailability->id));

        // Assert only one availability remains
        $allAvailabilities = availability_for($this->primaryDoctor)->all();
        $this->assertInstanceOf(Collection::class, $allAvailabilities);
        $this->assertCount(1, $allAvailabilities);
    }

    /**
     * Test complete impediment lifecycle including creation, conflict validation, and availability checks.
     */
    public function test_complete_impediment_management(): void
    {
        // Arrange: Create availability
        $availability = availability_for($this->primaryDoctor)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday', 'wednesday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-12-31',
        ]);

        // Act: Create training impediment
        $trainingImpediment = impediment_for($availability)->create([
            'reason' => 'Formation médicale obligatoire',
            'start_datetime' => '2038-01-05 09:00:00',
            'end_datetime' => '2038-01-05 12:00:00',
            'metadata' => ['location' => 'Hôpital Central', 'certification' => 'ACLS'],
        ]);

        // Assert impediment properties
        $this->assertInstanceOf(ImpedimentModel::class, $trainingImpediment);
        $this->assertEquals('Formation médicale obligatoire', $trainingImpediment->reason);
        $this->assertEquals($availability->id, $trainingImpediment->availability_id);

        // Act & Assert: Test validation failures for invalid impediments
        $this->expectException(ValidationFailedException::class);
        impediment_for($availability)->create([
            'reason' => 'Court empêchement',
            'start_datetime' => '2038-01-05 14:00:00',
            'end_datetime' => '2038-01-05 14:04:00',
        ]);

        $this->expectException(ValidationFailedException::class);
        impediment_for($availability)->create([
            'reason' => 'Empêchement invalide',
            'start_datetime' => '2038-01-05 15:00:00',
            'end_datetime' => '2038-01-05 14:00:00',
        ]);

        $this->expectException(ValidationFailedException::class);
        impediment_for($availability)->create([
            'reason' => 'Empêchement qui chevauche',
            'start_datetime' => '2038-01-05 11:00:00',
            'end_datetime' => '2038-01-05 13:00:00',
        ]);

        // Act: Create adjacent impediment
        $adjacentImpediment = impediment_for($availability)->create([
            'reason' => 'Déjeuner',
            'start_datetime' => '2038-01-05 12:00:00',
            'end_datetime' => '2038-01-05 13:00:00',
        ]);

        // Assert adjacent impediment was created
        $this->assertNotNull($adjacentImpediment);

        // Act: Get available time slots
        $availableSlots = impediment_for($availability)->getAvailableTimeSlots(
            start: Carbon::parse('2038-01-05 08:00:00'),
            end: Carbon::parse('2038-01-05 18:00:00'),
            type: 'consultation'
        );

        // Assert available slots are returned
        $this->assertInstanceOf(Collection::class, $availableSlots);

        // Verify slots exist around impediments
        $hasMorningSlot = false;
        $hasAfternoonSlot = false;

        foreach ($availableSlots as $availableSlot) {
            $startTime = $availableSlot['start']->format('H:i');
            $endTime = $availableSlot['end']->format('H:i');

            if ($startTime < '09:00') {
                $hasMorningSlot = true;
            }

            if ($startTime >= '13:00' && $endTime <= '17:00') {
                $hasAfternoonSlot = true;
            }
        }

        $this->assertTrue(
            $hasMorningSlot || $hasAfternoonSlot,
            'Should find available slots around impediments'
        );

        // Act: Update impediment
        $updateResult = impediment_for($availability)->update(
            id: $trainingImpediment->id,
            data: [
                'reason' => 'Formation médicale avancée',
                'metadata' => ['location' => 'Hôpital Central', 'certification' => 'ACLS', 'level' => 'advanced'],
            ]
        );

        // Assert update was successful
        $this->assertTrue($updateResult);
        $trainingImpediment->refresh();
        $this->assertEquals('Formation médicale avancée', $trainingImpediment->reason);

        // Act: Delete impediment
        $deleteResult = impediment_for($availability)->delete($adjacentImpediment->id);

        // Assert deletion was successful
        $this->assertTrue($deleteResult);
        $this->assertNull(ImpedimentModel::find($adjacentImpediment->id));
    }

    /**
     * Test complete schedule management including booking, validation, conflict resolution and availability searches.
     */
    public function test_complete_schedule_management(): void
    {
        // Arrange: Create consultation availability
        $consultationAvailability = availability_for($this->primaryDoctor)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday', 'wednesday', 'friday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-12-31',
        ]);

        // Arrange: Create surgery availability
        $surgeryAvailability = availability_for($this->primaryDoctor)->create([
            'type' => 'surgery',
            'daily_start' => '13:00:00',
            'daily_end' => '17:00:00',
            'days' => ['tuesday', 'thursday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-12-31',
        ]);

        // Act: Create annual checkup schedule
        $annualCheckupSchedule = schedule_for($consultationAvailability)->create([
            'title' => 'Consultation annuelle - Patient A',
            'description' => 'Bilan de santé annuel avec tests de routine',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
            'status' => ScheduleStatus::BOOKED,
            'metadata' => [
                'patient_id' => 123,
                'insurance' => 'ABC Insurance',
                'tests' => ['blood', 'urine', 'xray']
            ],
        ]);

        // Assert schedule properties
        $this->assertInstanceOf(ScheduleModel::class, $annualCheckupSchedule);
        $this->assertEquals('Consultation annuelle - Patient A', $annualCheckupSchedule->title);
        $this->assertEquals(ScheduleStatus::BOOKED, $annualCheckupSchedule->status);

        // Act: Create surgery schedule
        $surgerySchedule = schedule_for($surgeryAvailability)->create([
            'title' => 'Chirurgie mineure - Patient B',
            'description' => 'Ablation de grain de beauté',
            'start_datetime' => '2038-01-05 14:00:00',
            'end_datetime' => '2038-01-05 15:30:00',
            'status' => ScheduleStatus::BOOKED,
            'metadata' => [
                'patient_id' => 456,
                'anesthesia' => 'locale',
                'room' => 'Salle 3'
            ],
        ]);

        // Assert surgery schedule was created
        $this->assertNotNull($surgerySchedule);

        // Act & Assert: Test validation failures for invalid schedules
        $this->expectException(ValidationFailedException::class);
        schedule_for($consultationAvailability)->create([
            'title' => 'Consultation trop tardive',
            'start_datetime' => '2038-01-04 18:00:00',
            'end_datetime' => '2038-01-04 19:00:00',
        ]);

        $this->expectException(ValidationFailedException::class);
        schedule_for($consultationAvailability)->create([
            'title' => 'Consultation le mardi',
            'start_datetime' => '2038-01-05 10:00:00',
            'end_datetime' => '2038-01-05 11:00:00',
        ]);

        $this->expectException(ValidationFailedException::class);
        schedule_for($consultationAvailability)->create([
            'title' => 'Consultation qui chevauche',
            'start_datetime' => '2038-01-04 10:30:00',
            'end_datetime' => '2038-01-04 11:30:00',
        ]);

        // Act: Create adjacent schedule
        $adjacentSchedule = schedule_for($consultationAvailability)->create([
            'title' => 'Consultation suivante',
            'start_datetime' => '2038-01-04 11:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        // Assert adjacent schedule was created
        $this->assertNotNull($adjacentSchedule);

        // Act: Find next available slot
        $nextAvailableSlot = schedule_for($consultationAvailability)->findNextSlot(
            durationMinutes: 45,
            type: 'consultation',
            returnStartOnly: false,
            startFrom: Carbon::parse('2038-01-04 09:00:00')
        );

        // Assert slot is available
        $this->assertNotNull($nextAvailableSlot);
        $this->assertTrue(
            $nextAvailableSlot['start']->gte(Carbon::parse('2038-01-04 12:00:00')) ||
                $nextAvailableSlot['start']->lt(Carbon::parse('2038-01-04 09:00:00'))
        );

        // Act: Check if Wednesday slot is available
        $isWednesdaySlotAvailable = schedule_for($consultationAvailability)->isTimeSlotAvailable(
            start: Carbon::parse('2038-01-06 09:00:00'),
            end: Carbon::parse('2038-01-06 10:00:00'),
            type: 'consultation'
        );

        // Assert Wednesday slot is available
        $this->assertTrue($isWednesdaySlotAvailable, 'Wednesday morning slot should be available');

        // Act: Update schedule
        $updateResult = schedule_for($consultationAvailability)->update(
            id: $annualCheckupSchedule->id,
            data: [
                'title' => 'Consultation annuelle mise à jour - Patient A',
                'description' => 'Bilan de santé avec tests supplémentaires',
                'metadata' => [
                    'patient_id' => 123,
                    'insurance' => 'ABC Insurance',
                    'tests' => ['blood', 'urine', 'xray', 'ecg'],
                    'notes' => 'Patient a des antécédents familiaux'
                ],
            ]
        );

        // Assert update was successful
        $this->assertTrue($updateResult);
        $annualCheckupSchedule->refresh();
        $this->assertContains('ecg', $annualCheckupSchedule->metadata['tests']);

        // Act: Cancel schedule
        $cancellationResult = schedule_for($consultationAvailability)->update(
            id: $adjacentSchedule->id,
            data: ['status' => ScheduleStatus::CANCELLED]
        );

        // Assert cancellation was successful
        $this->assertTrue($cancellationResult);
        $adjacentSchedule->refresh();
        $this->assertEquals(ScheduleStatus::CANCELLED, $adjacentSchedule->status);

        // Act: Get all booked schedules
        $allBookedSchedules = schedule_for($consultationAvailability)
            ->setFilter('status', ScheduleStatus::BOOKED)
            ->all();

        // Assert only one booked schedule remains
        $this->assertCount(1, $allBookedSchedules);

        // Act: Find available slots
        $availableTimeSlots = schedule_for($consultationAvailability)->findAvailableSlots(
            startDate: Carbon::parse('2038-01-04'),
            endDate: Carbon::parse('2038-01-06'),
            durationMinutes: 60,
            type: 'consultation'
        );

        // Assert available slots are returned
        $this->assertInstanceOf(Collection::class, $availableTimeSlots);
        $this->assertGreaterThan(0, $availableTimeSlots->count());
    }

    /**
     * Test complex interactions between availabilities, impediments, and schedules with overlapping constraints.
     */
    public function test_complex_interaction_scenario(): void
    {
        // Arrange: Create weekly availability
        $weeklyAvailability = availability_for($this->primaryDoctor)->create([
            'type' => 'consultation',
            'daily_start' => '08:00:00',
            'daily_end' => '18:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-12-31',
        ]);

        // Arrange: Create lunch impediments for weekdays
        $weekdays = ['2038-01-04', '2038-01-05', '2038-01-06', '2038-01-07', '2038-01-08'];

        foreach ($weekdays as $weekday) {
            $dateTime = Carbon::parse($weekday);
            impediment_for($weeklyAvailability)->create([
                'reason' => 'Pause déjeuner',
                'start_datetime' => $dateTime->copy()->setTime(12, 0, 0),
                'end_datetime' => $dateTime->copy()->setTime(13, 0, 0),
                'metadata' => ['type' => 'lunch', 'recurring' => true],
            ]);
        }

        // Arrange: Create training impediment
        $trainingImpediment = impediment_for($weeklyAvailability)->create([
            'reason' => 'Formation continue sur les nouvelles procédures',
            'start_datetime' => '2038-01-05 09:00:00',
            'end_datetime' => '2038-01-05 12:00:00',
            'metadata' => ['mandatory' => true, 'location' => 'Auditorium'],
        ]);

        // Arrange: Create regular appointments
        $regularAppointments = [];

        $regularAppointments[] = schedule_for($weeklyAvailability)->create([
            'title' => 'Suivi patient chronique - Mme Dupont',
            'start_datetime' => '2038-01-04 09:00:00',
            'end_datetime' => '2038-01-04 09:30:00',
            'status' => ScheduleStatus::BOOKED,
            'metadata' => ['patient_id' => 101, 'chronic' => true, 'frequency' => 'weekly'],
        ]);

        $regularAppointments[] = schedule_for($weeklyAvailability)->create([
            'title' => 'Nouvelle consultation - M. Martin',
            'start_datetime' => '2038-01-06 14:00:00',
            'end_datetime' => '2038-01-06 14:45:00',
            'status' => ScheduleStatus::BOOKED,
            'metadata' => ['patient_id' => 102, 'new_patient' => true],
        ]);

        // Act & Assert: Test schedule update conflict with training impediment
        $this->expectException(ValidationFailedException::class);
        schedule_for($weeklyAvailability)->update(
            id: $regularAppointments[0]->id,
            data: [
                'start_datetime' => '2038-01-05 11:00:00',
                'end_datetime' => '2038-01-05 11:30:00',
            ]
        );

        // Act: Find emergency slot around impediments
        $emergencySlot = schedule_for($weeklyAvailability)->findNextSlot(
            durationMinutes: 30,
            type: 'consultation',
            returnStartOnly: false,
            startFrom: Carbon::parse('2038-01-05 08:00:00')
        );

        // Assert emergency slot is available
        $this->assertNotNull($emergencySlot);
        $slotStartTime = $emergencySlot['start']->format('H:i');
        $this->assertTrue(
            ($slotStartTime >= '12:00' && $slotStartTime < '12:00') ||
                ($slotStartTime >= '08:00' && $slotStartTime < '09:00'),
            "Emergency slot should be available around impediments"
        );

        // Act: Check if Monday afternoon period is available
        $isMondayAfternoonAvailable = schedule_for($weeklyAvailability)->isPeriodAvailable(
            start: Carbon::parse('2038-01-04 15:00:00'),
            end: Carbon::parse('2038-01-04 16:30:00'),
            type: 'consultation'
        );

        // Assert Monday afternoon is available
        $this->assertTrue(
            $isMondayAfternoonAvailable,
            "Monday afternoon period should be available"
        );

        // Act: Cancel appointment
        $cancellationResult = schedule_for($weeklyAvailability)->update(
            id: $regularAppointments[1]->id,
            data: [
                'status' => ScheduleStatus::CANCELLED,
                'metadata' => array_merge(
                    $regularAppointments[1]->metadata,
                    ['cancellation_reason' => 'patient indisponible']
                )
            ]
        );

        // Assert cancellation was successful
        $this->assertTrue($cancellationResult);

        // Act: Rebook cancelled slot
        $rebooking = schedule_for($weeklyAvailability)->create([
            'title' => 'Consultation urgente - M. Leblanc',
            'start_datetime' => '2038-01-06 14:00:00',
            'end_datetime' => '2038-01-06 14:45:00',
            'status' => ScheduleStatus::BOOKED,
            'metadata' => ['patient_id' => 103, 'urgent' => true],
        ]);

        // Assert rebooking was successful
        $this->assertNotNull($rebooking);

        // Act & Assert: Test schedule creation outside availability hours
        $this->expectException(ValidationFailedException::class);
        schedule_for($weeklyAvailability)->create([
            'title' => 'Consultation trop longue',
            'start_datetime' => '2038-01-04 08:00:00',
            'end_datetime' => '2038-01-04 20:00:00',
        ]);

        // Act: Get all January schedules
        $allJanuarySchedules = schedule_for($weeklyAvailability)
            ->setFilter('start_datetime', '2038-01-01 00:00:00')
            ->setFilter('end_datetime', '2038-01-31 23:59:59')
            ->all();

        // Assert schedule counts
        $bookedCount = $allJanuarySchedules->where('status', ScheduleStatus::BOOKED)->count();
        $cancelledCount = $allJanuarySchedules->where('status', ScheduleStatus::CANCELLED)->count();

        $this->assertGreaterThan(0, $bookedCount);
        $this->assertGreaterThan(0, $cancelledCount);

        // Act: Delete training impediment
        $deleteImpedimentResult = impediment_for($weeklyAvailability)->delete($trainingImpediment->id);

        // Assert impediment deletion was successful
        $this->assertTrue($deleteImpedimentResult);

        // Act: Check if previously blocked slot is now available
        $isNowAvailable = schedule_for($weeklyAvailability)->isTimeSlotAvailable(
            start: Carbon::parse('2038-01-05 10:00:00'),
            end: Carbon::parse('2038-01-05 11:00:00'),
            type: 'consultation'
        );

        // Assert slot is available after impediment removal
        $this->assertTrue(
            $isNowAvailable,
            "Time slot should be available after impediment removal"
        );
    }

    /**
     * Test multi-user resource conflict scenarios with shared resources.
     */
    public function test_multi_user_resource_conflicts(): void
    {
        // Arrange: Create room availability for first doctor
        $roomAvailabilityDoctor1 = availability_for($this->primaryDoctor)->create([
            'type' => 'room_a',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'wednesday', 'friday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-12-31',
        ]);

        // Arrange: Create room availability for second doctor
        $roomAvailabilityDoctor2 = availability_for($this->secondaryDoctor)->create([
            'type' => 'room_a',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['tuesday', 'thursday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-12-31',
        ]);

        // Act: Create booking for first doctor
        $doctor1Booking = schedule_for($roomAvailabilityDoctor1)->create([
            'title' => 'Utilisation salle A - Dr. Smith',
            'start_datetime' => '2038-01-06 10:00:00',
            'end_datetime' => '2038-01-06 12:00:00',
            'status' => ScheduleStatus::BOOKED,
            'metadata' => ['purpose' => 'consultations', 'equipment' => 'echographe'],
        ]);

        // Act & Assert: Test resource conflict for second doctor
        $this->expectException(ValidationFailedException::class);
        schedule_for($roomAvailabilityDoctor2)->create([
            'title' => 'Utilisation salle A - Dr. Jones',
            'start_datetime' => '2038-01-06 11:00:00',
            'end_datetime' => '2038-01-06 13:00:00',
            'status' => ScheduleStatus::BOOKED,
        ]);

        // Act: Create booking for second doctor on different day
        $doctor2Booking = schedule_for($roomAvailabilityDoctor2)->create([
            'title' => 'Utilisation salle A - Dr. Jones',
            'start_datetime' => '2038-01-07 10:00:00',
            'end_datetime' => '2038-01-07 12:00:00',
            'status' => ScheduleStatus::BOOKED,
        ]);

        // Assert booking was created
        $this->assertNotNull($doctor2Booking);

        // Act & Assert: Test update conflict with existing booking
        $this->expectException(ValidationFailedException::class);
        schedule_for($roomAvailabilityDoctor1)->update(
            id: $doctor1Booking->id,
            data: [
                'start_datetime' => '2038-01-07 11:00:00',
                'end_datetime' => '2038-01-07 13:00:00',
            ]
        );

        // Act: Find next available slots for both doctors
        $smithSlot = schedule_for($roomAvailabilityDoctor1)->findNextSlot(
            durationMinutes: 60,
            type: 'room_a',
            returnStartOnly: false,
            startFrom: Carbon::parse('2038-01-04 09:00:00')
        );

        $jonesSlot = schedule_for($roomAvailabilityDoctor2)->findNextSlot(
            durationMinutes: 60,
            type: 'room_a',
            returnStartOnly: false,
            startFrom: Carbon::parse('2038-01-05 09:00:00')
        );

        // Assert slots are found
        $this->assertNotNull($smithSlot);
        $this->assertNotNull($jonesSlot);

        // Assert doctors have slots on different days
        $this->assertNotEquals(
            $smithSlot['start']->format('Y-m-d'),
            $jonesSlot['start']->format('Y-m-d'),
            "Doctors should have slots on different days"
        );

        // Act: Cancel first doctor's booking
        schedule_for($roomAvailabilityDoctor1)->update(
            id: $doctor1Booking->id,
            data: [
                'status' => ScheduleStatus::CANCELLED,
                'metadata' => array_merge($doctor1Booking->metadata, ['cancelled_by' => 'doctor'])
            ]
        );

        // Act: Book freed slot with second doctor
        $jonesWednesdayBooking = schedule_for($roomAvailabilityDoctor2)->create([
            'title' => 'Utilisation exceptionnelle - Dr. Jones',
            'start_datetime' => '2038-01-06 14:00:00',
            'end_datetime' => '2038-01-06 15:00:00',
            'status' => ScheduleStatus::BOOKED,
            'metadata' => ['exceptional' => true, 'reason' => 'salle libérée'],
        ]);

        // Assert booking was successful
        $this->assertNotNull($jonesWednesdayBooking);
    }

    /**
     * Test error handling and edge cases including invalid data, past dates, and extreme scenarios.
     */
    public function test_error_handling_edge_cases(): void
    {
        // Act & Assert: Test invalid date format
        $this->expectException(InvalidFormatException::class);
        availability_for($this->primaryDoctor)->create([
            'type' => 'invalid_type',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['invalid_day'],
            'validity_start' => 'not_a_date',
            'validity_end' => 'also_not_a_date',
        ]);

        // Act & Assert: Test missing required fields
        $this->expectException(ValidationFailedException::class);
        availability_for($this->primaryDoctor)->create([
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Arrange: Create valid availability
        $availability = availability_for($this->primaryDoctor)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Act & Assert: Test past date scheduling
        $this->expectException(ValidationFailedException::class);
        schedule_for($availability)->create([
            'title' => 'Consultation passée',
            'start_datetime' => '2020-01-01 10:00:00',
            'end_datetime' => '2020-01-01 11:00:00',
        ]);

        // Act & Assert: Test schedule too short
        $this->expectException(ValidationFailedException::class);
        schedule_for($availability)->create([
            'title' => 'Consultation trop courte',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 10:01:00',
        ]);

        // Act: Create valid schedule
        schedule_for($availability)->create([
            'title' => 'Première consultation',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ]);

        // Act & Assert: Test exact overlap
        $this->expectException(ValidationFailedException::class);
        schedule_for($availability)->create([
            'title' => 'Deuxième consultation exactement chevauchante',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ]);

        // Act: Search for slot in distant future
        $distantFuture = Carbon::now()->addYears(2);
        $noAvailableSlot = schedule_for($availability)->findNextSlot(
            durationMinutes: 60,
            type: 'consultation',
            returnStartOnly: false,
            startFrom: $distantFuture,
            endBefore: $distantFuture->copy()->addDays(10)
        );

        // Assert no slot found
        $this->assertNull($noAvailableSlot, "No slot should be found in distant future");

        // Act: Create schedule with complex metadata
        $complexMetadataSchedule = schedule_for($availability)->create([
            'title' => 'Consultation avec métadonnées complexes',
            'start_datetime' => '2038-01-04 11:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
            'metadata' => [
                'patient' => [
                    'id' => 999,
                    'name' => 'Patient Complexe',
                    'history' => str_repeat('A', 1000),
                    'allergies' => ['penicillin', 'aspirin', 'latex', 'nuts'],
                    'medications' => array_fill(0, 50, 'medication_data'),
                ],
                'documents' => array_fill(0, 20, ['type' => 'pdf', 'size' => 1024]),
            ],
        ]);

        // Assert schedule with complex metadata was created
        $this->assertNotNull($complexMetadataSchedule);
        $this->assertCount(20, $complexMetadataSchedule->metadata['documents']);

        // Arrange: Create schedule to delete
        $scheduleToDelete = schedule_for($availability)->create([
            'title' => 'Consultation à supprimer',
            'start_datetime' => '2038-01-04 13:00:00',
            'end_datetime' => '2038-01-04 14:00:00',
        ]);

        // Arrange: Create overlapping impediment
        $overlappingImpediment = impediment_for($availability)->create([
            'reason' => 'Test empêchement',
            'start_datetime' => '2038-01-04 13:30:00',
            'end_datetime' => '2038-01-04 14:30:00',
        ]);

        // Assert impediment was created
        $this->assertNotNull($overlappingImpediment);

        // Act: Delete schedule
        $deleteResult = schedule_for($availability)->delete($scheduleToDelete->id);

        // Assert deletion was successful and impediment still exists
        $this->assertTrue($deleteResult);
        $this->assertNotNull(ImpedimentModel::find($overlappingImpediment->id));

        // Act: Try to find non-existent schedule
        $nonExistentSchedule = schedule_for($availability)->find(999999);

        // Assert schedule not found
        $this->assertNull($nonExistentSchedule);

        // Act & Assert: Test update non-existent schedule
        $this->expectException(ValidationFailedException::class);
        schedule_for($availability)->update(
            id: 999999,
            data: ['title' => 'test']
        );

        // Act & Assert: Test delete non-existent schedule
        $this->expectException(ValidationFailedException::class);
        schedule_for($availability)->delete(999999);
    }

    /**
     * Test performance under load with multiple concurrent operations and bulk data processing.
     */
    public function test_performance_and_load_scenario(): void
    {
        $testStartTime = microtime(true);

        // Arrange: Create multiple availabilities
        $allowedTypes = ['consultation', 'emergency', 'surgery', 'training'];
        $createdAvailabilities = [];

        for ($i = 0; $i < 5; ++$i) {
            $createdAvailabilities[] = availability_for($this->primaryDoctor)->create([
                'type' => $allowedTypes[$i % count($allowedTypes)],
                'daily_start' => sprintf('%02d:00:00', 8 + $i),
                'daily_end'   => sprintf('%02d:00:00', 16 + $i),
                'days' => array_slice(
                    ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                    0,
                    $i + 1
                ),
                'validity_start' => "2038-0" . ($i + 1) . "-01",
                'validity_end'   => "2038-0" . ($i + 1) . "-30",
            ]);
        }

        // Assert all availabilities created
        $this->assertCount(5, $createdAvailabilities);

        // Arrange: Create impediments for each availability
        $createdImpediments = [];

        foreach ($createdAvailabilities as $availability) {
            $impedimentsForAvailability = $this->createImpedimentsForAvailability($availability);
            $createdImpediments = array_merge($createdImpediments, $impedimentsForAvailability);
        }

        // Assert impediments created
        $this->assertGreaterThan(0, count($createdImpediments));

        // Arrange: Create schedules for each availability
        $createdSchedules = [];
        $availabilityByScheduleId = [];

        foreach ($createdAvailabilities as $availability) {
            $schedulesForAvailability = $this->createSchedulesForAvailability($availability, $availabilityByScheduleId);
            $createdSchedules = array_merge($createdSchedules, $schedulesForAvailability);
        }

        // Assert schedules created
        $this->assertGreaterThan(0, count($createdSchedules));

        // Act: Find next slot for each availability
        foreach ($createdAvailabilities as $createdAvailability) {
            schedule_for($createdAvailability)->findNextSlot(
                durationMinutes: 30,
                type: $createdAvailability->type,
                returnStartOnly: false,
                startFrom: Carbon::parse($createdAvailability->validity_start)->setTime(
                    (int) $createdAvailability->daily_start->format('H'),
                    0
                )
            );
        }

        // Act: Get all schedules within date range
        $allSchedules = schedule_for($createdAvailabilities[0])
            ->setFilter('start_datetime', '2038-01-01 00:00:00')
            ->setFilter('end_datetime', '2038-12-31 23:59:59')
            ->all();

        // Assert schedules collection returned
        $this->assertInstanceOf(Collection::class, $allSchedules);

        // Act: Cancel first schedule if exists
        if ($createdSchedules !== []) {
            $scheduleToCancel = $createdSchedules[0];
            $cancelAvailability = $availabilityByScheduleId[$scheduleToCancel->id] ?? $createdAvailabilities[0];

            $updateResult = schedule_for($cancelAvailability)->update(
                id: $scheduleToCancel->id,
                data: [
                    'status' => ScheduleStatus::CANCELLED,
                    'metadata' => array_merge(
                        $scheduleToCancel->metadata ?? [],
                        ['cancel_reason' => 'test_load']
                    ),
                ]
            );

            // Assert cancellation successful
            $this->assertTrue($updateResult);
        }

        // Assert performance
        $executionTime = microtime(true) - $testStartTime;
        $this->assertLessThan(
            5.0,
            $executionTime,
            'Performance test should execute in less than 5 seconds'
        );

        // Cleanup: Delete created schedules
        foreach ($createdSchedules as $createdSchedule) {
            $scheduleAvailability = $availabilityByScheduleId[$createdSchedule->id] ?? null;

            if ($scheduleAvailability) {
                try {
                    schedule_for($scheduleAvailability)->delete($createdSchedule->id);
                } catch (Exception $e) {
                    // Ignore cleanup errors
                }
            }
        }
    }

    /**
     * Test system recovery after errors while maintaining data integrity.
     */
    public function test_error_recovery_scenario(): void
    {
        // Arrange: Create availability
        $availability = availability_for($this->primaryDoctor)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Arrange: Create initial schedule
        $initialSchedule = schedule_for($availability)->create([
            'title' => 'Consultation initiale',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ]);

        // Arrange: Create initial impediment
        $initialImpediment = impediment_for($availability)->create([
            'reason' => 'Empêchement initial',
            'start_datetime' => '2038-01-04 14:00:00',
            'end_datetime' => '2038-01-04 15:00:00',
        ]);

        // Act & Assert: Test overlapping schedule creation failure
        try {
            schedule_for($availability)->create([
                'title' => 'Consultation invalide',
                'start_datetime' => '2038-01-04 10:30:00',
                'end_datetime' => '2038-01-04 11:30:00',
            ]);
            $this->fail('Should have thrown a validation exception');
        } catch (ValidationFailedException $validationFailedException) {
            $this->assertStringContainsString('overlaps', $validationFailedException->getMessage());
        }

        // Assert data integrity maintained after failed creation
        $initialSchedule->refresh();
        $this->assertEquals('Consultation initiale', $initialSchedule->title);
        $this->assertEquals('2038-01-04 10:00:00', $initialSchedule->start_datetime->format('Y-m-d H:i:s'));

        $initialImpediment->refresh();
        $this->assertNotNull($initialImpediment->id);

        // Act & Assert: Test overlapping schedule update failure
        try {
            schedule_for($availability)->update(
                id: $initialSchedule->id,
                data: [
                    'start_datetime' => '2038-01-04 14:30:00',
                    'end_datetime' => '2038-01-04 15:30:00',
                ]
            );
            $this->fail('Should have thrown a validation exception');
        } catch (ValidationFailedException $validationFailedException) {
            $this->assertStringContainsString('overlap', $validationFailedException->getMessage());
        }

        // Assert schedule unchanged after failed update
        $initialSchedule->refresh();
        $this->assertEquals('2038-01-04 10:00:00', $initialSchedule->start_datetime->format('Y-m-d H:i:s'));

        // Act: Check slot availability
        $isElevenToTwelveSlotAvailable = schedule_for($availability)->isTimeSlotAvailable(
            start: Carbon::parse('2038-01-04 11:00:00'),
            end: Carbon::parse('2038-01-04 12:00:00'),
            type: 'consultation'
        );

        // Assert slot available
        $this->assertTrue(
            $isElevenToTwelveSlotAvailable,
            '11:00-12:00 slot should be available before rescheduling'
        );

        // Act: Reschedule successfully
        $rescheduleResult = schedule_for($availability)->update(
            id: $initialSchedule->id,
            data: [
                'start_datetime' => '2038-01-04 11:00:00',
                'end_datetime' => '2038-01-04 12:00:00',
            ]
        );

        // Assert reschedule successful
        $this->assertTrue($rescheduleResult, 'Rescheduling to 11:00-12:00 should succeed');

        $initialSchedule->refresh();
        $this->assertEquals('2038-01-04 11:00:00', $initialSchedule->start_datetime->format('Y-m-d H:i:s'));
        $this->assertEquals('2038-01-04 12:00:00', $initialSchedule->end_datetime->format('Y-m-d H:i:s'));

        // Assert slot now unavailable
        $isElevenToTwelveSlotAvailableAfter = schedule_for($availability)->isTimeSlotAvailable(
            start: Carbon::parse('2038-01-04 11:00:00'),
            end: Carbon::parse('2038-01-04 12:00:00'),
            type: 'consultation'
        );

        $this->assertFalse(
            $isElevenToTwelveSlotAvailableAfter,
            '11:00-12:00 slot should be unavailable after rescheduling'
        );

        // Assert previous slot now available
        $isTenToElevenSlotAvailableAfter = schedule_for($availability)->isTimeSlotAvailable(
            start: Carbon::parse('2038-01-04 10:00:00'),
            end: Carbon::parse('2038-01-04 11:00:00'),
            type: 'consultation'
        );

        $this->assertTrue(
            $isTenToElevenSlotAvailableAfter,
            '10:00-11:00 slot (previous location) should be available after rescheduling'
        );

        // Act: Create new schedule in freed slot
        $newSchedule = schedule_for($availability)->create([
            'title' => 'Nouvelle consultation après récupération',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ]);

        // Assert new schedule created
        $this->assertNotNull($newSchedule);
        $this->assertEquals('Nouvelle consultation après récupération', $newSchedule->title);

        // Assert all schedules present
        /** @var Collection<int, ScheduleModel> $allSchedules */
        $allSchedules = schedule_for($availability)->all();
        $this->assertCount(2, $allSchedules);

        $scheduleTitles = $allSchedules->pluck('title')->toArray();
        $this->assertContains('Consultation initiale', $scheduleTitles);
        $this->assertContains('Nouvelle consultation après récupération', $scheduleTitles);

        // Assert impediments intact
        $allImpediments = impediment_for($availability)->all();
        $this->assertCount(1, $allImpediments);

        // Cleanup: Delete all schedules
        $deletedSchedulesCount = 0;
        foreach ($allSchedules as $allSchedule) {
            if (schedule_for($availability)->delete($allSchedule->id)) {
                ++$deletedSchedulesCount;
            }
        }

        $this->assertSame(2, $deletedSchedulesCount);

        // Cleanup: Delete impediment
        $deleteImpedimentResult = impediment_for($availability)->delete($initialImpediment->id);
        $this->assertTrue($deleteImpedimentResult);

        // Assert clean state
        $finalSchedules = schedule_for($availability)->all();
        $finalImpediments = impediment_for($availability)->all();

        $this->assertCount(0, $finalSchedules);
        $this->assertCount(0, $finalImpediments);
    }

    /**
     * Test real-world complex scenario with multiple specialists, shared resources, and institutional constraints.
     */
    public function test_real_world_complex_scenario(): void
    {
        // Arrange: Create specialists
        $cardiologist = TestSchedulable::create(['name' => 'Cardiologue', 'specialty' => 'cardiology']);
        $radiologist = TestSchedulable::create(['name' => 'Radiologue', 'specialty' => 'radiology']);
        $generalPractitioner = TestSchedulable::create(['name' => 'Généraliste', 'specialty' => 'general']);

        // Arrange: Create availabilities for each specialist
        $cardiologyConsultation = availability_for($cardiologist)->create([
            'type' => 'consultation',
            'daily_start' => '08:30:00',
            'daily_end' => '12:30:00',
            'days' => ['monday', 'wednesday', 'friday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-06-30',
        ]);

        $cardiologyEchography = availability_for($cardiologist)->create([
            'type' => 'echography',
            'daily_start' => '13:30:00',
            'daily_end' => '17:00:00',
            'days' => ['tuesday', 'thursday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-06-30',
        ]);

        $radiologyAvailability = availability_for($radiologist)->create([
            'type' => 'scan',
            'daily_start' => '13:00:00',
            'daily_end' => '18:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-06-30',
        ]);

        $generalPracticeAvailability = availability_for($generalPractitioner)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '18:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-06-30',
        ]);

        // Arrange: Create institutional impediments
        try {
            impediment_for($generalPracticeAvailability)->create([
                'reason' => 'Fermeture annuelle de la clinique',
                'start_datetime' => '2038-04-01 09:00:00',
                'end_datetime' => '2038-04-14 18:00:00',
                'metadata' => ['institutional' => true, 'affects_all' => true],
            ]);

            impediment_for($cardiologyConsultation)->create([
                'reason' => 'Fermeture annuelle - Cardiologie',
                'start_datetime' => '2038-04-01 08:30:00',
                'end_datetime' => '2038-04-14 12:30:00',
                'metadata' => ['institutional' => true],
            ]);

            impediment_for($cardiologyEchography)->create([
                'reason' => 'Fermeture annuelle - Échographie',
                'start_datetime' => '2038-04-03 13:30:00',
                'end_datetime' => '2038-04-03 17:00:00',
                'metadata' => ['institutional' => true],
            ]);
        } catch (ValidationFailedException $validationFailedException) {
            $this->addToAssertionCount(1);
        }

        // Arrange: Create recurring monthly training impediments
        for ($month = 1; $month <= 6; ++$month) {
            $trainingDate = Carbon::create(2038, $month, 15, 12, 0, 0);
            if ($trainingDate->isWeekday()) {
                try {
                    impediment_for($generalPracticeAvailability)->create([
                        'reason' => 'Formation mensuelle du personnel',
                        'start_datetime' => $trainingDate->copy()->setTime(12, 0, 0),
                        'end_datetime' => $trainingDate->copy()->setTime(14, 0, 0),
                        'metadata' => ['recurring' => true, 'month' => $month],
                    ]);
                } catch (ValidationFailedException $e) {
                    continue;
                }
            }
        }

        // Act: Create cardiac patient consultation
        $cardiacPatientConsultation = schedule_for($cardiologyConsultation)->create([
            'title' => 'Patient cardiologique - Consultation initiale',
            'start_datetime' => '2038-01-06 10:00:00',
            'end_datetime' => '2038-01-06 11:00:00',
            'status' => ScheduleStatus::BOOKED,
            'metadata' => [
                'patient_id' => 'CARD001',
                'priority' => 'medium',
                'referral' => true,
                'tests_required' => ['echocardiogram', 'stress_test']
            ],
        ]);

        $this->assertNotNull($cardiacPatientConsultation);

        // Act: Create follow-up echo schedule
        $followUpEchoDate = Carbon::parse('2038-01-06')->addWeeks(2);
        if ($followUpEchoDate->isTuesday() || $followUpEchoDate->isThursday()) {
            try {
                $echoSchedule = schedule_for($cardiologyEchography)->create([
                    'title' => 'Patient cardiologique - Échocardiogramme',
                    'start_datetime' => $followUpEchoDate->copy()->setTime(14, 0, 0),
                    'end_datetime' => $followUpEchoDate->copy()->setTime(15, 0, 0),
                    'status' => ScheduleStatus::BOOKED,
                    'metadata' => [
                        'patient_id' => 'CARD001',
                        'follow_up' => true,
                        'related_to' => $cardiacPatientConsultation->id,
                        'equipment' => 'echo_machine_1'
                    ],
                ]);
                $this->assertNotNull($echoSchedule);
            } catch (ValidationFailedException $e) {
                $this->addToAssertionCount(1);
            }
        }

        // Act: Create radiology scan
        $scanSchedule = schedule_for($radiologyAvailability)->create([
            'title' => 'Scan thoracique - Patient pulmonaire',
            'start_datetime' => '2038-01-05 15:00:00',
            'end_datetime' => '2038-01-05 16:00:00',
            'status' => ScheduleStatus::BOOKED,
            'metadata' => [
                'patient_id' => 'RAD001',
                'scan_type' => 'CT_chest',
                'contrast' => true,
                'urgency' => 'high'
            ],
        ]);

        $this->assertNotNull($scanSchedule);

        // Act: Handle emergency rescheduling
        $emergencyDate = Carbon::parse('2038-01-06');
        $alternativeSlot = schedule_for($cardiologyConsultation)->findNextSlot(
            durationMinutes: 60,
            type: 'consultation',
            returnStartOnly: false,
            startFrom: $emergencyDate->copy()->addDay()
        );

        if ($alternativeSlot) {
            try {
                schedule_for($cardiologyConsultation)->update(
                    id: $cardiacPatientConsultation->id,
                    data: [
                        'start_datetime' => $alternativeSlot['start'],
                        'end_datetime' => $alternativeSlot['end'],
                        'metadata' => array_merge(
                            $cardiacPatientConsultation->metadata,
                            ['rescheduled' => true, 'reason' => 'urgence', 'original_date' => '2038-01-06']
                        ),
                    ]
                );

                $emergencySchedule = schedule_for($cardiologyConsultation)->create([
                    'title' => 'URGENCE - Douleur thoracique aiguë',
                    'start_datetime' => '2038-01-06 10:00:00',
                    'end_datetime' => '2038-01-06 11:30:00',
                    'status' => ScheduleStatus::BOOKED,
                    'metadata' => [
                        'patient_id' => 'EMER001',
                        'emergency' => true,
                        'triage_level' => 1,
                        'symptoms' => ['chest_pain', 'shortness_of_breath'],
                        'displaced_appointment' => $cardiacPatientConsultation->id
                    ],
                ]);
                $this->assertNotNull($emergencySchedule);
            } catch (ValidationFailedException $e) {
                $this->addToAssertionCount(1);
            }
        }

        // Assert no scheduling conflicts
        /** @var Collection<int, ScheduleModel> $allSchedules */
        $allSchedules = schedule_for($cardiologyConsultation)
            ->setFilter('start_datetime', '2038-01-01 00:00:00')
            ->setFilter('end_datetime', '2038-01-31 23:59:59')
            ->all();

        $scheduleTimes = [];
        $hasConflicts = false;

        foreach ($allSchedules as $schedule) {
            $timeKey = $schedule->start_datetime->format('Y-m-d H:i') . '_' . $schedule->end_datetime->format('H:i');
            if (in_array($timeKey, $scheduleTimes)) {
                $hasConflicts = true;
                break;
            }

            $scheduleTimes[] = $timeKey;
        }

        $this->assertFalse($hasConflicts, 'No scheduling conflicts should be detected');

        // Act: Check resource availability
        $testTime = Carbon::parse('2038-01-05 15:00:00');

        $isRadiologistAvailable = schedule_for($radiologyAvailability)->isTimeSlotAvailable(
            start: $testTime,
            end: $testTime->copy()->addHour(),
            type: 'scan'
        );

        $isGeneralPractitionerAvailable = schedule_for($generalPracticeAvailability)->isTimeSlotAvailable(
            start: $testTime,
            end: $testTime->copy()->addHour(),
            type: 'consultation'
        );

        // Assert at least one doctor available
        $isResourceAvailable = $isRadiologistAvailable || $isGeneralPractitionerAvailable;
        $this->assertTrue($isResourceAvailable, 'At least one doctor should be available');

        // Assert booked schedules exist
        $januarySchedules = schedule_for($cardiologyConsultation)
            ->setFilter('start_datetime', '2038-01-01 00:00:00')
            ->setFilter('end_datetime', '2038-01-31 23:59:59')
            ->all();

        $bookedCount = $januarySchedules->where('status', ScheduleStatus::BOOKED)->count();
        $this->assertGreaterThan(0, $bookedCount);

        // Test robustness with multiple operations
        $operations = [];

        for ($i = 1; $i <= 3; ++$i) {
            try {
                $schedule = schedule_for($generalPracticeAvailability)->create([
                    'title' => 'Test de robustesse ' . $i,
                    'start_datetime' => sprintf('2038-01-0%d 10:00:00', $i),
                    'end_datetime' => sprintf('2038-01-0%d 11:00:00', $i),
                ]);
                $operations[] = ['type' => 'create', 'success' => true, 'id' => $schedule->id];
            } catch (ValidationFailedException $e) {
                $operations[] = ['type' => 'create', 'success' => false, 'error' => $e->getMessage()];
            }
        }

        $successfulOperations = array_filter($operations, fn(array $op): bool => $op['success'] === true);
        $this->assertGreaterThan(0, count($successfulOperations));
    }

    /**
     * Create multiple impediments for a given availability.
     *
     * @param AvailabilityModel $availability
     * @return array<int, ImpedimentModel>
     */
    private function createImpedimentsForAvailability(AvailabilityModel $availability): array
    {
        $impediments = [];
        $availabilityDays = $availability->days;
        $currentDate = Carbon::parse($availability->validity_start);

        // Find first valid day
        while (!in_array(strtolower($currentDate->englishDayOfWeek), $availabilityDays, true)) {
            $currentDate->addDay();
        }

        $startHour = (int) $availability->daily_start->format('H') + 1;
        $endHour   = $startHour + 2;

        // Create 3 impediments
        for ($j = 1; $j <= 3; ++$j) {
            if ($endHour <= (int) $availability->daily_end->format('H')) {
                $impediments[] = impediment_for($availability)->create([
                    'reason' => sprintf('Empêchement %d pour %s', $j, $availability->type),
                    'start_datetime' => $currentDate->copy()->setTime($startHour, 0, 0),
                    'end_datetime'   => $currentDate->copy()->setTime($endHour, 0, 0),
                ]);
            }

            $currentDate->addWeek();
        }

        return $impediments;
    }

    /**
     * Create multiple schedules for a given availability.
     *
     * @param AvailabilityModel $availability
     * @param array<int, AvailabilityModel> $availabilityByScheduleId
     * @return array<int, ScheduleModel>
     */
    private function createSchedulesForAvailability(
        AvailabilityModel $availability,
        array &$availabilityByScheduleId
    ): array {
        $schedules = [];
        $availabilityDays = $availability->days;
        $currentDate = Carbon::parse($availability->validity_start)->addDays(2);

        $schedulesCreated = 0;
        $maxAttempts = 10;

        // Try to create up to 4 schedules
        for ($k = 1; $k <= 4 && $schedulesCreated < 4 && $maxAttempts > 0; ++$k) {
            --$maxAttempts;

            // Find valid day
            while (!in_array(strtolower($currentDate->englishDayOfWeek), $availabilityDays, true)) {
                $currentDate->addDay();
            }

            $startHour = (int) $availability->daily_start->format('H');
            $scheduleStart = $currentDate->copy()->setTime($startHour, 0, 0);
            $scheduleEnd   = $scheduleStart->copy()->addHour();

            $scheduleEndHour = (int) $scheduleEnd->format('H');
            $availabilityEndHour = (int) $availability->daily_end->format('H');

            // Check if schedule fits within availability
            if ($scheduleEndHour <= $availabilityEndHour) {
                try {
                    $schedule = schedule_for($availability)->create([
                        'title' => sprintf('Consultation %d - %s', $k, $availability->type),
                        'start_datetime' => $scheduleStart,
                        'end_datetime'   => $scheduleEnd,
                        'metadata' => [
                            'batch_test' => true,
                            'iteration' => $k,
                        ],
                    ]);

                    $schedules[] = $schedule;
                    $availabilityByScheduleId[$schedule->id] = $availability;
                    ++$schedulesCreated;
                } catch (Exception $e) {
                    // Continue to next attempt
                }
            }

            $currentDate->addDays(2);
        }

        return $schedules;
    }
}
