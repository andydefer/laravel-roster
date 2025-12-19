<?php

declare(strict_types=1);

namespace Tests\Integration\Services\Core;

use Illuminate\Support\Carbon;
use Roster\Contracts\Services\ValidationServiceInterface;
use Roster\Exceptions\TimeRangeValidationException;
use Roster\Exceptions\ValidationException;
use Tests\TestCase;

final class ValidationServiceTest extends TestCase
{
    private ValidationServiceInterface $validationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validationService = app(ValidationServiceInterface::class);
    }

    public function test_validate_time_range_with_valid_range(): void
    {
        $start = Carbon::parse('2038-06-01 10:00:00');
        $end = Carbon::parse('2038-06-01 11:00:00');

        $this->expectNotToPerformAssertions();
        $this->validationService->validateTimeRange($start, $end);
    }

    public function test_validate_time_range_with_invalid_range_throws_exception(): void
    {
        $start = Carbon::parse('2038-06-01 11:00:00');
        $end = Carbon::parse('2038-06-01 10:00:00');

        $this->expectException(TimeRangeValidationException::class);
        $this->validationService->validateTimeRange($start, $end);
    }

    public function test_validate_future_date_with_future_date(): void
    {
        $futureDate = Carbon::now()->addDay();

        $this->expectNotToPerformAssertions();
        $this->validationService->validateFutureDate($futureDate);
    }

    public function test_validate_future_date_with_past_date_throws_exception(): void
    {
        $pastDate = Carbon::now()->subDay();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Cannot schedule in the past');
        $this->validationService->validateFutureDate($pastDate);
    }

    public function test_validate_minimum_duration_with_sufficient_duration(): void
    {
        $start = Carbon::parse('10:00:00');
        $end = Carbon::parse('10:30:00');

        $this->expectNotToPerformAssertions();
        $this->validationService->validateMinimumDuration($start, $end, 15);
    }

    public function test_validate_minimum_duration_with_insufficient_duration_throws_exception(): void
    {
        $start = Carbon::parse('10:00:00');
        $end = Carbon::parse('10:10:00');

        $this->expectException(ValidationException::class);
        $this->validationService->validateMinimumDuration($start, $end, 15);
    }

    public function test_validate_required_fields_with_all_fields_present(): void
    {
        $data = [
            'start_datetime' => '2038-06-01 10:00:00',
            'end_datetime' => '2038-06-01 11:00:00',
            'title' => 'Test Schedule',
        ];

        $requiredFields = ['start_datetime', 'end_datetime', 'title'];

        $this->expectNotToPerformAssertions();
        $this->validationService->validateRequiredFields($data, $requiredFields);
    }

    public function test_validate_required_fields_with_missing_field_throws_exception(): void
    {
        $data = [
            'start_datetime' => '2038-06-01 10:00:00',
            // end_datetime is missing
        ];

        $requiredFields = ['start_datetime', 'end_datetime'];

        $this->expectException(ValidationException::class);
        $this->validationService->validateRequiredFields($data, $requiredFields);
    }

    public function test_parse_and_validate_datetime_range(): void
    {
        $data = [
            'start_datetime' => '2038-06-01 10:00:00',
            'end_datetime' => '2038-06-01 11:00:00',
        ];

        $result = $this->validationService->parseAndValidateDateTimeRange($data);

        $this->assertInstanceOf(Carbon::class, $result['start']);
        $this->assertInstanceOf(Carbon::class, $result['end']);
        $this->assertSame('2038-06-01 10:00:00', $result['start']->format('Y-m-d H:i:s'));
        $this->assertSame('2038-06-01 11:00:00', $result['end']->format('Y-m-d H:i:s'));
    }

    public function test_parse_and_validate_time_range(): void
    {
        $data = [
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
        ];

        $result = $this->validationService->parseAndValidateTimeRange($data);

        $this->assertInstanceOf(Carbon::class, $result['start']);
        $this->assertInstanceOf(Carbon::class, $result['end']);
        $this->assertSame('10:00:00', $result['start']->format('H:i:s'));
        $this->assertSame('11:00:00', $result['end']->format('H:i:s'));
    }

    public function test_validate_timezone_with_valid_timezone(): void
    {
        $this->assertTrue($this->validationService->validateTimezone('UTC'));
        $this->assertTrue($this->validationService->validateTimezone('Europe/Paris'));
        $this->assertTrue($this->validationService->validateTimezone('America/New_York'));
    }

    public function test_validate_timezone_with_invalid_timezone(): void
    {
        $this->assertFalse($this->validationService->validateTimezone('Invalid/Timezone'));
        $this->assertFalse($this->validationService->validateTimezone('NotATimezone'));
    }
}
