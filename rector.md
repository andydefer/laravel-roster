# Rector Refactoring Report
*Generated: ven. 26 déc. 2025 11:15:01 WAT*


12 files with changes
=====================

1) /home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Repository/AvailabilityRepositoryInterface.php:13

    ---------- begin diff ----------
@@ @@
 interface AvailabilityRepositoryInterface
 {
     public function findForSchedulable(Model $model, ?string $type = null): Builder;
+
     public function getForDateRange(Model $model, Carbon $start, Carbon $end, ?string $type = null): Collection;
+
     public function getAvailabilityForTimeSlot(Model $model, Carbon $start, Carbon $end, ?string $type = null): ?Availability;
+
     public function getForDate(Model $model, Carbon $date, ?string $type = null): Collection;
+
     public function isAvailableOnDate(Availability $availability, Carbon $date): bool;
+
     public function findForTimeSlotWithConflictInfo(Model $model, Carbon $start, Carbon $end, ?string $type = null): ?Availability;
 }
    ----------- end diff -----------

Applied rules:
 * NewlineBetweenClassLikeStmtsRector


2) /home/andy-kani/pro/sites/packages/laravel-roster/src/Domain/Services/TemporalConflictService.php:24

    ---------- begin diff ----------
@@ @@
     ) {}

     /* -----------------------------------------------------------------
-     | Chevauchments de disponibilités (Availability vs Availability)
-     | -----------------------------------------------------------------
-     */
-
+       | Chevauchments de disponibilités (Availability vs Availability)
+       | -----------------------------------------------------------------
+       */
     /**
      * Check availability conflicts (overlapping availabilities).
+     * @param array<string, mixed> $availabilityData
      */
     public function checkAvailabilityConflicts(
-        Model $schedulable,
+        Model $model,
         array $availabilityData,
         ?int $excludeId = null
     ): ConflictResult {
@@ @@
         $type = $availabilityData['type'] ?? null;

         // Vérifier les conditions minimales
-        if (!$dailyStart || !$dailyEnd || empty($days)) {
+        if (!$dailyStart instanceof Carbon || !$dailyEnd instanceof Carbon || empty($days)) {
             return ConflictResult::noConflict();
         }

         // Récupérer les disponibilités potentielles en conflit
-        $builder = $this->availabilityRepository->findForSchedulable($schedulable, $type);
+        $builder = $this->availabilityRepository->findForSchedulable($model, $type);

         if ($excludeId !== null) {
             $builder->where('id', '!=', $excludeId);
    ----------- end diff -----------

Applied rules:
 * RenameParamToMatchTypeRector
 * AddParamArrayDocblockFromDimFetchAccessRector
 * BinaryOpNullableToInstanceofRector


3) /home/andy-kani/pro/sites/packages/laravel-roster/src/RosterServiceProvider.php:95

    ---------- begin diff ----------
@@ @@
         });


-        $this->app->singleton(TemporalConflictService::class, function ($app) {
+        $this->app->singleton(TemporalConflictService::class, function ($app): TemporalConflictService {
             return new TemporalConflictService(
                 $app->make(AvailabilityRepositoryInterface::class),
                 $app->make(ScheduleRepositoryInterface::class),
    ----------- end diff -----------

Applied rules:
 * ClosureReturnTypeRector


4) /home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php:4

    ---------- begin diff ----------
@@ @@

 namespace Roster\Services;

+use Roster\Validation\Exceptions\ValidationFailedException;
 use Roster\DTOs\AvailabilityData;
 use Roster\Enums\EntityType;
 use Roster\Enums\OperationType;
@@ @@
     {
         $entity = $this->find($id);
         if (!$entity instanceof Availability) {
-            throw \Roster\Validation\Exceptions\ValidationFailedException::fromViolations(
+            throw ValidationFailedException::fromViolations(
                 [
                     'id' => sprintf(
                         '%s with given ID does not exist',
    ----------- end diff -----------

Applied rules:


5) /home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractService.php:4

    ---------- begin diff ----------
@@ @@

 namespace Roster\Services\Core;

+use Roster\Enums\OperationType;
+use Roster\Validation\Exceptions\ValidationFailedException;
+use Illuminate\Support\Collection;
+use Roster\Enums\EntityType;
+use Roster\Validation\Context\ValidationContext;
 use BadMethodCallException;
 use LogicException;
 use Illuminate\Contracts\Pagination\LengthAwarePaginator;
@@ @@
 abstract class AbstractService implements EntityServiceInterface
 {
     protected ?Model $schedulable = null;
+
     protected ?Model $owner = null;
+
     protected array $filters = [];
+
     protected array $data = [];

     public function __construct(
@@ @@
         }

         // Create DTO
-        $dto = $this->createDTOFromArray($this->data, \Roster\Enums\OperationType::CREATE);
+        $dto = $this->createDTOFromArray($this->data, OperationType::CREATE);

         // Add schedulable info to DTO
         $dto = $this->addSchedulableInfoToDto($dto);

         // Validate
-        $this->validate($dto->toArray(), \Roster\Enums\OperationType::CREATE);
+        $this->validate($dto->toArray(), OperationType::CREATE);

         // Check conflicts if applicable
         $this->checkEntityConflicts($dto);
@@ @@
         // Find existing entity
         $existingEntity = $this->find($id);
         if (!$existingEntity) {
-            throw \Roster\Validation\Exceptions\ValidationFailedException::fromViolations(
+            throw ValidationFailedException::fromViolations(
                 ['id' => sprintf('%s with given ID does not exist', $this->getEntityTypeEnum()->displayName())],
-                \Roster\Enums\OperationType::UPDATE,
+                OperationType::UPDATE,
                 $this->getEntityTypeEnum()
             );
         }
@@ @@
         }

         // Create DTO
-        $entityData = $this->createDTOFromArray($data, \Roster\Enums\OperationType::UPDATE);
+        $entityData = $this->createDTOFromArray($data, OperationType::UPDATE);

         // Validate
-        $this->validate($entityData->toArray(), \Roster\Enums\OperationType::UPDATE, $id, $existingEntity);
+        $this->validate($entityData->toArray(), OperationType::UPDATE, $id, $existingEntity);

         // Check conflicts with exclusion
         $this->checkEntityConflicts($entityData, $id);
@@ @@
         $entity = $this->find($id);

         if (!$entity) {
-            throw \Roster\Validation\Exceptions\ValidationFailedException::fromViolations(
+            throw ValidationFailedException::fromViolations(
                 ['id' => sprintf('%s with given ID does not exist', $this->getEntityTypeEnum()->displayName())],
-                \Roster\Enums\OperationType::DELETE,
+                OperationType::DELETE,
                 $this->getEntityTypeEnum()
             );
         }
@@ @@
         }

         // Validate deletion
-        $this->validate($deleteData, \Roster\Enums\OperationType::DELETE, $id);
+        $this->validate($deleteData, OperationType::DELETE, $id);

         // Delete entity
         $result = $this->getCurrentRepository()->delete(
@@ @@
     /**
      * Get all entities.
      */
-    final public function all(): \Illuminate\Support\Collection
+    final public function all(): Collection
     {
         return $this->getCurrentRepository()->all($this->schedulable, $this->owner, $this->filters);
     }
@@ @@
     /**
      * Template method for DTO creation from array.
      */
-    abstract protected function createDTOFromArray(array $data, \Roster\Enums\OperationType $operationType): mixed;
+    abstract protected function createDTOFromArray(array $data, OperationType $operationType): mixed;

     /**
      * Get the entity type as an enum.
      */
-    abstract protected function getEntityTypeEnum(): \Roster\Enums\EntityType;
+    abstract protected function getEntityTypeEnum(): EntityType;

     /**
      * Add schedulable info to DTO.
@@ @@
                 get_class($this->schedulable)
             );
         }
+
         return $dto;
     }

@@ @@
     /**
      * Validate data.
      */
-    protected function validate(array $data, \Roster\Enums\OperationType $operationType, ?int $entityId = null, ?object $currentEntity = null): void
+    protected function validate(array $data, OperationType $operationType, ?int $entityId = null, ?object $currentEntity = null): void
     {
         $entityType = $this->getEntityTypeEnum();

@@ @@
             $currentEntity = $this->find($entityId);
         }

-        $validationContext = new \Roster\Validation\Context\ValidationContext(
+        $validationContext = new ValidationContext(
             operationType: $operationType,
             entityType: $entityType,
             data: $data,
@@ @@
         $validationResult = $this->validator->validate($validationContext);

         if (!$validationResult->isValid()) {
-            throw \Roster\Validation\Exceptions\ValidationFailedException::fromViolations(
+            throw ValidationFailedException::fromViolations(
                 $validationResult->getViolations(),
                 $operationType,
                 $entityType
@@ @@

     /**
      * Intercept dynamic method calls for "whereXyz" methods.
+     * @param array<int, mixed> $arguments
      */
     public function __call(string $method, array $arguments): self
     {
    ----------- end diff -----------

Applied rules:
 * NewlineBetweenClassLikeStmtsRector
 * NewlineAfterStatementRector
 * AddParamArrayDocblockFromDimFetchAccessRector


6) /home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php:5

    ---------- begin diff ----------
@@ @@
 namespace Roster\Services;

 use Roster\Domain\Helpers\TimeSlotHelper;
-use Roster\Domain\Services\TemporalConflictService;
 use Illuminate\Support\Carbon;
 use Illuminate\Support\Collection;
 use Roster\DTOs\ImpedimentData;
@@ @@
 use Roster\Enums\EntityType;
 use Roster\Enums\OperationType;
 use Roster\Models\Availability;
-use Roster\Models\Impediment;
-use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
 use Roster\Services\Core\AbstractService;
 use Roster\Validation\Exceptions\ValidationFailedException;
    ----------- end diff -----------

Applied rules:


7) /home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php:12

    ---------- begin diff ----------
@@ @@
 use Roster\Enums\EntityType;
 use Roster\Enums\OperationType;
 use Roster\Models\Availability;
-use Roster\Models\Schedule;
 use Roster\Services\Core\AbstractService;
 use Roster\Validation\Exceptions\ValidationFailedException;

@@ @@
             if ($slot !== null) {
                 $availableSlots->push($slot);
             }
+
             $currentDate->addDay();
         }

@@ @@
         ?string $type = null,
         ?Carbon $searchStart = null
     ): ?array {
-        /** @var \Illuminate\Support\Collection<\Roster\Models\Availability> $availabilities */
+        /** @var Collection<Availability> $availabilities */
         $availabilities = $this->getAvailabilityRepository()->getForDate(
             $this->schedulable,
             $day,
    ----------- end diff -----------

Applied rules:
 * NewlineAfterStatementRector


8) /home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/AvailabilityOverlapRule.php:19

    ---------- begin diff ----------
@@ @@
 )]
 class AvailabilityOverlapRule extends AbstractRule
 {
-    public function __construct() {}
-
     public function validate(ValidationContextInterface $validationContext): void
     {
         $operationType = $validationContext->getOperation();
    ----------- end diff -----------

Applied rules:
 * RemoveEmptyClassMethodRector


9) /home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/ScheduleOverlapRule.php:4

    ---------- begin diff ----------
@@ @@

 namespace Roster\Validation\Rules;

+use Exception;
 use Roster\Domain\Services\TemporalConflictService;
 use Illuminate\Support\Carbon;
 use Roster\Contracts\Validation\ValidationContextInterface;
@@ @@
 class ScheduleOverlapRule extends AbstractRule
 {
     public function __construct(
-        private TemporalConflictService $conflictService
+        private TemporalConflictService $temporalConflictService
     ) {}

     public function validate(ValidationContextInterface $validationContext): void
@@ @@
                 }
             }

-            $conflictResult = $this->conflictService->checkAllConflicts(
+            $conflictResult = $this->temporalConflictService->checkAllConflicts(
                 availabilityId: $availabilityId,
                 start: $start,
                 end: $end,
@@ @@
                     $conflictResult->message
                 );
             }
-        } catch (\Exception $exception) {
+        } catch (Exception $exception) {
             report($exception);
         }
     }
    ----------- end diff -----------

Applied rules:
 * RenamePropertyToMatchTypeRector


10) /home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/TimeRangeRule.php:5

    ---------- begin diff ----------
@@ @@
 namespace Roster\Validation\Rules;

 use Roster\Models\Availability;
-use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
 use Exception;
 use Illuminate\Support\Carbon;
 use Roster\Contracts\Validation\ValidationContextInterface;
@@ @@
 {
     public function validate(ValidationContextInterface $validationContext): void
     {
-        $operation = $validationContext->getOperation();
+        $validationContext->getOperation();
         $currentEntity = $validationContext->getCurrentEntity();

         try {
@@ @@
             $end = $this->getDateTimeValue($validationContext, 'end_datetime', $currentEntity);

             // Si les deux dates sont absentes, pas de validation
-            if ($start === null && $end === null) {
+            if (!$start instanceof Carbon && !$end instanceof Carbon) {
                 return;
             }

@@ @@
                 return; // AvailabilityOwnershipRule devrait déjà avoir échoué
             }

-            $this->validateTimeRange($validationContext, $availability, $start, $end, $currentEntity);
+            $this->validateTimeRange($validationContext, $availability, $start, $end);
         } catch (Exception $exception) {
             // La validation de format est gérée par d'autres règles
         }
@@ @@
             if ($value === null) {
                 return null;
             }
+
             try {
                 return Carbon::parse($value);
             } catch (Exception $e) {
@@ @@
             if ($value === null) {
                 return null;
             }
+
             try {
                 return $value instanceof Carbon ? $value : Carbon::parse($value);
             } catch (Exception $e) {
@@ @@
         ValidationContextInterface $validationContext,
         Availability $availability,
         ?Carbon $start,
-        ?Carbon $end,
-        ?object $currentEntity
+        ?Carbon $end
     ): void {
         /**
          * 1. Vérifie que les deux dates sont présentes pour certaines validations
          */
-        if ($start !== null && $end !== null) {
-            // Vérification de cohérence start < end
-            if ($start->gte($end)) {
-                $validationContext->setViolation(
-                    'end_datetime',
-                    'The end datetime must be after the start datetime'
-                );
-            }
+        // Vérification de cohérence start < end
+        if ($start instanceof Carbon && $end instanceof Carbon && $start->gte($end)) {
+            $validationContext->setViolation(
+                'end_datetime',
+                'The end datetime must be after the start datetime'
+            );
         }

         /**
          * 2. Validation pour la date de début si fournie
          */
-        if ($start !== null) {
+        if ($start instanceof Carbon) {
             $this->validateStartDateTime($validationContext, $availability, $start);
         }

@@ @@
         /**
          * 3. Validation pour la date de fin si fournie
          */
-        if ($end !== null) {
+        if ($end instanceof Carbon) {
             $this->validateEndDateTime($validationContext, $availability, $end, $start);
         }
     }
@@ @@
         /**
          * 3. Si start est fourni, vérifie que end est le même jour ou après
          */
-        if ($start !== null) {
+        if ($start instanceof Carbon) {
             // Vérifie que end n'est pas avant start
             if ($end->lte($start)) {
                 $validationContext->setViolation(
@@ @@

             // Vérifie que si start est un jour permis, end ne dépasse pas minuit du jour suivant
             // (pour empêcher les événements qui traversent minuit)
-            if (!$start->isSameDay($end) && $availabilityEndTime->format('H:i') === '00:00') {
-                // Si la disponibilité finit à minuit, vérifier que l'événement ne traverse pas minuit
-                if ($end->copy()->startOfDay()->gt($start->copy()->startOfDay())) {
-                    $validationContext->setViolation(
-                        'end_datetime',
-                        'Events cannot span across midnight when availability ends at 00:00'
-                    );
-                }
+            // Si la disponibilité finit à minuit, vérifier que l'événement ne traverse pas minuit
+            if (!$start->isSameDay($end) && $availabilityEndTime->format('H:i') === '00:00' && $end->copy()->startOfDay()->gt($start->copy()->startOfDay())) {
+                $validationContext->setViolation(
+                    'end_datetime',
+                    'Events cannot span across midnight when availability ends at 00:00'
+                );
             }
         }
     }
    ----------- end diff -----------

Applied rules:
 * FlipTypeControlToUseExclusiveTypeRector
 * CombineIfRector
 * NewlineAfterStatementRector
 * RemoveUnusedVariableAssignRector
 * RemoveUnusedPrivateMethodParameterRector
 * RenameVariableToMatchMethodCallReturnTypeRector


11) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Integration/Traits/BelongsToSchedulableTest.php:7

    ---------- begin diff ----------
@@ @@
 use Roster\Validation\Exceptions\ValidationFailedException;
 use Illuminate\Foundation\Testing\RefreshDatabase;
 use Roster\Exceptions\ForbiddenModelMutationException;
-use Roster\Exceptions\InvalidServiceContextException;
 use Roster\Facades\Availability;
 use Roster\Facades\Schedule;
 use Roster\Facades\Impediment;
    ----------- end diff -----------

Applied rules:


12) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Domain/RepositoryMutationTest.php:17

    ---------- begin diff ----------
@@ @@
 use Roster\Exceptions\MissingOwnerException;
 use Roster\Exceptions\MissingSchedulableException;
 use Tests\TestCase;
-use Illuminate\Support\Carbon;

 final class RepositoryMutationTest extends TestCase
 {
    ----------- end diff -----------

Applied rules:


 [OK] 12 files would have been changed (dry-run) by Rector                                                              

