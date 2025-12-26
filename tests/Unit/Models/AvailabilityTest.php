<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use Roster\Facades\Availability;
use Roster\Models\Availability as AvailabilityModel;
use Tests\Support\TestSchedulable;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

/**
 * Unit tests for the Availability model.
 */
final class AvailabilityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test schedulable instance.
     */
    private TestSchedulable $schedulable;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->schedulable = TestSchedulable::create();
    }

    /**
     * Helper method to create an availability instance.
     */
    private function createAvailability(array $attributes = []): AvailabilityModel
    {
        $defaultAttributes = [
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-07-01 00:00:00',
            'validity_end' => '2038-07-31 23:59:59',
        ];

        return Availability::for($this->schedulable)
            ->create(array_merge($defaultAttributes, $attributes));
    }

    /**
     * Test that availability can be created with valid attributes.
     */
    public function test_availability_can_be_created_with_valid_attributes(): void
    {
        $availability = $this->createAvailability([
            'type' => 'training',
            'daily_start' => '10:00:00',
            'daily_end' => '18:00:00',
            'days' => ['monday', 'wednesday', 'friday'],
        ]);

        $this->assertInstanceOf(AvailabilityModel::class, $availability);
        $this->assertSame($this->schedulable->id, $availability->schedulable_id);
        $this->assertSame(TestSchedulable::class, $availability->schedulable_type);
        $this->assertEquals('training', $availability->type);
        $this->assertEquals(['monday', 'wednesday', 'friday'], $availability->days);
    }

    /**
     * Test that daily_start and daily_end are properly cast to time format.
     */
    public function test_daily_times_are_properly_cast(): void
    {
        $availability = $this->createAvailability([
            'daily_start' => '08:30:00',
            'daily_end' => '16:45:00',
        ]);

        $this->assertEquals('08:30:00', $availability->daily_start->format('H:i:s'));
        $this->assertEquals('16:45:00', $availability->daily_end->format('H:i:s'));
    }

    /**
     * Test that validity dates are properly cast to datetime.
     */
    public function test_validity_dates_are_properly_cast(): void
    {
        $availability = $this->createAvailability([
            'validity_start' => '2038-07-15 00:00:00',
            'validity_end' => '2038-07-25 23:59:59',
        ]);

        $this->assertEquals('2038-07-15 00:00:00', $availability->validity_start->format('Y-m-d H:i:s'));
        $this->assertEquals('2038-07-25 23:59:59', $availability->validity_end->format('Y-m-d H:i:s'));
    }

    /**
     * Test that days attribute is properly cast to array.
     */
    public function test_days_are_properly_cast_to_array(): void
    {
        $availability = $this->createAvailability([
            'days' => ['tuesday', 'thursday'],
        ]);

        $this->assertIsArray($availability->days);
        $this->assertEquals(['tuesday', 'thursday'], $availability->days);
    }

    /**
     * Test that availability correctly identifies available days.
     */
    public function test_is_available_on_day_returns_true_for_included_days(): void
    {
        $availability = $this->createAvailability([
            'days' => ['monday', 'tuesday'],
        ]);

        // Juillet 2038 commence un jeudi
        $monday = Carbon::parse('2038-07-05'); // Lundi 5 juillet 2038
        $tuesday = Carbon::parse('2038-07-06'); // Mardi 6 juillet 2038
        $wednesday = Carbon::parse('2038-07-07'); // Mercredi 7 juillet 2038

        $this->assertTrue($availability->isActiveOnDate($monday));
        $this->assertTrue($availability->isActiveOnDate($tuesday));
        $this->assertFalse($availability->isActiveOnDate($wednesday));
    }

    /**
     * Test that is_within_daily_window returns true for times within window.
     */
    public function test_is_within_daily_window_returns_true_for_times_within_window(): void
    {
        $availability = $this->createAvailability([
            'days' => ['thursday'], // 1er juillet 2038 est un jeudi
        ]);

        $start = Carbon::parse('2038-07-01 10:00:00'); // Jeudi 1er juillet
        $end = Carbon::parse('2038-07-01 11:00:00');

        $this->assertTrue($availability->isAvailableForSchedule($start, $end));
    }

    /**
     * Test that is_within_daily_window returns false for times outside window.
     */
    public function test_is_within_daily_window_returns_false_for_times_outside_window(): void
    {
        $availability = $this->createAvailability([
            'days' => ['thursday'], // 1er juillet 2038 est un jeudi
        ]);

        $start = Carbon::parse('2038-07-01 08:00:00'); // Avant la fenêtre
        $end = Carbon::parse('2038-07-01 09:30:00');

        $this->assertFalse($availability->isAvailableForSchedule($start, $end));
    }

    /**
     * Test that is_within_validity_period returns true for dates within period.
     */
    public function test_is_within_validity_period_returns_true_for_dates_within_period(): void
    {
        $availability = $this->createAvailability([
            'days' => ['thursday'], // 1er juillet 2038 est un jeudi
        ]);

        $start = Carbon::parse('2038-07-01 10:00:00'); // Dans la période
        $end = Carbon::parse('2038-07-01 11:00:00');

        $this->assertTrue($availability->isAvailableForSchedule($start, $end));
    }

    /**
     * Test that is_within_validity_period returns false for dates before period.
     */
    public function test_is_within_validity_period_returns_false_for_dates_before_period(): void
    {
        $availability = $this->createAvailability([
            'validity_start' => '2038-07-15',
            'validity_end' => '2038-07-31',
            'days' => ['thursday'],
        ]);

        $start = Carbon::parse('2038-07-01 10:00:00'); // Avant la période
        $end = Carbon::parse('2038-07-01 11:00:00');

        $this->assertFalse($availability->isAvailableForSchedule($start, $end));
    }

    /**
     * Test that is_within_validity_period returns false for dates after period.
     */
    public function test_is_within_validity_period_returns_false_for_dates_after_period(): void
    {
        $availability = $this->createAvailability([
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-15',
            'days' => ['thursday'],
        ]);

        $start = Carbon::parse('2038-07-20 10:00:00'); // Après la période
        $end = Carbon::parse('2038-07-20 11:00:00');

        $this->assertFalse($availability->isAvailableForSchedule($start, $end));
    }

    /**
     * Test that get_daily_duration_minutes returns correct duration.
     */
    public function test_get_daily_duration_minutes_returns_correct_duration(): void
    {
        $availability = $this->createAvailability();

        $this->assertEquals(480, $availability->getDailyDurationMinutes()); // 8 heures * 60 minutes
    }

    /**
     * Test that get_validity_duration_days returns correct duration.
     */
    public function test_get_validity_duration_days_returns_correct_duration(): void
    {
        $availability = $this->createAvailability();

        $this->assertEquals(30, $availability->getValidityDurationDays()); // 31 jours - 1
    }

    /**
     * Test that get_validity_duration_days returns null when start or end date is missing.
     */
    public function test_get_validity_duration_days_returns_null_when_start_or_end_missing(): void
    {
        // Pour tester cette méthode dans un contexte où les dates seraient null,
        // nous devons créer une instance sans passer par la validation
        $availability = new AvailabilityModel([
            'schedulable_id' => $this->schedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => null,
            'validity_end' => '2038-07-31',
        ]);

        $this->assertNull($availability->getValidityDurationDays());
    }

    /**
     * Test that has_unlimited_validity returns false when validity_end is set.
     */
    public function test_has_unlimited_validity_returns_false_when_validity_end_is_set(): void
    {
        $availability = $this->createAvailability();

        $this->assertFalse($availability->hasUnlimitedValidity());
    }

    /**
     * Test that has_unlimited_validity returns true when validity_end is null.
     *
     * Note: Dans la pratique métier, les availabilities illimitées ne sont pas autorisées,
     * mais cette méthode existe pour la complétude du modèle.
     */
    public function test_has_unlimited_validity_returns_true_when_validity_end_is_null(): void
    {
        // Création d'une instance sans passer par la validation pour tester la méthode
        $availability = new AvailabilityModel([
            'schedulable_id' => $this->schedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => null,
        ]);

        $this->assertTrue($availability->hasUnlimitedValidity());
    }

    /**
     * Test that has_validity_started returns true when date is after start.
     */
    public function test_has_validity_started_returns_true_when_date_is_after_start(): void
    {
        $availability = $this->createAvailability();

        $date = Carbon::parse('2038-07-15');
        $this->assertTrue($availability->hasValidityStarted($date));
    }

    /**
     * Test that has_validity_started returns false when date is before start.
     */
    public function test_has_validity_started_returns_false_when_date_is_before_start(): void
    {
        $availability = $this->createAvailability([
            'validity_start' => '2038-07-15',
            'validity_end' => '2038-07-31',
        ]);

        $date = Carbon::parse('2038-07-01');
        $this->assertFalse($availability->hasValidityStarted($date));
    }

    /**
     * Test that has_validity_started returns true when validity_start is null.
     *
     * Note: Dans la pratique métier, validity_start est requis,
     * mais cette méthode existe pour la complétude du modèle.
     */
    public function test_has_validity_started_returns_true_when_validity_start_is_null(): void
    {
        // Création d'une instance sans passer par la validation
        $availability = new AvailabilityModel([
            'schedulable_id' => $this->schedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => null,
            'validity_end' => '2038-07-31',
        ]);

        $this->assertTrue($availability->hasValidityStarted());
    }

    /**
     * Test that has_validity_ended returns true when date is after end.
     */
    public function test_has_validity_ended_returns_true_when_date_is_after_end(): void
    {
        $availability = $this->createAvailability([
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-15',
        ]);

        $date = Carbon::parse('2038-07-20');
        $this->assertTrue($availability->hasValidityEnded($date));
    }

    /**
     * Test that has_validity_ended returns false when date is before end.
     */
    public function test_has_validity_ended_returns_false_when_date_is_before_end(): void
    {
        $availability = $this->createAvailability();

        $date = Carbon::parse('2038-07-15');
        $this->assertFalse($availability->hasValidityEnded($date));
    }

    /**
     * Test that has_validity_ended returns false when validity_end is null.
     *
     * Note: Dans la pratique métier, validity_end est requis,
     * mais cette méthode existe pour la complétude du modèle.
     */
    public function test_has_validity_ended_returns_false_when_validity_end_is_null(): void
    {
        // Création d'une instance sans passer par la validation
        $availability = new AvailabilityModel([
            'schedulable_id' => $this->schedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => null,
        ]);

        $this->assertFalse($availability->hasValidityEnded());
    }

    /**
     * Test that is_validity_active returns true when date is within validity period.
     */
    public function test_is_validity_active_returns_true_when_date_is_within_period(): void
    {
        $availability = $this->createAvailability();

        $date = Carbon::parse('2038-07-15');
        $this->assertTrue($availability->isValidityActive($date));
    }

    /**
     * Test that is_validity_active returns false when date is before validity period.
     */
    public function test_is_validity_active_returns_false_when_date_is_before_period(): void
    {
        $availability = $this->createAvailability([
            'validity_start' => '2038-07-15',
            'validity_end' => '2038-07-31',
        ]);

        $date = Carbon::parse('2038-07-01');
        $this->assertFalse($availability->isValidityActive($date));
    }

    /**
     * Test that is_validity_active returns false when date is after validity period.
     */
    public function test_is_validity_active_returns_false_when_date_is_after_period(): void
    {
        $availability = $this->createAvailability([
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-15',
        ]);

        $date = Carbon::parse('2038-07-20');
        $this->assertFalse($availability->isValidityActive($date));
    }

    /**
     * Test that schedulable relationship works correctly.
     */
    public function test_schedulable_relationship_returns_correct_model(): void
    {
        $availability = $this->createAvailability();

        $this->assertInstanceOf(TestSchedulable::class, $availability->schedulable);
        $this->assertEquals($this->schedulable->id, $availability->schedulable->id);
    }

    /**
     * Test that schedules relationship works correctly.
     */
    public function test_schedules_relationship_returns_has_many(): void
    {
        $availability = $this->createAvailability();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $availability->schedules());
    }

    /**
     * Test that impediments relationship works correctly.
     */
    public function test_impediments_relationship_returns_has_many(): void
    {
        $availability = $this->createAvailability();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $availability->impediments());
    }

    /**
     * Test that availability correctly checks for schedule availability with all conditions.
     */
    public function test_is_available_for_schedule_returns_true_when_all_conditions_met(): void
    {
        $availability = $this->createAvailability([
            'days' => ['thursday'], // 1er juillet 2038 est un jeudi
        ]);

        $start = Carbon::parse('2038-07-01 10:00:00'); // Jeudi 1er juillet, dans la fenêtre
        $end = Carbon::parse('2038-07-01 11:00:00');

        $this->assertTrue($availability->isAvailableForSchedule($start, $end));
    }

    /**
     * Test that is_available_for_schedule returns false when day is not available.
     */
    public function test_is_available_for_schedule_returns_false_when_day_not_available(): void
    {
        $availability = $this->createAvailability([
            'days' => ['monday'], // Seulement lundi
        ]);

        $start = Carbon::parse('2038-07-01 10:00:00'); // Jeudi 1er juillet (pas lundi)
        $end = Carbon::parse('2038-07-01 11:00:00');

        $this->assertFalse($availability->isAvailableForSchedule($start, $end));
    }

    /**
     * Test that is_available_for_schedule returns false when time is outside daily window.
     */
    public function test_is_available_for_schedule_returns_false_when_time_outside_window(): void
    {
        $availability = $this->createAvailability([
            'days' => ['thursday'], // 1er juillet 2038 est un jeudi
        ]);

        $start = Carbon::parse('2038-07-01 18:00:00'); // Après la fenêtre quotidienne
        $end = Carbon::parse('2038-07-01 19:00:00');

        $this->assertFalse($availability->isAvailableForSchedule($start, $end));
    }

    /**
     * Test that is_available_for_schedule returns false when outside validity period.
     */
    public function test_is_available_for_schedule_returns_false_when_outside_validity_period(): void
    {
        $availability = $this->createAvailability([
            'days' => ['thursday'], // 1er juillet 2038 est un jeudi
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-15',
        ]);

        $start = Carbon::parse('2038-07-20 10:00:00'); // Après la période de validité
        $end = Carbon::parse('2038-07-20 11:00:00');

        $this->assertFalse($availability->isAvailableForSchedule($start, $end));
    }
}
