<?php

declare(strict_types=1);

namespace Roster\Validation\Rules;

use Illuminate\Database\Eloquent\Model;
use Roster\Models\Availability;
use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Services\AvailabilityMergeService;
use Roster\Validation\Attributes\ValidationRule;

#[ValidationRule(
    priority: 95, // Haute priorité, s'exécute avant la logique métier
    entities: [EntityType::AVAILABILITY],
    operations: [OperationType::CREATE]
)]
class NoDangerousMergeRule extends AbstractRule
{
    private AvailabilityMergeService $availabilityMergeService;

    public function __construct(AvailabilityMergeService $availabilityMergeService)
    {
        $this->availabilityMergeService = $availabilityMergeService;
    }

    public function validate(ValidationContextInterface $validationContext): void
    {
        // Ne s'applique qu'à CREATE
        if ($validationContext->getOperation() !== OperationType::CREATE) {
            return;
        }

        $schedulable = $validationContext->getSchedulable();
        if (!$schedulable instanceof Model) {
            return;
        }

        $data = $validationContext->safeData();

        // Trouver les disponibilités adjacentes
        $adjacentAvailabilities = $this->availabilityMergeService->findAdjacentAvailabilities($data, $schedulable);

        foreach ($adjacentAvailabilities as $adjacentAvailability) {
            $this->checkForDangerousMerge($adjacentAvailability, $validationContext);
        }
    }

    private function checkForDangerousMerge(
        Availability $availability,
        ValidationContextInterface $validationContext
    ): void {
        // Compter les dépendances
        $schedulesCount = $availability->schedules()->count();
        $impedimentsCount = $availability->impediments()->count();

        $hasCriticalDependencies = $schedulesCount > 0 || $impedimentsCount > 0;

        if ($hasCriticalDependencies) {
            $validationContext->setViolation(
                'merge',
                sprintf(
                    'Cannot merge with availability ID %d because it has %d schedule(s) and %d impediment(s)',
                    $availability->id,
                    $schedulesCount,
                    $impedimentsCount
                )
            );

            // Ajouter un flag pour indiquer que la fusion est dangereuse
            $validationContext->setFlag('dangerous_merge_attempted', [
                'existing_availability_id' => $availability->id,
                'schedules_count' => $schedulesCount,
                'impediments_count' => $impedimentsCount,
            ]);
        }
    }
}
