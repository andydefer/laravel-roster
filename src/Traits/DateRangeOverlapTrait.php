<?php

declare(strict_types=1);

namespace Roster\Traits;

use Illuminate\Support\Carbon;

trait DateRangeOverlapTrait
{
    /**
     * Check if date ranges overlap.
     */
    public function dateRangesOverlap(
        ?Carbon $existingStartDate,
        ?Carbon $existingEndDate,
        ?Carbon $newStartDate,
        ?Carbon $newEndDate
    ): bool {
        if (!$existingStartDate && !$existingEndDate) {
            return true;
        }

        if (!$newStartDate && !$newEndDate) {
            return true;
        }

        $effectiveExistingStart = $existingStartDate ?? Carbon::create(1, 1, 1); // 0001-01-01
        $effectiveExistingEnd = $existingEndDate ?? Carbon::create(9999, 12, 31); // 9999-12-31
        $effectiveNewStart = $newStartDate ?? Carbon::create(1, 1, 1);
        $effectiveNewEnd = $newEndDate ?? Carbon::create(9999, 12, 31);

        return $effectiveNewStart->lte($effectiveExistingEnd) &&
            $effectiveNewEnd->gte($effectiveExistingStart);
    }
}
