<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use PHPUnit\Framework\TestCase;
use Roster\Enums\DaysOfWeek;

final class DaysOfWeekTest extends TestCase
{
    public function test_enum_has_correct_values(): void
    {
        $this->assertSame('monday', DaysOfWeek::MONDAY->value);
        $this->assertSame('tuesday', DaysOfWeek::TUESDAY->value);
        $this->assertSame('wednesday', DaysOfWeek::WEDNESDAY->value);
        $this->assertSame('thursday', DaysOfWeek::THURSDAY->value);
        $this->assertSame('friday', DaysOfWeek::FRIDAY->value);
        $this->assertSame('saturday', DaysOfWeek::SATURDAY->value);
        $this->assertSame('sunday', DaysOfWeek::SUNDAY->value);
    }

    public function test_values_method_returns_all_values(): void
    {
        $expected = [
            'monday',
            'tuesday',
            'wednesday',
            'thursday',
            'friday',
            'saturday',
            'sunday',
        ];

        $this->assertSame($expected, DaysOfWeek::values());
    }

    public function test_enum_can_be_instantiated_from_value(): void
    {
        $this->assertSame(DaysOfWeek::MONDAY, DaysOfWeek::from('monday'));
        $this->assertSame(DaysOfWeek::TUESDAY, DaysOfWeek::from('tuesday'));
    }
}
