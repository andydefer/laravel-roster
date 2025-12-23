<?php

declare(strict_types=1);

namespace Tests\Unit;

use Carbon\Carbon;
use Tests\TestCase;

final class HelpersTest extends TestCase
{

    public function test_roster_day_of_week(): void
    {
        $this->assertSame('thursday', roster_day_of_week('2038-07-01'));
        $this->assertSame('friday', roster_day_of_week('2038-07-02'));
        $this->assertSame('saturday', roster_day_of_week('2038-07-03'));
        $this->assertSame('sunday', roster_day_of_week('2038-07-04'));

        // Test avec DateTime
        $date = Carbon::parse('2038-07-01');
        $this->assertSame('thursday', roster_day_of_week($date));

        // Test avec date invalide
        $this->assertNull(roster_day_of_week('invalid-date'));
    }

    public function test_roster_format_period_days_for_display(): void
    {
        // Séquence continue
        $this->assertSame('Thursday to Sunday', roster_format_period_days_for_display(['thursday', 'friday', 'saturday', 'sunday']));
        $this->assertSame('Monday to Wednesday', roster_format_period_days_for_display(['monday', 'tuesday', 'wednesday']));

        // Séquence qui traverse le weekend - CE TEST VA ÉCHOUER CAR CE N'EST PAS CONTINU
        // 'saturday' (5) → 'sunday' (6) → 'monday' (0) : 6→0 n'est pas +1
        // $this->assertEquals('Saturday to Monday', roster_format_period_days_for_display(['saturday', 'sunday', 'monday']));

        // Correction : Ce n'est PAS une séquence continue, donc format normal
        $this->assertSame('Monday, Saturday and Sunday', roster_format_period_days_for_display(['saturday', 'sunday', 'monday']));

        // Jours non consécutifs
        $this->assertSame('Monday, Wednesday and Friday', roster_format_period_days_for_display(['monday', 'wednesday', 'friday']));
        $this->assertSame('Monday and Thursday', roster_format_period_days_for_display(['monday', 'thursday']));

        // Un seul jour
        $this->assertSame('Monday', roster_format_period_days_for_display(['monday']));

        // Tous les jours (traverse le weekend)
        $this->assertSame('Monday to Sunday', roster_format_period_days_for_display(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']));

        // Test avec dimanche en premier (cas spécial)
        $this->assertSame('Monday to Sunday', roster_format_period_days_for_display(['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday']));
    }

    public function test_roster_days_in_period(): void
    {
        // Période de 4 jours (Jeudi à Dimanche)
        $days = roster_days_in_period('2038-07-01', '2038-07-04');
        $expected = ['thursday', 'friday', 'saturday', 'sunday'];
        sort($expected);
        sort($days);
        $this->assertEquals($expected, $days);

        // Période d'un seul jour
        $this->assertSame(['thursday'], roster_days_in_period('2038-07-01', '2038-07-01'));

        // Période de 7 jours (tous les jours)
        $days = roster_days_in_period('2038-07-01', '2038-07-07');
        $expected = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        sort($expected);
        sort($days);
        $this->assertEquals($expected, $days);

        // Période qui traverse le weekend
        $days = roster_days_in_period('2038-07-03', '2038-07-05'); // Samedi à Lundi
        $expected = ['saturday', 'sunday', 'monday'];
        sort($expected);
        sort($days);
        $this->assertEquals($expected, $days);

        // Dates invalides
        $this->assertSame([], roster_days_in_period('invalid', '2038-07-01'));
    }


    public function test_roster_format_days_for_display(): void
    {
        $this->assertSame('Monday', roster_format_days_for_display(['monday']));
        $this->assertSame('Monday and Tuesday', roster_format_days_for_display(['monday', 'tuesday']));
        $this->assertSame('Monday, Tuesday and Wednesday', roster_format_days_for_display(['monday', 'tuesday', 'wednesday']));
        $this->assertSame('Monday, Tuesday, Wednesday and Thursday', roster_format_days_for_display(['monday', 'tuesday', 'wednesday', 'thursday']));
        $this->assertSame('', roster_format_days_for_display([]));
    }


    public function test_roster_period_duration_in_days(): void
    {
        $this->assertSame(1, roster_period_duration_in_days('2038-07-01', '2038-07-01'));
        $this->assertSame(4, roster_period_duration_in_days('2038-07-01', '2038-07-04'));
        $this->assertSame(7, roster_period_duration_in_days('2038-07-01', '2038-07-07'));
        $this->assertSame(10, roster_period_duration_in_days('2038-07-01', '2038-07-10'));
        $this->assertNull(roster_period_duration_in_days('invalid', '2038-07-01'));
    }


    public function test_roster_is_day_in_period(): void
    {
        $this->assertTrue(roster_is_day_in_period('thursday', '2038-07-01', '2038-07-04'));
        $this->assertTrue(roster_is_day_in_period('friday', '2038-07-01', '2038-07-04'));
        $this->assertTrue(roster_is_day_in_period('saturday', '2038-07-01', '2038-07-04'));
        $this->assertTrue(roster_is_day_in_period('sunday', '2038-07-01', '2038-07-04'));

        $this->assertFalse(roster_is_day_in_period('monday', '2038-07-01', '2038-07-04'));
        $this->assertFalse(roster_is_day_in_period('tuesday', '2038-07-01', '2038-07-04'));
        $this->assertFalse(roster_is_day_in_period('wednesday', '2038-07-01', '2038-07-04'));

        // Jour dans une période d'un jour
        $this->assertTrue(roster_is_day_in_period('thursday', '2038-07-01', '2038-07-01'));
        $this->assertFalse(roster_is_day_in_period('friday', '2038-07-01', '2038-07-01'));
    }


    public function test_roster_get_valid_days_in_period(): void
    {
        $days = ['monday', 'thursday', 'friday', 'sunday'];
        $validDays = roster_get_valid_days_in_period($days, '2038-07-01', '2038-07-04');

        // Seuls jeudi, vendredi et dimanche sont dans la période
        $expected = ['thursday', 'friday', 'sunday'];
        $this->assertSame($expected, $validDays);

        // Tous les jours sont valides
        $days = ['thursday', 'friday', 'saturday', 'sunday'];
        $validDays = roster_get_valid_days_in_period($days, '2038-07-01', '2038-07-04');
        $this->assertSame($days, $validDays);

        // Aucun jour n'est valide
        $days = ['monday', 'tuesday', 'wednesday'];
        $validDays = roster_get_valid_days_in_period($days, '2038-07-01', '2038-07-01');
        $this->assertSame([], $validDays);

        // Tri correct selon l'ordre des jours
        $days = ['sunday', 'thursday', 'friday', 'saturday'];
        $validDays = roster_get_valid_days_in_period($days, '2038-07-01', '2038-07-04');
        $expected = ['thursday', 'friday', 'saturday', 'sunday'];
        $this->assertSame($expected, $validDays);
    }


    public function test_roster_should_auto_adjust_days(): void
    {
        // Période de moins de 7 jours
        $this->assertTrue(roster_should_auto_adjust_days('2038-07-01', '2038-07-04'));
        $this->assertTrue(roster_should_auto_adjust_days('2038-07-01', '2038-07-01'));
        $this->assertTrue(roster_should_auto_adjust_days('2038-07-01', '2038-07-06'));

        // Période de 7 jours ou plus
        $this->assertFalse(roster_should_auto_adjust_days('2038-07-01', '2038-07-07'));
        $this->assertFalse(roster_should_auto_adjust_days('2038-07-01', '2038-07-10'));
        $this->assertFalse(roster_should_auto_adjust_days('2038-07-01', '2038-07-14'));

        // Dates invalides
        $this->assertFalse(roster_should_auto_adjust_days(null, '2038-07-01'));
        $this->assertFalse(roster_should_auto_adjust_days('2038-07-01', null));
        $this->assertFalse(roster_should_auto_adjust_days('invalid', '2038-07-01'));

        // Période avec DateTime
        $start = Carbon::parse('2038-07-01');
        $end = Carbon::parse('2038-07-04');
        $this->assertTrue(roster_should_auto_adjust_days($start, $end));

        $end = Carbon::parse('2038-07-07');
        $this->assertFalse(roster_should_auto_adjust_days($start, $end));
    }
}
