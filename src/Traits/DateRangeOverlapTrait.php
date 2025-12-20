<?php

declare(strict_types=1);

namespace Roster\Traits;

use Illuminate\Support\Carbon;

/**
 * Provides methods to check for overlapping date ranges with support for null values.
 */
trait DateRangeOverlapTrait
{
    /**
     * Determines whether two date ranges overlap.
     *
     * Null start or end dates are treated as open-ended ranges:
     * - Null start becomes year 0001-01-01
     * - Null end becomes year 9999-12-31
     *
     * @param Carbon|null $existingStartDate Start of the existing range
     * @param Carbon|null $existingEndDate End of the existing range
     * @param Carbon|null $newStartDate Start of the new range
     * @param Carbon|null $newEndDate End of the new range
     *
     * @return bool True if ranges overlap, false otherwise
     */
    public function dateRangesOverlap(
        ?Carbon $existingStartDate,
        ?Carbon $existingEndDate,
        ?Carbon $newStartDate,
        ?Carbon $newEndDate
    ): bool {
        if (! $existingStartDate && ! $existingEndDate) {
            return true;
        }

        if (! $newStartDate && ! $newEndDate) {
            return true;
        }

        $effectiveExistingStart = $existingStartDate ?? Carbon::create(1, 1, 1);
        $effectiveExistingEnd = $existingEndDate ?? Carbon::create(9999, 12, 31);
        $effectiveNewStart = $newStartDate ?? Carbon::create(1, 1, 1);
        $effectiveNewEnd = $newEndDate ?? Carbon::create(9999, 12, 31);

        return $effectiveNewStart->lte($effectiveExistingEnd) &&
            $effectiveNewEnd->gte($effectiveExistingStart);
    }
}
