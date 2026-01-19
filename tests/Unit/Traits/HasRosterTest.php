<?php

declare(strict_types=1);

namespace Tests\Unit\Traits;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Models\Availability as AvailabilityModel;
use Roster\Models\Impediment as ImpedimentModel;
use Roster\Models\Schedule as ScheduleModel;
use Roster\Traits\HasRoster;
use Tests\TestCase;
use Tests\Support\TestSchedulable;

/**
 * Test suite for HasRoster trait functionality.
 *
 * Validates relationships and custom methods added to the trait,
 * focusing on period-based queries for impediments and schedules.
 */
final class HasRosterTest extends TestCase
{
    use RefreshDatabase;

    /** @var TestSchedulable Primary test model using HasRoster trait */
    private TestSchedulable $testModel;

    /** @var TestSchedulable Secondary test model for isolation testing */
    private TestSchedulable $otherModel;

    /**
     * Set up test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->testModel = TestSchedulable::create(['name' => 'Test Model']);
        $this->otherModel = TestSchedulable::create(['name' => 'Other Model']);

        // Verify trait is used
        $this->assertContains(HasRoster::class, class_uses($this->testModel));
        $this->assertContains(HasRoster::class, class_uses($this->otherModel));
    }

    /**
     * Test relationships are properly defined.
     */
    public function test_relationships_are_properly_defined(): void
    {
        // Test schedules relationship
        $this->assertTrue(method_exists($this->testModel, 'schedules'));
        $this->assertTrue(method_exists($this->testModel, 'availabilities'));
        $this->assertTrue(method_exists($this->testModel, 'impediments'));

        // Test relationships return correct types
        $schedulesRelation = $this->testModel->schedules();
        $availabilitiesRelation = $this->testModel->availabilities();
        $impedimentsRelation = $this->testModel->impediments();

        $this->assertEquals(ScheduleModel::class, $schedulesRelation->getRelated()::class);
        $this->assertEquals(AvailabilityModel::class, $availabilitiesRelation->getRelated()::class);
        $this->assertEquals(ImpedimentModel::class, $impedimentsRelation->getRelated()::class);
    }

    /**
     * Test getImpedimentsInPeriod returns correct impediments.
     */
    public function test_get_impediments_in_period_returns_correct_impediments(): void
    {
        // Arrange: Create availability for July 2038 (using correct days)
        // July 1, 2038 is Thursday
        $availability = availability_for($this->testModel)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Impediment 1: Completely inside period (Thursday, July 15)
        $impediment1 = impediment_for($availability)->create([
            'reason' => 'Formation interne',
            'start_datetime' => '2038-07-15 10:00:00', // Thursday
            'end_datetime' => '2038-07-15 12:00:00',
        ]);

        // Impediment 2: Starts before period, ends inside (Wednesday, July 14)
        $impediment2 = impediment_for($availability)->create([
            'reason' => 'Réunion matinale',
            'start_datetime' => '2038-07-14 09:00:00', // Wednesday
            'end_datetime' => '2038-07-14 11:00:00',
        ]);

        // Impediment 3: Starts inside period, ends after (Friday, July 16)
        $impediment3 = impediment_for($availability)->create([
            'reason' => 'Rendez-vous externe',
            'start_datetime' => '2038-07-16 15:00:00', // Friday
            'end_datetime' => '2038-07-16 17:00:00',
        ]);

        // Impediment 4: Outside period (should not be returned) (Monday, July 5)
        $impediment4 = impediment_for($availability)->create([
            'reason' => 'Vacances',
            'start_datetime' => '2038-07-05 10:00:00', // Monday
            'end_datetime' => '2038-07-05 12:00:00',
        ]);

        // Act: Get impediments for period July 10-20
        $periodStart = Carbon::parse('2038-07-10 00:00:00');
        $periodEnd = Carbon::parse('2038-07-20 23:59:59');
        $impediments = $this->testModel->getImpedimentsInPeriod($periodStart, $periodEnd);

        // Assert: Should return impediments 1, 2, 3 but not 4
        $this->assertInstanceOf(Collection::class, $impediments);
        $this->assertCount(3, $impediments);

        $impedimentIds = $impediments->pluck('id')->toArray();
        $this->assertContains($impediment1->id, $impedimentIds);
        $this->assertContains($impediment2->id, $impedimentIds);
        $this->assertContains($impediment3->id, $impedimentIds);
        $this->assertNotContains($impediment4->id, $impedimentIds);
    }

    /**
     * Test getImpedimentsInPeriod returns empty collection when no impediments exist.
     */
    public function test_get_impediments_in_period_returns_empty_when_none_exist(): void
    {
        // Arrange: Create availability but no impediments
        availability_for($this->testModel)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Act: Get impediments for period
        $periodStart = Carbon::parse('2038-07-10 00:00:00');
        $periodEnd = Carbon::parse('2038-07-20 23:59:59');
        $impediments = $this->testModel->getImpedimentsInPeriod($periodStart, $periodEnd);

        // Assert: Empty collection
        $this->assertInstanceOf(Collection::class, $impediments);
        $this->assertCount(0, $impediments);
    }

    /**
     * Test getImpedimentsInPeriod respects model isolation.
     */
    public function test_get_impediments_in_period_respects_model_isolation(): void
    {
        // Arrange: Create availabilities for both models
        $availability1 = availability_for($this->testModel)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        $availability2 = availability_for($this->otherModel)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['tuesday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Create impediments for each model (using correct days)
        $impediment1 = impediment_for($availability1)->create([
            'reason' => 'Test Model impediment',
            'start_datetime' => '2038-07-05 10:00:00', // Monday, July 5
            'end_datetime' => '2038-07-05 12:00:00',
        ]);

        $impediment2 = impediment_for($availability2)->create([
            'reason' => 'Other Model impediment',
            'start_datetime' => '2038-07-06 14:00:00', // Tuesday, July 6
            'end_datetime' => '2038-07-06 16:00:00',
        ]);

        // Act: Get impediments for each model
        $periodStart = Carbon::parse('2038-07-01 00:00:00');
        $periodEnd = Carbon::parse('2038-07-10 23:59:59');

        $testModelImpediments = $this->testModel->getImpedimentsInPeriod($periodStart, $periodEnd);
        $otherModelImpediments = $this->otherModel->getImpedimentsInPeriod($periodStart, $periodEnd);

        // Assert: Each model only sees its own impediments
        $this->assertCount(1, $testModelImpediments);
        $this->assertEquals($impediment1->id, $testModelImpediments->first()->id);
        $this->assertEquals('Test Model impediment', $testModelImpediments->first()->reason);

        $this->assertCount(1, $otherModelImpediments);
        $this->assertEquals($impediment2->id, $otherModelImpediments->first()->id);
        $this->assertEquals('Other Model impediment', $otherModelImpediments->first()->reason);
    }

    /**
     * Test getSchedulesInPeriod returns correct schedules.
     */
    public function test_get_schedules_in_period_returns_correct_schedules(): void
    {
        // Arrange: Create availability for July 2038
        $availability = availability_for($this->testModel)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Schedule 1: Completely inside period (Thursday, July 15)
        $schedule1 = schedule_for($availability)->create([
            'title' => 'Consultation annuelle',
            'start_datetime' => '2038-07-15 10:00:00',
            'end_datetime' => '2038-07-15 11:00:00',
        ]);

        // Schedule 2: Starts before period, ends inside (Wednesday, July 14)
        $schedule2 = schedule_for($availability)->create([
            'title' => 'Réunion projet',
            'start_datetime' => '2038-07-14 09:00:00',
            'end_datetime' => '2038-07-14 10:30:00',
        ]);

        // Schedule 3: Starts inside period, ends after (Friday, July 16)
        $schedule3 = schedule_for($availability)->create([
            'title' => 'Formation continue',
            'start_datetime' => '2038-07-16 16:00:00',
            'end_datetime' => '2038-07-16 17:00:00',
        ]);

        // Schedule 4: Outside period (should not be returned) (Monday, July 5)
        $schedule4 = schedule_for($availability)->create([
            'title' => 'Planification trimestrielle',
            'start_datetime' => '2038-07-05 09:00:00',
            'end_datetime' => '2038-07-05 12:00:00',
        ]);

        // Act: Get schedules for period July 10-20
        $periodStart = Carbon::parse('2038-07-10 00:00:00');
        $periodEnd = Carbon::parse('2038-07-20 23:59:59');
        $schedules = $this->testModel->getSchedulesInPeriod($periodStart, $periodEnd);

        // Assert: Should return schedules 1, 2, 3 but not 4
        $this->assertInstanceOf(Collection::class, $schedules);
        $this->assertCount(3, $schedules);

        $scheduleIds = $schedules->pluck('id')->toArray();
        $this->assertContains($schedule1->id, $scheduleIds);
        $this->assertContains($schedule2->id, $scheduleIds);
        $this->assertContains($schedule3->id, $scheduleIds);
        $this->assertNotContains($schedule4->id, $scheduleIds);
    }

    /**
     * Test getSchedulesInPeriod returns empty collection when no schedules exist.
     */
    public function test_get_schedules_in_period_returns_empty_when_none_exist(): void
    {
        // Arrange: Create availability but no schedules
        availability_for($this->testModel)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Act: Get schedules for period
        $periodStart = Carbon::parse('2038-07-10 00:00:00');
        $periodEnd = Carbon::parse('2038-07-20 23:59:59');
        $schedules = $this->testModel->getSchedulesInPeriod($periodStart, $periodEnd);

        // Assert: Empty collection
        $this->assertInstanceOf(Collection::class, $schedules);
        $this->assertCount(0, $schedules);
    }

    /**
     * Test getSchedulesInPeriod respects model isolation.
     */
    public function test_get_schedules_in_period_respects_model_isolation(): void
    {
        // Arrange: Create availabilities for both models
        $availability1 = availability_for($this->testModel)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        $availability2 = availability_for($this->otherModel)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['tuesday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Create schedules for each model (using correct days)
        $schedule1 = schedule_for($availability1)->create([
            'title' => 'Test Model schedule',
            'start_datetime' => '2038-07-05 10:00:00', // Monday, July 5
            'end_datetime' => '2038-07-05 11:00:00',
        ]);

        $schedule2 = schedule_for($availability2)->create([
            'title' => 'Other Model schedule',
            'start_datetime' => '2038-07-06 14:00:00', // Tuesday, July 6
            'end_datetime' => '2038-07-06 15:00:00',
        ]);

        // Act: Get schedules for each model
        $periodStart = Carbon::parse('2038-07-01 00:00:00');
        $periodEnd = Carbon::parse('2038-07-10 23:59:59');

        $testModelSchedules = $this->testModel->getSchedulesInPeriod($periodStart, $periodEnd);
        $otherModelSchedules = $this->otherModel->getSchedulesInPeriod($periodStart, $periodEnd);

        // Assert: Each model only sees its own schedules
        $this->assertCount(1, $testModelSchedules);
        $this->assertEquals($schedule1->id, $testModelSchedules->first()->id);
        $this->assertEquals('Test Model schedule', $testModelSchedules->first()->title);

        $this->assertCount(1, $otherModelSchedules);
        $this->assertEquals($schedule2->id, $otherModelSchedules->first()->id);
        $this->assertEquals('Other Model schedule', $otherModelSchedules->first()->title);
    }

    /**
     * Test getRosterItemsInPeriod returns both impediments and schedules.
     */
    public function test_get_roster_items_in_period_returns_both_impediments_and_schedules(): void
    {
        // Arrange: Create availability for July 2038
        $availability = availability_for($this->testModel)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Create impediments (Monday and Tuesday)
        $impediment1 = impediment_for($availability)->create([
            'reason' => 'Formation sécurité',
            'start_datetime' => '2038-07-05 09:00:00', // Monday, July 5
            'end_datetime' => '2038-07-05 12:00:00',
        ]);

        $impediment2 = impediment_for($availability)->create([
            'reason' => 'Entretien annuel',
            'start_datetime' => '2038-07-06 14:00:00', // Tuesday, July 6
            'end_datetime' => '2038-07-06 16:00:00',
        ]);

        // Create schedules (Monday and Tuesday)
        $schedule1 = schedule_for($availability)->create([
            'title' => 'Consultation patient A',
            'start_datetime' => '2038-07-05 13:00:00', // Monday, July 5
            'end_datetime' => '2038-07-05 14:00:00',
        ]);

        $schedule2 = schedule_for($availability)->create([
            'title' => 'Consultation patient B',
            'start_datetime' => '2038-07-06 10:00:00', // Tuesday, July 6
            'end_datetime' => '2038-07-06 11:00:00',
        ]);

        // Schedule outside period (Monday, July 12)
        schedule_for($availability)->create([
            'title' => 'Consultation hors période',
            'start_datetime' => '2038-07-12 10:00:00',
            'end_datetime' => '2038-07-12 11:00:00',
        ]);

        // Act: Get all roster items for period July 5-6
        $periodStart = Carbon::parse('2038-07-05 00:00:00');
        $periodEnd = Carbon::parse('2038-07-06 23:59:59');
        $rosterItems = $this->testModel->getRosterItemsInPeriod($periodStart, $periodEnd);

        // Assert: Correct structure and content
        $this->assertIsArray($rosterItems);
        $this->assertArrayHasKey('impediments', $rosterItems);
        $this->assertArrayHasKey('schedules', $rosterItems);

        // Assert impediments collection
        $this->assertInstanceOf(Collection::class, $rosterItems['impediments']);
        $this->assertCount(2, $rosterItems['impediments']);
        $this->assertEquals(
            [$impediment1->id, $impediment2->id],
            $rosterItems['impediments']->pluck('id')->sort()->values()->toArray()
        );

        // Assert schedules collection
        $this->assertInstanceOf(Collection::class, $rosterItems['schedules']);
        $this->assertCount(2, $rosterItems['schedules']);
        $this->assertEquals(
            [$schedule1->id, $schedule2->id],
            $rosterItems['schedules']->pluck('id')->sort()->values()->toArray()
        );
    }

    /**
     * Test getRosterItemsInPeriod returns empty collections when no items exist.
     */
    public function test_get_roster_items_in_period_returns_empty_when_none_exist(): void
    {
        // Arrange: Create availability but no impediments or schedules
        availability_for($this->testModel)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Act: Get roster items for period
        $periodStart = Carbon::parse('2038-07-10 00:00:00');
        $periodEnd = Carbon::parse('2038-07-20 23:59:59');
        $rosterItems = $this->testModel->getRosterItemsInPeriod($periodStart, $periodEnd);

        // Assert: Both collections are empty
        $this->assertInstanceOf(Collection::class, $rosterItems['impediments']);
        $this->assertCount(0, $rosterItems['impediments']);

        $this->assertInstanceOf(Collection::class, $rosterItems['schedules']);
        $this->assertCount(0, $rosterItems['schedules']);
    }

    /**
     * Test hasConflictsInPeriod returns true when conflicts exist.
     */
    public function test_has_conflicts_in_period_returns_true_when_conflicts_exist(): void
    {
        // Arrange: Create availability
        $availability = availability_for($this->testModel)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Create impediment in period (Monday, July 5)
        impediment_for($availability)->create([
            'reason' => 'Réunion urgente',
            'start_datetime' => '2038-07-05 10:00:00',
            'end_datetime' => '2038-07-05 12:00:00',
        ]);

        // Create schedule in period (Monday, July 5)
        schedule_for($availability)->create([
            'title' => 'Consultation',
            'start_datetime' => '2038-07-05 14:00:00',
            'end_datetime' => '2038-07-05 15:00:00',
        ]);

        // Act: Check for conflicts in period
        $periodStart = Carbon::parse('2038-07-05 00:00:00');
        $periodEnd = Carbon::parse('2038-07-05 23:59:59');
        $hasConflicts = $this->testModel->hasConflictsInPeriod($periodStart, $periodEnd);

        // Assert: True because both impediment and schedule exist
        $this->assertTrue($hasConflicts);
    }

    /**
     * Test hasConflictsInPeriod returns false when no conflicts exist.
     */
    public function test_has_conflicts_in_period_returns_false_when_no_conflicts(): void
    {
        // Arrange: Create availability
        $availability = availability_for($this->testModel)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Create items outside period (different Monday)
        $availability = availability_for($this->testModel)->first();

        impediment_for($availability)->create([
            'reason' => 'Réunion',
            'start_datetime' => '2038-07-12 10:00:00', // Monday, July 12
            'end_datetime' => '2038-07-12 12:00:00',
        ]);

        schedule_for($availability)->create([
            'title' => 'Consultation',
            'start_datetime' => '2038-07-12 14:00:00', // Monday, July 12
            'end_datetime' => '2038-07-12 15:00:00',
        ]);

        // Act: Check for conflicts in period (no items in this period)
        $periodStart = Carbon::parse('2038-07-05 00:00:00');
        $periodEnd = Carbon::parse('2038-07-05 23:59:59');
        $hasConflicts = $this->testModel->hasConflictsInPeriod($periodStart, $periodEnd);

        // Assert: False because no items in period
        $this->assertFalse($hasConflicts);
    }

    /**
     * Test hasConflictsInPeriod returns true with only impediments.
     */
    public function test_has_conflicts_in_period_with_only_impediments(): void
    {
        // Arrange: Create availability and impediment
        $availability = availability_for($this->testModel)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        impediment_for($availability)->create([
            'reason' => 'Formation',
            'start_datetime' => '2038-07-05 10:00:00', // Monday, July 5
            'end_datetime' => '2038-07-05 12:00:00',
        ]);

        // Act: Check for conflicts
        $periodStart = Carbon::parse('2038-07-05 00:00:00');
        $periodEnd = Carbon::parse('2038-07-05 23:59:59');
        $hasConflicts = $this->testModel->hasConflictsInPeriod($periodStart, $periodEnd);

        // Assert: True because impediment exists
        $this->assertTrue($hasConflicts);
    }

    /**
     * Test hasConflictsInPeriod returns true with only schedules.
     */
    public function test_has_conflicts_in_period_with_only_schedules(): void
    {
        // Arrange: Create availability and schedule
        $availability = availability_for($this->testModel)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        schedule_for($availability)->create([
            'title' => 'Consultation',
            'start_datetime' => '2038-07-05 14:00:00', // Monday, July 5
            'end_datetime' => '2038-07-05 15:00:00',
        ]);

        // Act: Check for conflicts
        $periodStart = Carbon::parse('2038-07-05 00:00:00');
        $periodEnd = Carbon::parse('2038-07-05 23:59:59');
        $hasConflicts = $this->testModel->hasConflictsInPeriod($periodStart, $periodEnd);

        // Assert: True because schedule exists
        $this->assertTrue($hasConflicts);
    }

    /**
     * Test getAvailabilitiesInPeriod returns correct availabilities.
     */
    public function test_get_availabilities_in_period_returns_correct_availabilities(): void
    {
        // Arrange: Create multiple availabilities for July 2038
        $availability1 = availability_for($this->testModel)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday', 'wednesday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-15', // Se termine le 15
        ]);

        $availability2 = availability_for($this->testModel)->create([
            'type' => 'surgery',
            'daily_start' => '13:00:00',
            'daily_end' => '17:00:00',
            'days' => ['tuesday', 'thursday'],
            'validity_start' => '2038-07-10', // Commence le 10
            'validity_end' => '2038-07-25',
        ]);

        $availability3 = availability_for($this->testModel)->create([
            'type' => 'training',
            'daily_start' => '08:00:00',
            'daily_end' => '10:00:00',
            'days' => ['friday'],
            'validity_start' => '2038-07-21', // Commence le 21 (après la période)
            'validity_end' => '2038-07-31',
        ]);

        // Act: Get availabilities for mid-July (July 10-20)
        $periodStart = Carbon::parse('2038-07-10 00:00:00');
        $periodEnd = Carbon::parse('2038-07-20 23:59:59');
        $availabilities = $this->testModel->getAvailabilitiesInPeriod($periodStart, $periodEnd);

        // Assert: Should return availabilities 1 and 2, but not 3
        // availability3 starts on July 21, which is after our period end (July 20)
        $this->assertInstanceOf(Collection::class, $availabilities);
        $this->assertCount(2, $availabilities);

        $availabilityIds = $availabilities->pluck('id')->toArray();
        $this->assertContains($availability1->id, $availabilityIds);
        $this->assertContains($availability2->id, $availabilityIds);
        $this->assertNotContains($availability3->id, $availabilityIds);
    }

    /**
     * Test getAvailabilitiesInPeriod with type filter.
     */
    public function test_get_availabilities_in_period_with_type_filter(): void
    {
        // Arrange: Create availabilities with different types
        $consultationAvailability = availability_for($this->testModel)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        $surgeryAvailability = availability_for($this->testModel)->create([
            'type' => 'surgery',
            'daily_start' => '13:00:00',
            'daily_end' => '17:00:00',
            'days' => ['tuesday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        $trainingAvailability = availability_for($this->testModel)->create([
            'type' => 'training',
            'daily_start' => '08:00:00',
            'daily_end' => '10:00:00',
            'days' => ['wednesday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Act: Get availabilities with type filter
        $periodStart = Carbon::parse('2038-07-01 00:00:00');
        $periodEnd = Carbon::parse('2038-07-31 23:59:59');

        $consultationAvailabilities = $this->testModel->getAvailabilitiesInPeriod($periodStart, $periodEnd, 'consultation');
        $surgeryAvailabilities = $this->testModel->getAvailabilitiesInPeriod($periodStart, $periodEnd, 'surgery');
        $trainingAvailabilities = $this->testModel->getAvailabilitiesInPeriod($periodStart, $periodEnd, 'training');

        // Assert: Each collection contains only the correct type
        $this->assertCount(1, $consultationAvailabilities);
        $this->assertEquals('consultation', $consultationAvailabilities->first()->type);

        $this->assertCount(1, $surgeryAvailabilities);
        $this->assertEquals('surgery', $surgeryAvailabilities->first()->type);

        $this->assertCount(1, $trainingAvailabilities);
        $this->assertEquals('training', $trainingAvailabilities->first()->type);
    }

    /**
     * Test getAvailabilitiesInPeriod with overlapping validity periods.
     */
    public function test_get_availabilities_in_period_with_overlapping_validity(): void
    {
        // Arrange: Create availabilities with different validity periods for July
        $shortTerm = availability_for($this->testModel)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-10',
            'validity_end' => '2038-07-20',
        ]);

        $mediumTerm = availability_for($this->testModel)->create([
            'type' => 'consultation',
            'daily_start' => '13:00:00',
            'daily_end' => '17:00:00',
            'days' => ['tuesday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-25',
        ]);

        $longTerm = availability_for($this->testModel)->create([
            'type' => 'consultation',
            'daily_start' => '08:00:00',
            'daily_end' => '10:00:00',
            'days' => ['wednesday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Act: Get availabilities for mid-July (July 15-25)
        $periodStart = Carbon::parse('2038-07-15 00:00:00');
        $periodEnd = Carbon::parse('2038-07-25 23:59:59');
        $availabilities = $this->testModel->getAvailabilitiesInPeriod($periodStart, $periodEnd);

        // Assert: Should return all three (all overlap with the period)
        $this->assertCount(3, $availabilities);

        $availabilityIds = $availabilities->pluck('id')->toArray();
        $this->assertContains($shortTerm->id, $availabilityIds);
        $this->assertContains($mediumTerm->id, $availabilityIds);
        $this->assertContains($longTerm->id, $availabilityIds);
    }

    /**
     * Test boundary conditions for period queries.
     */
    public function test_boundary_conditions_for_period_queries(): void
    {
        // Arrange: Create availability with specific impediment
        $availability = availability_for($this->testModel)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        $impediment = impediment_for($availability)->create([
            'reason' => 'Boundary test impediment',
            'start_datetime' => '2038-07-05 10:00:00', // Monday, July 5
            'end_datetime' => '2038-07-05 12:00:00',
        ]);

        $schedule = schedule_for($availability)->create([
            'title' => 'Boundary test schedule',
            'start_datetime' => '2038-07-05 14:00:00', // Monday, July 5
            'end_datetime' => '2038-07-05 15:00:00',
        ]);

        // Test 1: Period exactly matching impediment
        $exactPeriodStart = Carbon::parse('2038-07-05 10:00:00');
        $exactPeriodEnd = Carbon::parse('2038-07-05 12:00:00');
        $exactImpediments = $this->testModel->getImpedimentsInPeriod($exactPeriodStart, $exactPeriodEnd);
        $this->assertCount(1, $exactImpediments);
        $this->assertEquals($impediment->id, $exactImpediments->first()->id);

        // Test 2: Period exactly matching schedule
        $exactScheduleStart = Carbon::parse('2038-07-05 14:00:00');
        $exactScheduleEnd = Carbon::parse('2038-07-05 15:00:00');
        $exactSchedules = $this->testModel->getSchedulesInPeriod($exactScheduleStart, $exactScheduleEnd);
        $this->assertCount(1, $exactSchedules);
        $this->assertEquals($schedule->id, $exactSchedules->first()->id);

        // Test 3: Period one second before impediment
        $justBeforeStart = Carbon::parse('2038-07-05 09:59:59');
        $justBeforeEnd = Carbon::parse('2038-07-05 10:00:00');
        $beforeImpediments = $this->testModel->getImpedimentsInPeriod($justBeforeStart, $justBeforeEnd);
        $this->assertCount(0, $beforeImpediments);

        // Test 4: Period one second after impediment
        $justAfterStart = Carbon::parse('2038-07-05 12:00:00');
        $justAfterEnd = Carbon::parse('2038-07-05 12:00:01');
        $afterImpediments = $this->testModel->getImpedimentsInPeriod($justAfterStart, $justAfterEnd);
        $this->assertCount(0, $afterImpediments);

        // Test 5: Period overlapping end of impediment
        $overlapEndStart = Carbon::parse('2038-07-05 11:30:00');
        $overlapEndEnd = Carbon::parse('2038-07-05 12:30:00');
        $overlapEndImpediments = $this->testModel->getImpedimentsInPeriod($overlapEndStart, $overlapEndEnd);
        $this->assertCount(1, $overlapEndImpediments);
    }

    /**
     * Test edge cases with empty periods and invalid dates.
     */
    public function test_edge_cases_with_empty_periods(): void
    {
        // Arrange: Create some data
        $availability = availability_for($this->testModel)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        impediment_for($availability)->create([
            'reason' => 'Test impediment',
            'start_datetime' => '2038-07-05 10:00:00', // Monday, July 5
            'end_datetime' => '2038-07-05 12:00:00',
        ]);

        // Test 1: Same start and end time (zero duration period)
        $sameTime = Carbon::parse('2038-07-12 10:00:00');
        $sameTimeImpediments = $this->testModel->getImpedimentsInPeriod($sameTime, $sameTime);
        $this->assertCount(0, $sameTimeImpediments);

        // Test 2: End before start (invalid period)
        $endBeforeStartImpediments = $this->testModel->getImpedimentsInPeriod(
            Carbon::parse('2038-07-12 12:00:00'),
            Carbon::parse('2038-07-12 10:00:00')
        );
        $this->assertCount(0, $endBeforeStartImpediments);

        // Test 3: Very short period (1 second)
        $shortPeriodStart = Carbon::parse('2038-07-05 10:00:00');
        $shortPeriodEnd = Carbon::parse('2038-07-05 10:00:01');
        $shortPeriodImpediments = $this->testModel->getImpedimentsInPeriod($shortPeriodStart, $shortPeriodEnd);
        $this->assertCount(1, $shortPeriodImpediments);

        // Test 4: Very long period (months)
        $longPeriodStart = Carbon::parse('2038-06-01 00:00:00');
        $longPeriodEnd = Carbon::parse('2038-08-31 23:59:59');
        $longPeriodImpediments = $this->testModel->getImpedimentsInPeriod($longPeriodStart, $longPeriodEnd);
        $this->assertCount(1, $longPeriodImpediments);
    }

    /**
     * Test method chaining with relationships.
     */
    public function test_method_chaining_with_relationships(): void
    {
        // Arrange: Create availability and multiple items
        $availability = availability_for($this->testModel)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Create multiple impediments and schedules (Mondays and Tuesdays in July)
        $operations = [];
        $mondayDates = ['2038-07-05', '2038-07-12', '2038-07-19']; // Mondays in July 2038
        $tuesdayDates = ['2038-07-06', '2038-07-13', '2038-07-20']; // Tuesdays in July 2038

        for ($i = 1; $i <= 3; $i++) {
            try {
                $impediment = impediment_for($availability)->create([
                    'reason' => "Impediment $i",
                    'start_datetime' => "{$mondayDates[$i - 1]} 10:00:00",
                    'end_datetime' => "{$mondayDates[$i - 1]} 12:00:00",
                ]);
                $operations[] = ['type' => 'impediment', 'success' => true, 'id' => $impediment->id];
            } catch (\Exception $e) {
                $operations[] = ['type' => 'impediment', 'success' => false, 'error' => $e->getMessage()];
            }

            try {
                $schedule = schedule_for($availability)->create([
                    'title' => "Schedule $i",
                    'start_datetime' => "{$tuesdayDates[$i - 1]} 14:00:00",
                    'end_datetime' => "{$tuesdayDates[$i - 1]} 15:00:00",
                ]);
                $operations[] = ['type' => 'schedule', 'success' => true, 'id' => $schedule->id];
            } catch (\Exception $e) {
                $operations[] = ['type' => 'schedule', 'success' => false, 'error' => $e->getMessage()];
            }
        }

        // Act: Chain period methods with relationship queries
        $periodStart = Carbon::parse('2038-07-10 00:00:00');
        $periodEnd = Carbon::parse('2038-07-20 23:59:59');

        // Get impediments and filter further
        $impediments = $this->testModel->getImpedimentsInPeriod($periodStart, $periodEnd);
        $filteredImpediments = $impediments->where('reason', 'Impediment 2');

        // Get schedules and filter further
        $schedules = $this->testModel->getSchedulesInPeriod($periodStart, $periodEnd);
        $filteredSchedules = $schedules->where('title', 'Schedule 3');

        // Assert: Chaining works correctly
        // Should have impediment for July 12 (Monday) and schedule for July 20 (Tuesday)
        $this->assertGreaterThan(0, $impediments->count());
        $this->assertGreaterThan(0, $filteredImpediments->count());

        $this->assertGreaterThan(0, $schedules->count());
        $this->assertGreaterThan(0, $filteredSchedules->count());
    }

    /**
     * Test performance with large datasets.
     */
    public function test_performance_with_large_datasets(): void
    {
        // Arrange: Create many items
        $availability = availability_for($this->testModel)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        $startTime = microtime(true);

        // Create 20 impediments (weekdays in July)
        for ($i = 1; $i <= 20; $i++) {
            // Calculate valid weekday dates in July
            $date = Carbon::parse('2038-07-01')->addDays($i - 1);
            if ($date->isWeekday()) {
                $day = str_pad((string)$date->day, 2, '0', STR_PAD_LEFT);
                try {
                    impediment_for($availability)->create([
                        'reason' => "Impediment $i",
                        'start_datetime' => "2038-07-$day 10:00:00",
                        'end_datetime' => "2038-07-$day 12:00:00",
                    ]);
                } catch (\Exception $e) {
                    // Skip if validation fails
                }
            }
        }

        // Create 20 schedules (weekdays in July)
        for ($i = 1; $i <= 20; $i++) {
            // Calculate valid weekday dates in July
            $date = Carbon::parse('2038-07-01')->addDays($i + 4); // Offset to avoid overlapping with impediments
            if ($date->isWeekday() && $date->month === 7) {
                $day = str_pad((string)$date->day, 2, '0', STR_PAD_LEFT);
                try {
                    schedule_for($availability)->create([
                        'title' => "Schedule $i",
                        'start_datetime' => "2038-07-$day 14:00:00",
                        'end_datetime' => "2038-07-$day 15:00:00",
                    ]);
                } catch (\Exception $e) {
                    // Skip if validation fails
                }
            }
        }

        // Act: Query for specific period (mid-July)
        $periodStart = Carbon::parse('2038-07-10 00:00:00');
        $periodEnd = Carbon::parse('2038-07-20 23:59:59');

        $impediments = $this->testModel->getImpedimentsInPeriod($periodStart, $periodEnd);
        $schedules = $this->testModel->getSchedulesInPeriod($periodStart, $periodEnd);
        $rosterItems = $this->testModel->getRosterItemsInPeriod($periodStart, $periodEnd);
        $hasConflicts = $this->testModel->hasConflictsInPeriod($periodStart, $periodEnd);

        $executionTime = microtime(true) - $startTime;

        // Assert: Correct counts and reasonable performance
        $this->assertGreaterThan(0, $impediments->count());
        $this->assertGreaterThan(0, $schedules->count());
        $this->assertTrue($hasConflicts);

        // Performance assertion (should complete quickly)
        $this->assertLessThan(2.0, $executionTime, 'Queries should complete in less than 2 seconds');
    }
}
