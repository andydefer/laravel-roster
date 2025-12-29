# Rector Refactoring Report
*Generated: lun. 29 déc. 2025 18:48:48 WAT*


52 files with changes
=====================

1) /home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/FutureDateRule.php:221

    ---------- begin diff ----------
@@ @@

     /**
      * Combines date and time strings into a Carbon instance.
-     *
-     * @param Carbon $date
-     * @param string|null $time
-     * @return Carbon
      */
     private function combineDateAndTime(Carbon $date, ?string $time): Carbon
     {
@@ @@

     /**
      * Gets current daily_start from database (for update operations).
-     *
-     * @param ValidationContextInterface $validationContext
-     * @return string|null
      */
     private function getCurrentDailyStart(ValidationContextInterface $validationContext): ?string
     {
    ----------- end diff -----------

Applied rules:
 * RemoveUselessParamTagRector
 * RemoveUselessReturnTagRector


2) /home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/TimeRangeRule.php:45

    ---------- begin diff ----------
@@ @@
             $this->validateSingleDayEvent($context, $startDatetime, $endDatetime);

             $availability = $this->resolveAvailability($context);
-            if ($availability === null) {
+            if (!$availability instanceof Availability) {
                 return;
             }
    ----------- end diff -----------

Applied rules:
 * FlipTypeControlToUseExclusiveTypeRector


3) /home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/TimezoneValidationRule.php:39

    ---------- begin diff ----------
@@ @@
      * Validates timezone and datetime fields in the validation context.
      *
      * @param ValidationContextInterface $validationContext The context containing data to validate
-     * @return void
      */
     public function validate(ValidationContextInterface $validationContext): void
     {
    ----------- end diff -----------

Applied rules:
 * RemoveUselessReturnTagRector


4) /home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/ValidationResult.php:72

    ---------- begin diff ----------
@@ @@
     public function toArray(bool $includeRuleDescriptions = false): array
     {
         $violationsArray = array_map(
-            function (ViolationData $violation) use ($includeRuleDescriptions) {
+            function (ViolationData $violation) use ($includeRuleDescriptions): array {
                 $data = [
                     'field' => $violation->getField(),
                     'rule' => $violation->getRule(),
    ----------- end diff -----------

Applied rules:
 * ClosureReturnTypeRector


5) /home/andy-kani/pro/sites/packages/laravel-roster/src/helpers.php:77

    ---------- begin diff ----------
@@ @@
      */
     function roster_format_period_days_for_display(array $days): string
     {
-        if (empty($days)) {
+        if ($days === []) {
             return '';
         }

@@ @@
      */
     function roster_format_days_for_display(array $days): string
     {
-        if (empty($days)) {
+        if ($days === []) {
             return '';
         }

@@ @@
      * Creates an Availability service instance for a given schedulable model.
      *
      * @param Model $model The schedulable model instance
-     * @return AvailabilityService
      * @throws BindingResolutionException If the service cannot be resolved from the container
      */
     function availability_for(Model $model): AvailabilityService
@@ @@
      * Automatically extracts the schedulable from the availability's polymorphic relationship.
      *
      * @param Availability $availability The availability model instance
-     * @return ImpedimentService
      * @throws InvalidArgumentException If the availability has no schedulable relationship
      * @throws BindingResolutionException If the service cannot be resolved from the container
      */
@@ @@
      * Automatically extracts the schedulable from the availability's polymorphic relationship.
      *
      * @param Availability $availability The availability model instance
-     * @return ScheduleService
      * @throws InvalidArgumentException If the availability has no schedulable relationship
      * @throws BindingResolutionException If the service cannot be resolved from the container
      */
@@ @@
             $days
         );

-        for ($index = 0; $index < count($dayIndices) - 1; $index++) {
+        for ($index = 0; $index < count($dayIndices) - 1; ++$index) {
             $currentIndex = $dayIndices[$index];
             $nextIndex = $dayIndices[$index + 1];
    ----------- end diff -----------

Applied rules:
 * SimplifyEmptyCheckOnEmptyArrayRector
 * PostIncDecToPreIncDecRector
 * RemoveUselessReturnTagRector


6) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Feature/Integration/CompleteRosterIntegrationTest.php:954

    ---------- begin diff ----------
@@ @@
         foreach ($createdSchedules as $createdSchedule) {
             $scheduleAvailability = $availabilityByScheduleId[$createdSchedule->id] ?? null;

-            if ($scheduleAvailability) {
+            if ($scheduleAvailability instanceof AvailabilityModel) {
                 try {
                     schedule_for($scheduleAvailability)->delete($createdSchedule->id);
                 } catch (Exception $e) {
@@ @@
     /**
      * Create multiple impediments for a given availability.
      *
-     * @param AvailabilityModel $availability
      * @return array<int, ImpedimentModel>
      */
     private function createImpedimentsForAvailability(AvailabilityModel $availability): array
@@ @@
     /**
      * Create multiple schedules for a given availability.
      *
-     * @param AvailabilityModel $availability
      * @param array<int, AvailabilityModel> $availabilityByScheduleId
      * @return array<int, ScheduleModel>
      */
    ----------- end diff -----------

Applied rules:
 * FlipTypeControlToUseExclusiveTypeRector
 * NullableCompareToNullRector
 * RemoveUselessParamTagRector


7) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Integration/Database/AvailabilityIntegrationTest.php:4

    ---------- begin diff ----------
@@ @@

 namespace Integration\Database;

-use PHPUnit\Framework\Attributes\Group;
 use Illuminate\Foundation\Testing\RefreshDatabase;
 use Illuminate\Support\Collection;
 use Roster\Models\Availability as AvailabilityModel;
    ----------- end diff -----------

Applied rules:


8) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Integration/Database/ImpedimentIntegrationTest.php:287

    ---------- begin diff ----------
@@ @@

         $trashed = ImpedimentModel::withTrashed()->find(id: $impediment->id);
         $this->assertNotNull($trashed);
-        $this->assertNotNull($trashed->deleted_at);
+        $this->assertInstanceOf(Carbon::class, $trashed->deleted_at);
     }

     /**
    ----------- end diff -----------

Applied rules:
 * AssertEmptyNullableObjectToAssertInstanceofRector


9) /home/andy-kani/pro/sites/packages/laravel-roster/src/Casts/TimezoneAwareDateTimeCast.php:4

    ---------- begin diff ----------
@@ @@

 namespace Roster\Casts;

-use Carbon\CarbonTimeZone;
+use Illuminate\Database\Eloquent\Model;
 use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
 use Illuminate\Support\Carbon;
 use Roster\Domain\Helpers\TimezoneHelper;
@@ @@
     /**
      * Convert the stored UTC datetime to the user's timezone.
      *
-     * @param \Illuminate\Database\Eloquent\Model $model
-     * @param string $key
+     * @param Model $model
      * @param mixed $value The UTC datetime string from database
-     * @param array $attributes
      * @return Carbon|null Carbon instance in user timezone or null
      */
     public function get($model, string $key, $value, array $attributes): ?Carbon
@@ @@
     /**
      * Convert the datetime value to UTC format for database storage.
      *
-     * @param \Illuminate\Database\Eloquent\Model $model
-     * @param string $key
+     * @param Model $model
      * @param mixed $value Carbon instance or datetime string
-     * @param array $attributes
      * @return string|null UTC datetime string in 'Y-m-d H:i:s' format or null
      */
     public function set($model, string $key, $value, array $attributes): ?string
    ----------- end diff -----------

Applied rules:
 * RemoveUselessParamTagRector


10) /home/andy-kani/pro/sites/packages/laravel-roster/src/Commands/DebugRulesCommand.php:4

    ---------- begin diff ----------
@@ @@

 namespace Roster\Commands;

+use ValueError;
+use Exception;
+use Roster\Validation\Attributes\ValidationRule;
+use ReflectionType;
 use Illuminate\Console\Command;
-use Illuminate\Database\Eloquent\Model;
 use Roster\Enums\EntityType;
 use Roster\Enums\OperationType;
 use Roster\Validation\RuleScanner;
@@ @@
             }

             return self::SUCCESS;
-        } catch (Throwable $exception) {
-            $this->error($exception->getMessage());
+        } catch (Throwable $throwable) {
+            $this->error($throwable->getMessage());

             if ($this->option('verbose')) {
-                $this->error($exception->getTraceAsString());
+                $this->error($throwable->getTraceAsString());
             }

             return self::FAILURE;
@@ @@
     ): void {
         $entityType = $this->resolveEntityType($entityInput);

-        $this->line("🔍 Debugging validation rules for: {$entityInput}");
+        $this->line('🔍 Debugging validation rules for: ' . $entityInput);
         $this->line("📊 Entity Type: " . $entityType->value);
         $this->newLine();

@@ @@
             validator: $validator
         );

-        if (empty($rules)) {
+        if ($rules === []) {
             $this->warn('No validation rules found for this entity/operation combination.');
             return;
         }
@@ @@
     {
         try {
             return EntityType::from(strtolower($input));
-        } catch (\ValueError) {
-            $this->warn("Entity '{$input}' not found in EntityType enum. Using AVAILABILITY as default.");
+        } catch (ValueError) {
+            $this->warn(sprintf("Entity '%s' not found in EntityType enum. Using AVAILABILITY as default.", $input));
             return EntityType::AVAILABILITY;
         }
     }
@@ @@
             : $supportedOperations;

         foreach ($operations as $operation) {
-            if (!$operation instanceof OperationType) {
-                continue;
-            }
-
             // Skip RETRIEVE operation as validation rules don't apply to read operations
             if ($operation === OperationType::RETRIEVE) {
                 continue;
@@ @@
         EntityType $entityType,
         ?string $operationFilter
     ): void {
-        $this->line("📋 Rules for {$entityType->value}" .
-            ($operationFilter ? " (Operation: {$operationFilter})" : ""));
+        $this->line('📋 Rules for ' . $entityType->value .
+            ($operationFilter ? sprintf(' (Operation: %s)', $operationFilter) : ""));
         $this->newLine();

         $groupedRules = $this->groupRulesByClassName($rules);
@@ @@
         $sortedRules = $this->sortRulesByPriority($groupedRules);
         $filteredRules = $this->filterRulesByProperty($sortedRules, $propertyFilter);

-        if (empty($filteredRules)) {
+        if ($filteredRules === []) {
             $this->warn("No rules match the specified filters.");
             return;
         }
@@ @@
     private function formatRuleProperties(object $rule): string
     {
         $properties = $this->extractRuleProperties($rule);
-        return !empty($properties) ? implode(', ', $properties) : '(class-level)';
+        return $properties === [] ? '(class-level)' : implode(', ', $properties);
     }

     /**
@@ @@
             }

             return array_unique($properties);
-        } catch (ReflectionException $exception) {
+        } catch (ReflectionException $reflectionException) {
             $this->warn("Could not analyze properties for rule: " . get_class($rule));
             return [];
         }
@@ @@
             $startLine = max(0, $method->getStartLine() - 1);
             $endLine = $method->getEndLine();

-            for ($i = $startLine; $i < $endLine && $i < count($methodSource); $i++) {
+            for ($i = $startLine; $i < $endLine && $i < count($methodSource); ++$i) {
                 $line = $methodSource[$i];
                 $properties = array_merge(
                     $properties,
@@ @@
             }

             return $properties;
-        } catch (\Exception) {
+        } catch (Exception) {
             return [];
         }
     }
@@ @@
         try {
             $reflection = new ReflectionClass($rule);

-            if (!empty($reflection->getAttributes(\Roster\Validation\Attributes\ValidationRule::class))) {
+            if ($reflection->getAttributes(ValidationRule::class) !== []) {
                 return 'Attribute';
             }

@@ @@
      */
     private function displayRuleMethodDetails(object $rule, array $operations): void
     {
-        $this->line("Rule: " . $rule->getName() . " (Priority: {$rule->getPriority()})");
+        $this->line("Rule: " . $rule->getName() . sprintf(' (Priority: %s)', $rule->getPriority()));
         $this->line("Operations: " . implode(', ', $operations));
         $this->line("Class: " . get_class($rule));

@@ @@
                     $this->displayMethodInfo($method);
                 }
             }
-        } catch (ReflectionException $exception) {
+        } catch (ReflectionException $reflectionException) {
             $this->warn("    Could not analyze methods for: " . get_class($rule));
         }

@@ @@
      */
     private function displayMethodInfo(ReflectionMethod $method): void
     {
-        $this->line("  📝 Method: {$method->getName()}()");
-        $this->line("    📍 File: {$method->getFileName()}:{$method->getStartLine()}");
+        $this->line(sprintf('  📝 Method: %s()', $method->getName()));
+        $this->line(sprintf('    📍 File: %s:%s', $method->getFileName(), $method->getStartLine()));

         $parameters = $this->extractMethodParameters($method);
-        if (!empty($parameters)) {
+        if ($parameters !== []) {
             $this->line("    🔧 Params: " . implode(', ', $parameters));
         }
     }
@@ @@
         $parameters = [];

         foreach ($method->getParameters() as $parameter) {
-            $type = $parameter->getType() ? $parameter->getType()->getName() : 'mixed';
-            $parameters[] = "{$type} \${$parameter->getName()}";
+            $type = $parameter->getType() instanceof ReflectionType ? $parameter->getType()->getName() : 'mixed';
+            $parameters[] = sprintf('%s $%s', $type, $parameter->getName());
         }

         return $parameters;
@@ @@
     /**
      * Display all scanned rules.
      *
-     * @param array $scannedRules Scanned rules data
+     * @param array<string, ValidationRule> $scannedRules Scanned rules data
      */
     private function displayScannedRulesTable(array $scannedRules): void
     {
@@ @@
      * @param string $className Rule class name
      * @param object $ruleData Rule data object
      * @param int $index Row index
-     * @return array Table row data
+     * @return array<int, mixed> Table row data
      */
     private function createScannedRuleRow(string $className, object $ruleData, int $index): array
     {
@@ @@
      * Get entity values from scanned rule data.
      *
      * @param object $ruleData Scanned rule data
-     * @return array Entity values
+     * @return string[] Entity values
      */
     private function getEntityValuesFromScannedRule(object $ruleData): array
     {
    ----------- end diff -----------

Applied rules:
 * SimplifyEmptyCheckOnEmptyArrayRector
 * ExplicitBoolCompareRector
 * SwitchNegatedTernaryRector
 * CatchExceptionNameMatchingTypeRector
 * EncapsedStringsToSprintfRector
 * PostIncDecToPreIncDecRector
 * RemoveDeadInstanceOfRector
 * DisallowedEmptyRuleFixerRector
 * DocblockReturnArrayFromDirectArrayInstanceRector
 * ClassMethodArrayDocblockParamFromLocalCallsRector
 * AddReturnArrayDocblockBasedOnArrayMapRector


11) /home/andy-kani/pro/sites/packages/laravel-roster/src/Domain/Helpers/TimezoneHelper.php:4

    ---------- begin diff ----------
@@ @@

 namespace Roster\Domain\Helpers;

+use Exception;
 use Illuminate\Support\Carbon;
 use DateTimeZone;
 use InvalidArgumentException;
@@ @@
 final class TimezoneHelper
 {
     private static ?string $defaultTimezone = null;
+
     private static ?string $userTimezone = null;
+
     private const SYSTEM_TIMEZONE = 'UTC';
+
     private static bool $initialized = false;

     /**
@@ @@
         }

         if (!self::isValidTimezone($configValue)) {
-            throw new InvalidArgumentException("Invalid timezone configured: {$configValue}");
+            throw new InvalidArgumentException('Invalid timezone configured: ' . $configValue);
         }

         self::$defaultTimezone = self::normalizeTimezone($configValue);
@@ @@

         if ($timezone !== null) {
             if (!self::isValidTimezone($timezone)) {
-                throw new InvalidArgumentException("Invalid user timezone: {$timezone}");
+                throw new InvalidArgumentException('Invalid user timezone: ' . $timezone);
             }
+
             $timezone = self::normalizeTimezone($timezone);
         }

@@ @@
      */
     public static function isValidTimezone(string $timezone): bool
     {
-        if (empty($timezone)) {
+        if ($timezone === '' || $timezone === '0') {
             return false;
         }

@@ @@
         try {
             new DateTimeZone($timezone);
             return true;
-        } catch (\Exception) {
+        } catch (Exception) {
             return false;
         }
     }
@@ @@
     public static function normalizeTimezone(string $timezone): string
     {
         $all = DateTimeZone::listIdentifiers();
-        $key = array_search(strtolower($timezone), array_map('strtolower', $all));
+        $key = array_search(strtolower($timezone), array_map('strtolower', $all), true);
         return $key !== false ? $all[$key] : self::SYSTEM_TIMEZONE;
     }
    ----------- end diff -----------

Applied rules:
 * NewlineBetweenClassLikeStmtsRector
 * EncapsedStringsToSprintfRector
 * StrictArraySearchRector
 * NewlineAfterStatementRector
 * DisallowedEmptyRuleFixerRector


12) /home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Impediment.php:4

    ---------- begin diff ----------
@@ @@

 namespace Roster\Models;

+use InvalidArgumentException;
 use Illuminate\Database\Eloquent\Casts\Attribute;
 use Illuminate\Database\Eloquent\Model;
 use Illuminate\Database\Eloquent\Relations\BelongsTo;
@@ @@
                 if ($value === null) {
                     return null;
                 }
+
                 return is_string($value) ? json_decode($value, true, 512, JSON_THROW_ON_ERROR) : $value;
             },
             set: function ($value): ?string {
@@ @@
                 if ($value === null) {
                     return null;
                 }
+
                 return is_array($value) ? json_encode($value, JSON_THROW_ON_ERROR) : $value;
             }
         );
@@ @@
      * @param Carbon $end End time of the period to check
      * @return bool True if there is any overlap
      *
-     * @throws \InvalidArgumentException When the time window is not valid
+     * @throws InvalidArgumentException When the time window is not valid
      */
     public function overlapsWith(Carbon $start, Carbon $end): bool
     {
    ----------- end diff -----------

Applied rules:
 * NewlineAfterStatementRector


13) /home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractService.php:192

    ---------- begin diff ----------
@@ @@
                 $this->getEntityTypeEnum()
             );
         }
+
         $deleteData = [
             'id' => $id,
             'schedulable_id' => $entity->schedulable_id ?? $this->schedulable->id,
    ----------- end diff -----------

Applied rules:
 * NewlineAfterStatementRector
 * DocblockGetterReturnArrayFromPropertyDocblockVarRector
 * DocblockVarArrayFromGetterReturnRector


14) /home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Cache/RuleCacheGenerator.php:148

    ---------- begin diff ----------
@@ @@
         foreach ($rules as $className => $validationRule) {
             $body .= $this->buildRuleEntry($className, $validationRule);
         }
+
         return $body;
     }

@@ @@
      */
     private function buildRuleEntry(string $className, ValidationRule $validationRule): string
     {
-        $entities = $this->extractEnumValues($validationRule->entities, EntityType::class);
-        $operations = $this->extractEnumValues($validationRule->operations, OperationType::class);
+        $entities = $this->extractEnumValues($validationRule->entities);
+        $operations = $this->extractEnumValues($validationRule->operations);

         $indent = '    ';
         $entry = $indent . "'" . addslashes($className) . "' => [\n";
@@ @@
      * Extracts string values from enum arrays.
      *
      * @param array<EntityType|OperationType> $enums Array of enum instances
-     * @param string $enumClass The enum class for type hinting
      * @return array<string> Array of string values
      */
-    private function extractEnumValues(array $enums, string $enumClass): array
+    private function extractEnumValues(array $enums): array
     {
         return array_map(
-            fn($enum): string => $enum->value,
+            fn(EntityType|OperationType $enum): string => $enum->value,
             $enums
         );
     }
    ----------- end diff -----------

Applied rules:
 * NewlineAfterStatementRector
 * RemoveUnusedPrivateMethodParameterRector
 * AddArrayFunctionClosureParamTypeRector


15) /home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Context/ValidationContext.php:370

    ---------- begin diff ----------
@@ @@
     ): void {
         $this->violations[] = new ViolationData(
             field: $field,
+            message: $message,
             rule: $rule,
-            message: $message,
             ruleDescription: $ruleDescription
         );
     }
@@ @@
     ): void {
         $this->violations[] = new ViolationData(
             field: $field,
+            message: $message,
             rule: $rule->getName(),
-            message: $message,
             ruleDescription: $rule->getDescription()
         );
     }
    ----------- end diff -----------

Applied rules:
 * SortNamedParamRector


16) /home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Exceptions/ValidationFailedException.php:116

    ---------- begin diff ----------
@@ @@
     public function toArray(): array
     {
         $violationsArray = array_map(
-            fn(ViolationData $violation) => [
+            fn(ViolationData $violation): array => [
                 'field' => $violation->getField(),
                 'rule' => $violation->getRule(),
                 'message' => $violation->getMessage(),
@@ @@
     public function toDetailedArray(): array
     {
         $violationsArray = array_map(
-            fn(ViolationData $violation) => $violation->toArray(),
+            fn(ViolationData $violation): array => $violation->toArray(),
             $this->violations
         );

@@ @@
         $latestViolations = $this->keepLatestViolationPerField($violations);

         $messages = array_map(
-            fn(ViolationData $violation) => $violation->getMessage(),
+            fn(ViolationData $violation): string => $violation->getMessage(),
             $latestViolations
         );

@@ @@
      * @param array<int, mixed> $violations
      * @return array<int, ViolationData>
      *
-     * @throws \InvalidArgumentException If an element is not a ViolationData instance
+     * @throws InvalidArgumentException If an element is not a ViolationData instance
      */
     private function keepLatestViolationPerField(array $violations): array
     {
@@ @@

         foreach ($violations as $violation) {
             if (!$violation instanceof ViolationData) {
-                throw new \InvalidArgumentException(
+                throw new InvalidArgumentException(
                     sprintf(
                         'Expected instance of ViolationData, got %s',
                         is_object($violation) ? get_class($violation) : gettype($violation)
    ----------- end diff -----------

Applied rules:
 * AddArrowFunctionReturnTypeRector


17) /home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/AvailabilityDaysCoherenceRule.php:72

    ---------- begin diff ----------
@@ @@
         }

         $days = $validationContext->get('days');
-
-        if ($days === null || $days === []) {
-            return false;
-        }
-
-        return true;
+        return $days !== null && $days !== [];
     }

     /**
@@ @@
     /**
      * Check if validity period is valid (start < end and parseable).
      *
-     * @param array $period Validity period with 'start' and 'end'
+     * @param array<string, mixed> $period Validity period with 'start' and 'end'
      * @return bool True if period is valid
      */
     private function isValidPeriod(array $period): bool
@@ @@
      *
      * @param ValidationContextInterface $validationContext Validation context
      * @param array $days Days to check
-     * @param array $period Validity period
+     * @param array<string, mixed> $period Validity period
      */
     private function checkDaysWithinPeriod(
         ValidationContextInterface $validationContext,
    ----------- end diff -----------

Applied rules:
 * SimplifyDeMorganBinaryRector
 * SimplifyIfReturnBoolRector
 * AddParamArrayDocblockFromDimFetchAccessRector


18) /home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/AvailabilityTemporalCoherenceRule.php:126

    ---------- begin diff ----------
@@ @@
      *
      * @param ValidationContextInterface $validationContext Validation context
      * @param Availability $availability Original availability
-     * @return array Normalized update data
+     * @return array<string, string|mixed[]|null> Normalized update data
      */
     private function extractUpdateData(ValidationContextInterface $validationContext, Availability $availability): array
     {
@@ @@
      */
     private function hasRelevantChanges(array $updateData): bool
     {
-        return !empty(array_filter($updateData, fn($value) => $value !== null));
+        return array_filter($updateData, fn($value): bool => $value !== null) !== [];
     }

     /**
@@ @@
      *
      * @param string $entityClass Entity class to validate against
      * @param Availability $availability Availability being modified
-     * @param array $updateData Normalized update data
+     * @param array<string, mixed> $updateData Normalized update data
      * @param ValidationContextInterface $validationContext Validation context
      * @param Carbon $referenceTime Reference time for "future" determination
      */
@@ @@
      * Validate date boundaries for a specific entity.
      *
      * @param object $entity Existing entity to check
-     * @param array $updateData Normalized update data
+     * @param array<string, mixed> $updateData Normalized update data
      * @param string $entityClass Entity class name
      * @param ValidationContextInterface $validationContext Validation context
      */
@@ @@
     /**
      * Check if specific days are missing from new days array.
      *
-     * @param array $entityDays Days used by the entity
+     * @param string[] $entityDays Days used by the entity
      * @param array $newDays New days array
      * @param object $entity Existing entity
      * @param string $entityClass Entity class name
@@ @@
      */
     private function extractDaysFromPeriod(?Carbon $start, ?Carbon $end): array
     {
-        if ($start === null || $end === null || $end->lt($start)) {
+        if (!$start instanceof Carbon || !$end instanceof Carbon || $end->lt($start)) {
             return [];
         }
    ----------- end diff -----------

Applied rules:
 * FlipTypeControlToUseExclusiveTypeRector
 * DisallowedEmptyRuleFixerRector
 * AddParamArrayDocblockFromDimFetchAccessRector
 * DocblockReturnArrayFromDirectArrayInstanceRector
 * ClassMethodArrayDocblockParamFromLocalCallsRector
 * AddArrowFunctionReturnTypeRector


19) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/Rules/TimeRangeRuleTest.php:4

    ---------- begin diff ----------
@@ @@

 namespace Tests\Unit\Validation\Rules;

+use stdClass;
 use Exception;
 use Mockery;
 use Mockery\MockInterface;
@@ @@
 final class TimeRangeRuleTest extends TestCase
 {
     private AvailabilityService|MockInterface $availabilityService;
+
     private TimeRangeRule $rule;

     /**
@@ @@
             ->with(123)
             ->andReturn($availability);

-        $context->expects($this->any())
+        $context
             ->method('getAvailabilityService')
             ->willReturn($this->availabilityService);

@@ @@
             ->with(123)
             ->andReturn($availability);

-        $context->expects($this->any())
+        $context
             ->method('getAvailabilityService')
             ->willReturn($this->availabilityService);

@@ @@
             ->with(123)
             ->andReturn($availability);

-        $context->expects($this->any())
+        $context
             ->method('getAvailabilityService')
             ->willReturn($this->availabilityService);

@@ @@
             ->with(123)
             ->andReturn($availability);

-        $context->expects($this->any())
+        $context
             ->method('getAvailabilityService')
             ->willReturn($this->availabilityService);

@@ @@
             ->with(123)
             ->andReturn($availability);

-        $context->expects($this->any())
+        $context
             ->method('getAvailabilityService')
             ->willReturn($this->availabilityService);

@@ @@
             ->with(123)
             ->andReturn($availability);

-        $context->expects($this->any())
+        $context
             ->method('getAvailabilityService')
             ->willReturn($this->availabilityService);

@@ @@
             ->with(123)
             ->andReturn($availability);

-        $context->expects($this->any())
+        $context
             ->method('getAvailabilityService')
             ->willReturn($this->availabilityService);

@@ @@
             ->with(999)
             ->andReturn(null);

-        $context->expects($this->any())
+        $context
             ->method('getAvailabilityService')
             ->willReturn($this->availabilityService);

@@ @@
             ->with(123)
             ->andReturn($availability);

-        $context->expects($this->any())
+        $context
             ->method('getAvailabilityService')
             ->willReturn($this->availabilityService);

@@ @@
             ->with(123)
             ->andReturn($availability);

-        $context->expects($this->any())
+        $context
             ->method('getAvailabilityService')
             ->willReturn($this->availabilityService);

@@ @@
         );

         // Mock existing entity with datetime values
-        $entity = new \stdClass();
+        $entity = new stdClass();
         $entity->start_datetime = '2024-01-01 10:00:00';
         $entity->end_datetime = '2024-01-01 16:00:00';
         $entity->availability_id = 123;

-        $context->expects($this->any())
+        $context
             ->method('getCurrentEntity')
             ->willReturn($entity);

@@ @@
             ->with(123)
             ->andReturn($availability);

-        $context->expects($this->any())
+        $context
             ->method('getAvailabilityService')
             ->willReturn($this->availabilityService);

@@ @@

         return $context;
     }
+
     /**
      * Create an Availability model instance with test data.
      *
    ----------- end diff -----------

Applied rules:
 * NewlineBetweenClassLikeStmtsRector
 * RemoveExpectAnyFromMockRector


20) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/Rules/TimeSlotDateTimeRuleTest.php:5

    ---------- begin diff ----------
@@ @@
 namespace Tests\Unit\Validation\Rules;

 use Illuminate\Support\Carbon;
-use Illuminate\Database\Eloquent\Model;
 use Mockery;
-use Mockery\MockInterface;
 use Roster\Enums\EntityType;
 use Roster\Enums\OperationType;
 use Roster\Validation\Rules\TimeSlotDateTimeRule;
@@ @@
      * @param bool $hasEndDatetime Whether end_datetime is present
      * @param string|null $endDatetime The end datetime string or null
      * @param object|null $currentEntity The current entity for UPDATE operations
-     *
-     * @return MockObject&ValidationContextInterface
      */
     private function createValidationContext(
         OperationType $operationType,
@@ @@
      *
      * @param string|null $startDatetime The start datetime string or null
      * @param string|null $endDatetime The end datetime string or null
-     *
-     * @return object
      */
     private function createMockEntity(?string $startDatetime, ?string $endDatetime): object
     {
    ----------- end diff -----------

Applied rules:
 * RemoveUselessReturnTagRector


21) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/Rules/TimezoneValidationRuleTest.php:4

    ---------- begin diff ----------
@@ @@

 namespace Tests\Unit\Validation\Rules;

-use Carbon\Carbon;
 use Exception;
 use Mockery;
 use Mockery\MockInterface;
@@ @@
 final class TimezoneValidationRuleTest extends TestCase
 {
     private TimezoneValidationRule $rule;
+
     private Model|MockInterface $schedulable;

     /**
@@ @@
             // Configure get() method - shouldn't be called since has() returns false
             $context->method('get')->willReturnCallback(
                 function (string $key): mixed {
-                    $this->fail("get() should not be called when has() returns false for field '{$key}'");
+                    $this->fail(sprintf("get() should not be called when has() returns false for field '%s'", $key));
                 }
             );

@@ @@
             // Configure get() method - shouldn't be called since has() returns false
             $context->method('get')->willReturnCallback(
                 function (string $key): mixed {
-                    $this->fail("get() should not be called when has() returns false for field '{$key}'");
+                    $this->fail(sprintf("get() should not be called when has() returns false for field '%s'", $key));
                 }
             );

@@ @@
         // Mais on configure quand même has() pour être sûr
         $context->method('has')->willReturnCallback(
             function (string $key): bool {
-                $this->fail("has() should not be called for DELETE operation, but was called with field '{$key}'");
+                $this->fail(sprintf("has() should not be called for DELETE operation, but was called with field '%s'", $key));
             }
         );

@@ @@
                 $this->rule->validate($context);
                 $this->addToAssertionCount(1); // Test executed without exception
             } catch (Exception $exception) {
-                $this->fail("Validation threw exception for date format '{$dateFormat}': " . $exception->getMessage());
+                $this->fail(sprintf("Validation threw exception for date format '%s': ", $dateFormat) . $exception->getMessage());
             }
         }
     }
    ----------- end diff -----------

Applied rules:
 * NewlineBetweenClassLikeStmtsRector
 * EncapsedStringsToSprintfRector


22) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/ValidationContextTest.php:4

    ---------- begin diff ----------
@@ @@

 namespace Tests\Unit\Validation\Context;

-use Exception;
 use Illuminate\Foundation\Testing\RefreshDatabase;
 use Illuminate\Database\Eloquent\Model;
 use Illuminate\Support\Facades\Schema;
@@ @@
     use RefreshDatabase;

     private TestSchedulable $schedulable;
+
     private Availability $availability;

     /**
@@ @@
         $this->createTestTable('test_entity_for_update');
         $currentEntity = new class extends Model {
             protected $table = 'test_entity_for_update';
+
             protected $guarded = [];
+
             public $timestamps = false;

             protected $attributes = [
@@ @@
     public function test_resolves_owner_from_current_entity_with_availability_method(): void
     {
         // Arrange: Create entity with availability relationship
-        $this->createTestTable('test_entity_with_availability', function ($table) {
+        $this->createTestTable('test_entity_with_availability', function ($table): void {
             $table->foreignId('availability_id')->nullable()->constrained('roster_availabilities')->nullOnDelete();
         });

         $currentEntity = new class extends Model {
             protected $table = 'test_entity_with_availability';
+
             protected $guarded = [];
+
             public $timestamps = false;

             public function availability()
@@ @@
         $this->createTestTable('test_partial_updates');
         $currentEntity = new class extends Model {
             protected $table = 'test_partial_updates';
+
             protected $guarded = [];
+
             public $timestamps = false;

             protected $attributes = [
@@ @@

         $violation = $violations[0];
         $this->assertInstanceOf(ViolationData::class, $violation);
-        $this->assertEquals('test_field', $violation->getField());
-        $this->assertEquals('Test violation message', $violation->getMessage());
-        $this->assertEquals('TestRule', $violation->getRule());
-        $this->assertEquals('Test rule description', $violation->getRuleDescription());
+        $this->assertSame('test_field', $violation->getField());
+        $this->assertSame('Test violation message', $violation->getMessage());
+        $this->assertSame('TestRule', $violation->getRule());
+        $this->assertSame('Test rule description', $violation->getRuleDescription());
     }

     /**
@@ @@
         $this->assertIsArray($violations);
         $this->assertCount(2, $violations);

-        foreach ($violations as $violation) {
-            $this->assertInstanceOf(ViolationData::class, $violation);
-        }
+        $this->assertContainsOnlyInstancesOf(ViolationData::class, $violations);

         $this->assertEquals('field1', $violations[0]->getField());
         $this->assertEquals('field2', $violations[1]->getField());
@@ @@
     private function createTestTable(string $tableName, ?callable $callback = null): void
     {
         if (!Schema::hasTable($tableName)) {
-            Schema::create($tableName, function ($table) use ($callback) {
+            Schema::create($tableName, function ($table) use ($callback): void {
                 $table->id();
                 $table->string('name')->nullable();
                 $table->integer('count')->nullable();

-                if ($callback) {
+                if ($callback !== null) {
                     $callback($table);
                 }
             });
@@ @@
     {
         $entity = new class extends Model {
             protected $guarded = [];
+
             public $timestamps = false;
         };
    ----------- end diff -----------

Applied rules:
 * NewlineBetweenClassLikeStmtsRector
 * NullableCompareToNullRector
 * SimplifyForeachInstanceOfRector
 * AssertEqualsToSameRector
 * AddClosureVoidReturnTypeWhereNoReturnRector


23) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/ValidationResultTest.php:93

    ---------- begin diff ----------
@@ @@
         $this->assertTrue($result->isValid());
         $this->assertEmpty($result->getViolations());
         $this->assertFalse($result->hasViolations());
-        $this->assertEquals(0, $result->countViolations());
+        $this->assertSame(0, $result->countViolations());

         // Vérification du tableau
         $array = $result->toArray();
@@ @@
         $this->assertFalse($result->isValid());
         $this->assertSame($violations, $result->getViolations());
         $this->assertTrue($result->hasViolations());
-        $this->assertEquals(2, $result->countViolations());
+        $this->assertSame(2, $result->countViolations());

         // Vérification du tableau
         $array = $result->toArray();
@@ @@
         $result = ValidationResult::failed($violations);

         // Assert
-        $this->assertEquals(3, $result->countViolations());
+        $this->assertSame(3, $result->countViolations());
     }
 }
    ----------- end diff -----------

Applied rules:
 * AssertEqualsToSameRector


24) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/ValidatorTest.php:4

    ---------- begin diff ----------
@@ @@

 namespace Tests\Unit\Validation;

+use Roster\Validation\DTOs\ViolationData;
+use ReflectionClass;
 use Exception;
 use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
 use PHPUnit\Framework\MockObject\MockObject;
@@ @@
         // Assert: Verify both rules are registered by class name
         $this->assertTrue($validator->hasRule(TestRule1::class));
         $this->assertTrue($validator->hasRule(TestRule2::class));
-        $this->assertEquals(2, $validator->getRuleCount());
+        $this->assertSame(2, $validator->getRuleCount());
     }

     /**
@@ @@

         // Assert: Verify rule is registered and accessible by class name
         $this->assertTrue($this->validator->hasRule(CustomRule::class));
-        $this->assertEquals(1, $this->validator->getRuleCount());
+        $this->assertSame(1, $this->validator->getRuleCount());
     }

     /**
@@ @@
             {
                 return 'LowPriorityRule';
             }
+
             public function getPriority(): int
             {
                 return 10;
             }
+
             public function getDescription(): string
             {
                 return 'Low priority test rule';
             }
+
             public function supports(OperationType $operation, EntityType $entity): bool
             {
                 return $operation === OperationType::CREATE && $entity === EntityType::AVAILABILITY;
             }
+
             public function validate(ValidationContextInterface $context): void
             {
                 $this->executionOrder[] = 'LowPriorityRule';
@@ @@
             {
                 return 'MediumPriorityRule';
             }
+
             public function getPriority(): int
             {
                 return 50;
             }
+
             public function getDescription(): string
             {
                 return 'Medium priority test rule';
             }
+
             public function supports(OperationType $operation, EntityType $entity): bool
             {
                 return $operation === OperationType::CREATE && $entity === EntityType::AVAILABILITY;
             }
+
             public function validate(ValidationContextInterface $context): void
             {
                 $this->executionOrder[] = 'MediumPriorityRule';
@@ @@
             {
                 return 'HighPriorityRule';
             }
+
             public function getPriority(): int
             {
                 return 100;
             }
+
             public function getDescription(): string
             {
                 return 'High priority test rule';
             }
+
             public function supports(OperationType $operation, EntityType $entity): bool
             {
                 return $operation === OperationType::CREATE && $entity === EntityType::AVAILABILITY;
             }
+
             public function validate(ValidationContextInterface $context): void
             {
                 $this->executionOrder[] = 'HighPriorityRule';
@@ @@
         $context = $this->createMock(ValidationContextInterface::class);
         $context->method('getOperation')->willReturn(OperationType::CREATE);
         $context->method('getEntityType')->willReturn(EntityType::AVAILABILITY);
-        $context->method('hasViolations')->willReturnCallback(function () use (&$violations) {
-            return count($violations) > 0;
+        $context->method('hasViolations')->willReturnCallback(function () use (&$violations): bool {
+            return $violations !== [];
         });
-        $context->method('getViolations')->willReturnCallback(function () use (&$violations) {
+        $context->method('getViolations')->willReturnCallback(function () use (&$violations): array {
             return $violations;
         });

@@ @@
         $result = $validator->validate($context);

         // Assert: Rules should be sorted by priority (higher priority first)
-        $this->assertEquals(['HighPriorityRule', 'MediumPriorityRule', 'LowPriorityRule'], $executionOrder);
+        $this->assertSame(['HighPriorityRule', 'MediumPriorityRule', 'LowPriorityRule'], $executionOrder);
         $this->assertTrue($result->isValid());

         // Also verify all three rules are registered
@@ @@
             {
                 return 'PassingRule';
             }
+
             public function getPriority(): int
             {
                 return 50;
             }
+
             public function getDescription(): string
             {
                 return 'Test rule that always passes validation';
             }
+
             public function supports(OperationType $operation, EntityType $entity): bool
             {
                 return $operation === OperationType::CREATE && $entity === EntityType::SCHEDULE;
             }
+
             public function validate(ValidationContextInterface $context): void
             {
                 // No violation added
@@ @@
         $context = $this->createMock(ValidationContextInterface::class);
         $context->method('getOperation')->willReturn(OperationType::CREATE);
         $context->method('getEntityType')->willReturn(EntityType::SCHEDULE);
-        $context->method('hasViolations')->willReturnCallback(function () use (&$violations) {
-            return count($violations) > 0;
+        $context->method('hasViolations')->willReturnCallback(function () use (&$violations): bool {
+            return $violations !== [];
         });
-        $context->method('getViolations')->willReturnCallback(function () use (&$violations) {
+        $context->method('getViolations')->willReturnCallback(function () use (&$violations): array {
             return $violations;
         });
-        $context->method('setViolation')->willReturnCallback(function ($field, $message, $rule = null) use (&$violations) {
-            $violations[] = new \Roster\Validation\DTOs\ViolationData($field, $message, $rule);
+        $context->method('setViolation')->willReturnCallback(function (string $field, string $message, ?string $rule = null) use (&$violations): void {
+            $violations[] = new ViolationData($field, $message, $rule);
         });

         // Act: Execute validation
@@ @@
             {
                 return 'DescriptiveRule';
             }
+
             public function getPriority(): int
             {
                 return 50;
             }
+
             public function getDescription(): string
             {
                 return 'Rule that demonstrates setViolationFromRule usage';
             }
+
             public function supports(OperationType $operation, EntityType $entity): bool
             {
                 return $operation === OperationType::CREATE && $entity === EntityType::AVAILABILITY;
             }
+
             public function validate(ValidationContextInterface $context): void
             {
                 $context->setViolationFromRule($this, 'test_field', 'Test violation message');
@@ @@
         $context = $this->createMock(ValidationContextInterface::class);
         $context->method('getOperation')->willReturn(OperationType::CREATE);
         $context->method('getEntityType')->willReturn(EntityType::AVAILABILITY);
-        $context->method('hasViolations')->willReturnCallback(function () use (&$violations) {
-            return count($violations) > 0;
+        $context->method('hasViolations')->willReturnCallback(function () use (&$violations): bool {
+            return $violations !== [];
         });
-        $context->method('getViolations')->willReturnCallback(function () use (&$violations) {
+        $context->method('getViolations')->willReturnCallback(function () use (&$violations): array {
             return $violations;
         });
-        $context->method('setViolationFromRule')->willReturnCallback(function ($ruleArg, $field, $message) use (&$violations) {
-            $violations[] = new \Roster\Validation\DTOs\ViolationData(
+        $context->method('setViolationFromRule')->willReturnCallback(function ($ruleArg, string $field, string $message) use (&$violations): void {
+            $violations[] = new ViolationData(
                 $field,
                 $message,
                 $ruleArg->getName(),
@@ @@
             {
                 return 'ViolatingRule';
             }
+
             public function getPriority(): int
             {
                 return 50;
             }
+
             public function getDescription(): string
             {
                 return 'Test rule that always creates violations';
             }
+
             public function supports(OperationType $operation, EntityType $entity): bool
             {
                 return $operation === OperationType::CREATE && $entity === EntityType::SCHEDULE;
             }
+
             public function validate(ValidationContextInterface $context): void
             {
                 $context->setViolation('field1', 'Field is required');
@@ @@
         $context = $this->createMock(ValidationContextInterface::class);
         $context->method('getOperation')->willReturn(OperationType::CREATE);
         $context->method('getEntityType')->willReturn(EntityType::SCHEDULE);
-        $context->method('hasViolations')->willReturnCallback(function () use (&$violations) {
-            return count($violations) > 0;
+        $context->method('hasViolations')->willReturnCallback(function () use (&$violations): bool {
+            return $violations !== [];
         });
-        $context->method('getViolations')->willReturnCallback(function () use (&$violations) {
+        $context->method('getViolations')->willReturnCallback(function () use (&$violations): array {
             return $violations;
         });
-        $context->method('setViolation')->willReturnCallback(function ($field, $message, $rule = null) use (&$violations) {
-            $violations[] = new \Roster\Validation\DTOs\ViolationData($field, $message, $rule);
+        $context->method('setViolation')->willReturnCallback(function (string $field, string $message, ?string $rule = null) use (&$violations): void {
+            $violations[] = new ViolationData($field, $message, $rule);
         });

         // Act: Execute validation
@@ @@
             {
                 return 'BaseRule';
             }
+
             public function getPriority(): int
             {
                 return 50;
             }
+
             public function getDescription(): string
             {
                 return 'Base test rule';
             }
+
             public function supports(OperationType $operation, EntityType $entity): bool
             {
                 return $operation === OperationType::UPDATE && $entity === EntityType::IMPEDIMENT;
             }
+
             public function validate(ValidationContextInterface $context): void
             {
                 $context->setViolation('base', 'Base rule violation');
@@ @@
             {
                 return 'AdditionalRule';
             }
+
             public function getPriority(): int
             {
                 return 50;
             }
+
             public function getDescription(): string
             {
                 return 'Additional test rule';
             }
+
             public function supports(OperationType $operation, EntityType $entity): bool
             {
                 return $operation === OperationType::UPDATE && $entity === EntityType::IMPEDIMENT;
             }
+
             public function validate(ValidationContextInterface $context): void
             {
                 $context->setViolation('additional', 'Additional rule violation');
@@ @@
         $context = $this->createMock(ValidationContextInterface::class);
         $context->method('getOperation')->willReturn(OperationType::UPDATE);
         $context->method('getEntityType')->willReturn(EntityType::IMPEDIMENT);
-        $context->method('hasViolations')->willReturnCallback(function () use (&$violations) {
-            return count($violations) > 0;
+        $context->method('hasViolations')->willReturnCallback(function () use (&$violations): bool {
+            return $violations !== [];
         });
-        $context->method('getViolations')->willReturnCallback(function () use (&$violations) {
+        $context->method('getViolations')->willReturnCallback(function () use (&$violations): array {
             return $violations;
         });
-        $context->method('setViolation')->willReturnCallback(function ($field, $message, $rule = null) use (&$violations) {
-            $violations[] = new \Roster\Validation\DTOs\ViolationData($field, $message, $rule);
+        $context->method('setViolation')->willReturnCallback(function (string $field, string $message, ?string $rule = null) use (&$violations): void {
+            $violations[] = new ViolationData($field, $message, $rule);
         });

         // Act: Execute validation with additional rules
@@ @@
         $this->assertFalse($result->isValid());
         $this->assertCount(2, $result->getViolations());

-        $violationFields = array_map(fn($v) => $v->getField(), $result->getViolations());
+        $violationFields = array_map(fn(ViolationData $v): string => $v->getField(), $result->getViolations());
         $this->assertContains('base', $violationFields);
         $this->assertContains('additional', $violationFields);
     }
@@ @@
             {
                 return 'ExceptionRule';
             }
+
             public function getPriority(): int
             {
                 return 50;
             }
+
             public function getDescription(): string
             {
                 return 'Test rule that throws exception';
             }
+
             public function supports(OperationType $operation, EntityType $entity): bool
             {
                 return $operation === OperationType::CREATE && $entity === EntityType::SCHEDULE;
             }
+
             public function validate(ValidationContextInterface $context): void
             {
                 throw new Exception('Rule processing failed');
@@ @@
         $context = $this->createMock(ValidationContextInterface::class);
         $context->method('getOperation')->willReturn(OperationType::CREATE);
         $context->method('getEntityType')->willReturn(EntityType::SCHEDULE);
-        $context->method('hasViolations')->willReturnCallback(function () use (&$violations) {
-            return count($violations) > 0;
+        $context->method('hasViolations')->willReturnCallback(function () use (&$violations): bool {
+            return $violations !== [];
         });
-        $context->method('getViolations')->willReturnCallback(function () use (&$violations) {
+        $context->method('getViolations')->willReturnCallback(function () use (&$violations): array {
             return $violations;
         });
-        $context->method('setViolationFromRule')->willReturnCallback(function ($ruleArg, $field, $message) use (&$violations) {
-            $violations[] = new \Roster\Validation\DTOs\ViolationData(
+        $context->method('setViolationFromRule')->willReturnCallback(function ($ruleArg, string $field, string $message) use (&$violations): void {
+            $violations[] = new ViolationData(
                 $field,
                 $message,
                 $ruleArg->getName(),
@@ @@
             {
                 return 'AvailabilityRule';
             }
+
             public function getPriority(): int
             {
                 return 50;
             }
+
             public function getDescription(): string
             {
                 return 'Rule for availability entities';
             }
+
             public function supports(OperationType $operation, EntityType $entity): bool
             {
                 return $entity === EntityType::AVAILABILITY &&
                     ($operation === OperationType::CREATE || $operation === OperationType::UPDATE);
             }
+
             public function validate(ValidationContextInterface $context): void {}
         };

@@ @@
             {
                 return 'ScheduleRule';
             }
+
             public function getPriority(): int
             {
                 return 50;
             }
+
             public function getDescription(): string
             {
                 return 'Rule for schedule entities';
             }
+
             public function supports(OperationType $operation, EntityType $entity): bool
             {
                 return $entity === EntityType::SCHEDULE && $operation === OperationType::CREATE;
             }
+
             public function validate(ValidationContextInterface $context): void {}
         };

@@ @@
             {
                 return 'TestRule';
             }
+
             public function getPriority(): int
             {
                 return 50;
             }
+
             public function getDescription(): string
             {
                 return 'Generic test rule';
             }
+
             public function supports(OperationType $operation, EntityType $entity): bool
             {
                 return $operation === OperationType::CREATE && $entity === EntityType::AVAILABILITY;
             }
+
             public function validate(ValidationContextInterface $context): void {}
         };

@@ @@
             {
                 return 'Rule1';
             }
+
             public function getPriority(): int
             {
                 return 50;
             }
+
             public function getDescription(): string
             {
                 return 'First test rule';
             }
+
             public function supports(OperationType $operation, EntityType $entity): bool
             {
                 return true;
             }
+
             public function validate(ValidationContextInterface $context): void {}
         };

@@ @@
             {
                 return 'Rule2';
             }
+
             public function getPriority(): int
             {
                 return 50;
             }
+
             public function getDescription(): string
             {
                 return 'Second test rule';
             }
+
             public function supports(OperationType $operation, EntityType $entity): bool
             {
                 return true;
             }
+
             public function validate(ValidationContextInterface $context): void {}
         };

@@ @@
             {
                 return 'Rule3';
             }
+
             public function getPriority(): int
             {
                 return 50;
             }
+
             public function getDescription(): string
             {
                 return 'Third test rule';
             }
+
             public function supports(OperationType $operation, EntityType $entity): bool
             {
                 return true;
             }
+
             public function validate(ValidationContextInterface $context): void {}
         };

@@ @@
         // Assert: Verify all rules are returned
         $this->assertCount(3, $allRules);

-        $ruleNames = array_map(fn($rule) => $rule->getName(), $allRules);
+        $ruleNames = array_map(fn(RuleInterface $rule): string => $rule->getName(), $allRules);
         $this->assertContains('Rule1', $ruleNames);
         $this->assertContains('Rule2', $ruleNames);
         $this->assertContains('Rule3', $ruleNames);
@@ @@
             {
                 return 'Rule1';
             }
+
             public function getPriority(): int
             {
                 return 50;
             }
+
             public function getDescription(): string
             {
                 return 'First multiple rule';
             }
+
             public function supports(OperationType $operation, EntityType $entity): bool
             {
                 return $operation === OperationType::CREATE && $entity === EntityType::AVAILABILITY;
             }
+
             public function validate(ValidationContextInterface $context): void
             {
                 $context->setViolation('rule1', 'Violation from Rule1');
@@ @@
             {
                 return 'Rule2';
             }
+
             public function getPriority(): int
             {
                 return 50;
             }
+
             public function getDescription(): string
             {
                 return 'Second multiple rule';
             }
+
             public function supports(OperationType $operation, EntityType $entity): bool
             {
                 return $operation === OperationType::CREATE && $entity === EntityType::AVAILABILITY;
             }
+
             public function validate(ValidationContextInterface $context): void
             {
                 $context->setViolation('rule2', 'Violation from Rule2');
@@ @@
             {
                 return 'Rule3';
             }
+
             public function getPriority(): int
             {
                 return 50;
             }
+
             public function getDescription(): string
             {
                 return 'Third multiple rule';
             }
+
             public function supports(OperationType $operation, EntityType $entity): bool
             {
                 return $operation === OperationType::CREATE && $entity === EntityType::AVAILABILITY;
             }
+
             public function validate(ValidationContextInterface $context): void
             {
                 $context->setViolation('rule3', 'Violation from Rule3');
@@ @@
         $context = $this->createMock(ValidationContextInterface::class);
         $context->method('getOperation')->willReturn(OperationType::CREATE);
         $context->method('getEntityType')->willReturn(EntityType::AVAILABILITY);
-        $context->method('hasViolations')->willReturnCallback(function () use (&$violations) {
-            return count($violations) > 0;
+        $context->method('hasViolations')->willReturnCallback(function () use (&$violations): bool {
+            return $violations !== [];
         });
-        $context->method('getViolations')->willReturnCallback(function () use (&$violations) {
+        $context->method('getViolations')->willReturnCallback(function () use (&$violations): array {
             return $violations;
         });
-        $context->method('setViolation')->willReturnCallback(function ($field, $message, $rule = null) use (&$violations) {
-            $violations[] = new \Roster\Validation\DTOs\ViolationData($field, $message, $rule);
+        $context->method('setViolation')->willReturnCallback(function (string $field, string $message, ?string $rule = null) use (&$violations): void {
+            $violations[] = new ViolationData($field, $message, $rule);
         });

         // Act: Execute validation
@@ @@
     public function test_generates_correct_cache_keys_for_rule_indexing(): void
     {
         // Arrange: Use reflection to access private method
-        $reflection = new \ReflectionClass($this->validator);
+        $reflection = new ReflectionClass($this->validator);
         $method = $reflection->getMethod('createCacheKey');
         $method->setAccessible(true);

@@ @@
             {
                 return 'InvalidAttributeRule';
             }
+
             public function getPriority(): int
             {
                 return 50;
             }
+
             public function getDescription(): string
             {
                 return 'Rule with invalid attribute handling';
             }
+
             public function supports(OperationType $operation, EntityType $entity): bool
             {
                 return false;
             }
+
             public function validate(ValidationContextInterface $context): void {}
         };

@@ @@
             {
                 return 'CountRule1';
             }
+
             public function getPriority(): int
             {
                 return 50;
             }
+
             public function getDescription(): string
             {
                 return 'First count test rule';
             }
+
             public function supports(OperationType $operation, EntityType $entity): bool
             {
                 return true;
             }
+
             public function validate(ValidationContextInterface $context): void {}
         };

@@ @@
             {
                 return 'CountRule2';
             }
+
             public function getPriority(): int
             {
                 return 50;
             }
+
             public function getDescription(): string
             {
                 return 'Second count test rule';
             }
+
             public function supports(OperationType $operation, EntityType $entity): bool
             {
                 return true;
             }
+
             public function validate(ValidationContextInterface $context): void {}
         };

         // Act: Register rules and check counts
-        $this->assertEquals(0, $this->validator->getRuleCount());
+        $this->assertSame(0, $this->validator->getRuleCount());

         $this->validator->registerRule($rule1);
-        $this->assertEquals(1, $this->validator->getRuleCount());
+        $this->assertSame(1, $this->validator->getRuleCount());

         $this->validator->registerRule($rule2);
-        $this->assertEquals(2, $this->validator->getRuleCount());
+        $this->assertSame(2, $this->validator->getRuleCount());

         // Register same instance again (should increase count - duplicates are allowed)
         $this->validator->registerRule($rule1);
-        $this->assertEquals(3, $this->validator->getRuleCount());
+        $this->assertSame(3, $this->validator->getRuleCount());
     }

     /**
@@ @@
             {
                 return 'DuplicateRule';
             }
+
             public function getPriority(): int
             {
                 return 50;
             }
+
             public function getDescription(): string
             {
                 return 'Duplicate rule test';
             }
+
             public function supports(OperationType $operation, EntityType $entity): bool
             {
                 return true;
             }
+
             public function validate(ValidationContextInterface $context): void {}
         };

@@ @@
         $this->validator->registerRule($rule);

         // Assert: All registrations are counted
-        $this->assertEquals(3, $this->validator->getRuleCount());
+        $this->assertSame(3, $this->validator->getRuleCount());
         $this->assertTrue($this->validator->hasRule(get_class($rule)));
     }

@@ @@
             {
                 return 'ViolationDataRule';
             }
+
             public function getPriority(): int
             {
                 return 50;
             }
+
             public function getDescription(): string
             {
                 return 'Rule demonstrating ViolationData objects';
             }
+
             public function supports(OperationType $operation, EntityType $entity): bool
             {
                 return $operation === OperationType::CREATE && $entity === EntityType::AVAILABILITY;
             }
+
             public function validate(ValidationContextInterface $context): void
             {
                 $context->setViolation('field1', 'Required field', 'required');
@@ @@
         $context = $this->createMock(ValidationContextInterface::class);
         $context->method('getOperation')->willReturn(OperationType::CREATE);
         $context->method('getEntityType')->willReturn(EntityType::AVAILABILITY);
-        $context->method('hasViolations')->willReturnCallback(function () use (&$violations) {
-            return count($violations) > 0;
+        $context->method('hasViolations')->willReturnCallback(function () use (&$violations): bool {
+            return $violations !== [];
         });
-        $context->method('getViolations')->willReturnCallback(function () use (&$violations) {
+        $context->method('getViolations')->willReturnCallback(function () use (&$violations): array {
             return $violations;
         });
-        $context->method('setViolation')->willReturnCallback(function ($field, $message, $rule = null) use (&$violations) {
-            $violations[] = new \Roster\Validation\DTOs\ViolationData($field, $message, $rule);
+        $context->method('setViolation')->willReturnCallback(function (string $field, string $message, ?string $rule = null) use (&$violations): void {
+            $violations[] = new ViolationData($field, $message, $rule);
         });

         // Act: Execute validation
@@ @@
         $this->assertFalse($result->isValid());
         $violations = $result->getViolations();
         $this->assertCount(2, $violations);
-        $this->assertInstanceOf(\Roster\Validation\DTOs\ViolationData::class, $violations[0]);
-        $this->assertEquals('field1', $violations[0]->getField());
-        $this->assertEquals('required', $violations[0]->getRule());
+        $this->assertInstanceOf(ViolationData::class, $violations[0]);
+        $this->assertSame('field1', $violations[0]->getField());
+        $this->assertSame('required', $violations[0]->getRule());
     }
 }
    ----------- end diff -----------

Applied rules:
 * NewlineBetweenClassLikeStmtsRector
 * CountArrayToEmptyArrayComparisonRector
 * TypeWillReturnCallableArrowFunctionRector
 * AssertEqualsToSameRector
 * AddArrowFunctionReturnTypeRector
 * AddArrayFunctionClosureParamTypeRector


25) /home/andy-kani/pro/sites/packages/laravel-roster/tests/database/migrations/2024_01_01_000000_create_test_schedulables_table.php:18

    ---------- begin diff ----------
@@ @@
      * Run the migrations.
      *
      * Creates the test_schedulables table with necessary columns for testing.
-     *
-     * @return void
      */
     public function up(): void
     {
@@ @@
      * Reverse the migrations.
      *
      * Drops the test_schedulables table.
-     *
-     * @return void
      */
     public function down(): void
     {
    ----------- end diff -----------

Applied rules:
 * RemoveUselessReturnTagRector


26) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Support/TestSchedulable.php:14

    ---------- begin diff ----------
@@ @@
 class TestSchedulable extends Model
 {
     use HasRoster;
+
     /**
      * The table associated with the model.
      *
    ----------- end diff -----------

Applied rules:
 * NewlineBetweenClassLikeStmtsRector


27) /home/andy-kani/pro/sites/packages/laravel-roster/tests/TestCase.php:24

    ---------- begin diff ----------
@@ @@
 {
     /**
      * Set up the test environment.
-     *
-     * @return void
      */
     protected function setUp(): void
     {
@@ @@

     /**
      * Register observers for domain models.
-     *
-     * @return void
      */
     private function registerDomainObservers(): void
     {
@@ @@

     /**
      * Load package migrations.
-     *
-     * @return void
      */
     private function loadPackageMigrations(): void
     {
@@ @@

     /**
      * Load test-specific migrations.
-     *
-     * @return void
      */
     private function loadTestMigrations(): void
     {
@@ @@

     /**
      * Configure in-memory cache for tests.
-     *
-     * @return void
      */
     private function configureMemoryCache(): void
     {
@@ @@
      * Get package service providers.
      *
      * @param mixed $app
-     * @return array
      */
     protected function getPackageProviders($app): array
     {
@@ @@
      * Define the test environment configuration.
      *
      * @param mixed $app
-     * @return void
      */
     protected function defineEnvironment($app): void
     {
    ----------- end diff -----------

Applied rules:
 * RemoveUselessReturnTagRector


28) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Commands/DebugRulesCommandTest.php:22

    ---------- begin diff ----------
@@ @@
 {
     /**
      * Clean up Mockery after each test.
-     *
-     * @return void
      */
     protected function tearDown(): void
     {
@@ @@
         ];

         foreach ($expectedOptions as $optionName) {
-            $this->assertArrayHasKey($optionName, $options, "Option '$optionName' is missing");
+            $this->assertArrayHasKey($optionName, $options, sprintf("Option '%s' is missing", $optionName));
         }

         // Assert: Verify custom options have correct configurations
    ----------- end diff -----------

Applied rules:
 * EncapsedStringsToSprintfRector
 * RemoveUselessReturnTagRector


29) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/DTOs/AvailabilityDataTest.php:5

    ---------- begin diff ----------
@@ @@
 namespace Tests\Unit\DTOs;

 use Illuminate\Support\Carbon;
-use Illuminate\Database\Eloquent\Model;
 use Roster\DTOs\AvailabilityData;
 use Roster\Models\Availability;
 use Roster\Support\RosterMutationContext;
@@ @@
         $availabilityData = AvailabilityData::fromArray($rawData);

         // Assert: Verify all properties are correctly set
-        $this->assertEquals(123, $availabilityData->id);
-        $this->assertEquals('consultation', $availabilityData->type);
-        $this->assertEquals(['monday', 'wednesday', 'friday'], $availabilityData->days);
-        $this->assertEquals('2038-01-01', $availabilityData->validityStart?->format('Y-m-d'));
-        $this->assertEquals('2038-12-31', $availabilityData->validityEnd?->format('Y-m-d'));
-        $this->assertEquals('09:00:00', $availabilityData->dailyStart?->format('H:i:s'));
-        $this->assertEquals('17:00:00', $availabilityData->dailyEnd?->format('H:i:s'));
-        $this->assertEquals(456, $availabilityData->schedulableId);
-        $this->assertEquals('user', $availabilityData->schedulableType);
+        $this->assertSame(123, $availabilityData->id);
+        $this->assertSame('consultation', $availabilityData->type);
+        $this->assertSame(['monday', 'wednesday', 'friday'], $availabilityData->days);
+        $this->assertSame('2038-01-01', $availabilityData->validityStart?->format('Y-m-d'));
+        $this->assertSame('2038-12-31', $availabilityData->validityEnd?->format('Y-m-d'));
+        $this->assertSame('09:00:00', $availabilityData->dailyStart?->format('H:i:s'));
+        $this->assertSame('17:00:00', $availabilityData->dailyEnd?->format('H:i:s'));
+        $this->assertSame(456, $availabilityData->schedulableId);
+        $this->assertSame('user', $availabilityData->schedulableType);
     }

     /**
@@ @@

         // Assert: Verify provided properties are set, others are null
         $this->assertNull($availabilityData->id);
-        $this->assertEquals('training', $availabilityData->type);
+        $this->assertSame('training', $availabilityData->type);
         $this->assertNull($availabilityData->days);
-        $this->assertNull($availabilityData->validityStart);
-        $this->assertNull($availabilityData->validityEnd);
-        $this->assertEquals('08:00:00', $availabilityData->dailyStart?->format('H:i:s'));
-        $this->assertEquals('16:00:00', $availabilityData->dailyEnd?->format('H:i:s'));
+        $this->assertNotInstanceOf(Carbon::class, $availabilityData->validityStart);
+        $this->assertNotInstanceOf(Carbon::class, $availabilityData->validityEnd);
+        $this->assertSame('08:00:00', $availabilityData->dailyStart?->format('H:i:s'));
+        $this->assertSame('16:00:00', $availabilityData->dailyEnd?->format('H:i:s'));
     }

     /**
@@ @@
         $updatedData = $originalData->withDays(['tuesday', 'thursday']);

         // Assert: Verify new instance has updated days, original unchanged
-        $this->assertEquals(['tuesday', 'thursday'], $updatedData->days);
-        $this->assertEquals(['monday'], $originalData->days);
-        $this->assertEquals('consultation', $updatedData->type);
-        $this->assertEquals('09:00:00', $updatedData->dailyStart?->format('H:i:s'));
+        $this->assertSame(['tuesday', 'thursday'], $updatedData->days);
+        $this->assertSame(['monday'], $originalData->days);
+        $this->assertSame('consultation', $updatedData->type);
+        $this->assertSame('09:00:00', $updatedData->dailyStart?->format('H:i:s'));
     }

     /**
@@ @@

         // Act & Assert: Verify isUpdateOperation returns false
         $this->assertFalse($availabilityData->isUpdateOperation());
-        $this->assertNull($availabilityData->getExistingEntity());
+        $this->assertNotInstanceOf(Availability::class, $availabilityData->getExistingEntity());
     }

     /**
@@ @@

         // Act & Assert: Verify getExistingEntity returns null
         $this->assertFalse($availabilityData->isUpdateOperation());
-        $this->assertNull($availabilityData->getExistingEntity());
+        $this->assertNotInstanceOf(Availability::class, $availabilityData->getExistingEntity());
     }

     /**
@@ @@

         // Assert: Verify string days are treated as array with single element

-        $this->assertEquals(['monday,tuesday'], $days);
+        $this->assertSame(['monday,tuesday'], $days);
         $this->assertEquals(['monday,tuesday'], $arrayData['days']);
     }

@@ @@
         ]);

         // Assert: Verify correctly parsed days
-        $this->assertEquals(['monday', 'tuesday', 'wednesday'], $availabilityData->days);
+        $this->assertSame(['monday', 'tuesday', 'wednesday'], $availabilityData->days);
     }

     /**
@@ @@
         ]);

         // Act: Create new DTO with schedulable info
-        /**  @var \Roster\DTOs\AvailabilityData $updatedData */
+        /** @var AvailabilityData $updatedData */
         $updatedData = $originalData->withSchedulable(123, 'user');

         // Assert: Verify new instance has schedulable info, original unchanged
    ----------- end diff -----------

Applied rules:
 * AssertEmptyNullableObjectToAssertInstanceofRector
 * AssertEqualsToSameRector


30) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/DTOs/ImpedimentDataTest.php:4

    ---------- begin diff ----------
@@ @@

 namespace Tests\Unit\DTOs;

-use Illuminate\Support\Carbon;
-use Illuminate\Database\Eloquent\Model;
+use Exception;
+use ReflectionClass;
 use Illuminate\Support\Carbon as IlluminateCarbon;
 use InvalidArgumentException;
 use Roster\DTOs\ImpedimentData;
@@ @@
         $impedimentData = ImpedimentData::fromArray($rawData);

         // Assert: Verify all properties are correctly set with UTC timezone
-        $this->assertEquals(123, $impedimentData->id);
-        $this->assertEquals(456, $impedimentData->availabilityId);
-        $this->assertEquals('2038-01-15 09:00:00', $impedimentData->startDatetime?->format('Y-m-d H:i:s'));
+        $this->assertSame(123, $impedimentData->id);
+        $this->assertSame(456, $impedimentData->availabilityId);
+        $this->assertSame('2038-01-15 09:00:00', $impedimentData->startDatetime?->format('Y-m-d H:i:s'));
         $this->assertEquals('UTC', $impedimentData->startDatetime?->getTimezone()->getName());
-        $this->assertEquals('2038-01-15 17:00:00', $impedimentData->endDatetime?->format('Y-m-d H:i:s'));
+        $this->assertSame('2038-01-15 17:00:00', $impedimentData->endDatetime?->format('Y-m-d H:i:s'));
         $this->assertEquals('UTC', $impedimentData->endDatetime?->getTimezone()->getName());
-        $this->assertEquals('Maintenance window', $impedimentData->reason);
-        $this->assertEquals(['type' => 'scheduled', 'impact' => 'high'], $impedimentData->metadata);
-        $this->assertEquals(789, $impedimentData->schedulableId);
-        $this->assertEquals('equipment', $impedimentData->schedulableType);
+        $this->assertSame('Maintenance window', $impedimentData->reason);
+        $this->assertSame(['type' => 'scheduled', 'impact' => 'high'], $impedimentData->metadata);
+        $this->assertSame(789, $impedimentData->schedulableId);
+        $this->assertSame('equipment', $impedimentData->schedulableType);
     }

     /**
@@ @@
         // Assert: Verify provided properties are set with defaults applied
         $this->assertNull($impedimentData->id);
         $this->assertNull($impedimentData->availabilityId);
-        $this->assertEquals('2038-02-01 08:00:00', $impedimentData->startDatetime?->format('Y-m-d H:i:s'));
-        $this->assertEquals('2038-02-01 12:00:00', $impedimentData->endDatetime?->format('Y-m-d H:i:s'));
-        $this->assertEquals('Team offsite', $impedimentData->reason);
-        $this->assertEquals([], $impedimentData->metadata);
+        $this->assertSame('2038-02-01 08:00:00', $impedimentData->startDatetime?->format('Y-m-d H:i:s'));
+        $this->assertSame('2038-02-01 12:00:00', $impedimentData->endDatetime?->format('Y-m-d H:i:s'));
+        $this->assertSame('Team offsite', $impedimentData->reason);
+        $this->assertSame([], $impedimentData->metadata);
         $this->assertNull($impedimentData->schedulableId);
         $this->assertNull($impedimentData->schedulableType);
     }
@@ @@
         $impedimentData = ImpedimentData::fromArray($rawData);

         // Assert: Verify Carbon instances are correctly handled
-        $this->assertEquals('System upgrade', $impedimentData->reason);
-        $this->assertEquals('2038-03-10 10:00:00', $impedimentData->startDatetime?->format('Y-m-d H:i:s'));
-        $this->assertEquals('2038-03-10 18:00:00', $impedimentData->endDatetime?->format('Y-m-d H:i:s'));
+        $this->assertSame('System upgrade', $impedimentData->reason);
+        $this->assertSame('2038-03-10 10:00:00', $impedimentData->startDatetime?->format('Y-m-d H:i:s'));
+        $this->assertSame('2038-03-10 18:00:00', $impedimentData->endDatetime?->format('Y-m-d H:i:s'));
         $this->assertEquals('UTC', $impedimentData->startDatetime?->getTimezone()->getName());
         $this->assertEquals('UTC', $impedimentData->endDatetime?->getTimezone()->getName());
     }
@@ @@
         ]);

         // Act & Assert: Verify metadata is empty array
-        $this->assertEquals([], $impedimentData->metadata);
+        $this->assertSame([], $impedimentData->metadata);
         $this->assertEquals([], $impedimentData->toArray()['metadata']);
     }

@@ @@
         ];

         // Act & Assert: Verify exception is thrown for invalid datetime
-        $this->expectException(\Exception::class);
+        $this->expectException(Exception::class);

         ImpedimentData::fromArray($rawData);
     }
@@ @@
         $impedimentData = ImpedimentData::fromArray($rawData);

         // Assert: Verify empty string creates Carbon instance (current date), null remains null
-        $this->assertNotNull($impedimentData->startDatetime);
         $this->assertInstanceOf(IlluminateCarbon::class, $impedimentData->startDatetime);
-        $this->assertNull($impedimentData->endDatetime);
+        $this->assertInstanceOf(IlluminateCarbon::class, $impedimentData->startDatetime);
+        $this->assertNotInstanceOf(IlluminateCarbon::class, $impedimentData->endDatetime);
     }

     /**
@@ @@

         // Verify timezone is preserved in array conversion
         $arrayData = $impedimentData->toArray();
-        $this->assertStringContainsString('2038-12-25 00:00:00', $arrayData['start_datetime']);
-        $this->assertStringContainsString('2038-12-26 00:00:00', $arrayData['end_datetime']);
+        $this->assertStringContainsString('2038-12-25 00:00:00', (string) $arrayData['start_datetime']);
+        $this->assertStringContainsString('2038-12-26 00:00:00', (string) $arrayData['end_datetime']);
     }

     /**
@@ @@
         ]);

         // Act & Assert: Verify properties are readonly (cannot be modified)
-        $reflection = new \ReflectionClass($impedimentData);
+        $reflection = new ReflectionClass($impedimentData);

         foreach ($reflection->getProperties() as $property) {
             $this->assertTrue($property->isReadOnly());
@@ @@
         ]);

         // Act: Create new DTO with schedulable info
-        /**  @var \Roster\DTOs\ImpedimentData $updatedData */
+        /** @var ImpedimentData $updatedData */
         $updatedData = $originalData->withSchedulable(456, 'team');

         // Assert: Verify new instance has schedulable info, original unchanged
@@ @@
     public function test_parse_datetime_throws_exception_for_invalid_input_type(): void
     {
         // Arrange: Use reflection to test protected method
-        $reflectionClass = new \ReflectionClass(ImpedimentData::class);
+        $reflectionClass = new ReflectionClass(ImpedimentData::class);
         $method = $reflectionClass->getMethod('parseDateTime');
         $method->setAccessible(true);

@@ @@
     public function test_parse_datetime_returns_null_for_null_input(): void
     {
         // Arrange: Use reflection to test protected method
-        $reflectionClass = new \ReflectionClass(ImpedimentData::class);
+        $reflectionClass = new ReflectionClass(ImpedimentData::class);
         $method = $reflectionClass->getMethod('parseDateTime');
         $method->setAccessible(true);

@@ @@
         $carbon = IlluminateCarbon::create(2039, 4, 1, 10, 0, 0, 'UTC');

         // Use reflection to test protected method
-        $reflectionClass = new \ReflectionClass(ImpedimentData::class);
+        $reflectionClass = new ReflectionClass(ImpedimentData::class);
         $method = $reflectionClass->getMethod('parseDateTime');
         $method->setAccessible(true);

@@ @@

         // Assert: Verify all non-null data is preserved
         foreach ($originalData as $key => $value) {
-            if ($value !== null) {
-                $this->assertArrayHasKey($key, $convertedData);
-                $this->assertEquals($value, $convertedData[$key]);
-            }
+            $this->assertArrayHasKey($key, $convertedData);
+            $this->assertEquals($value, $convertedData[$key]);
         }
     }

@@ @@

         // Assert: Verify it's exactly 24 hours
         $this->assertEquals(24, $duration);
-        $this->assertEquals('All day event', $impedimentData->reason);
+        $this->assertSame('All day event', $impedimentData->reason);
     }

     /**
@@ @@
         ]);

         // Assert: Verify start date is set, end date is null
-        $this->assertNotNull($impedimentData->startDatetime);
-        $this->assertNull($impedimentData->endDatetime);
-        $this->assertEquals('Start only test', $impedimentData->reason);
+        $this->assertInstanceOf(IlluminateCarbon::class, $impedimentData->startDatetime);
+        $this->assertNotInstanceOf(IlluminateCarbon::class, $impedimentData->endDatetime);
+        $this->assertSame('Start only test', $impedimentData->reason);

         $arrayData = $impedimentData->toArray();
         $this->assertArrayHasKey('start_datetime', $arrayData);
@@ @@
         ]);

         // Assert: Verify end date is set, start date is null
-        $this->assertNull($impedimentData->startDatetime);
-        $this->assertNotNull($impedimentData->endDatetime);
-        $this->assertEquals('End only test', $impedimentData->reason);
+        $this->assertNotInstanceOf(IlluminateCarbon::class, $impedimentData->startDatetime);
+        $this->assertInstanceOf(IlluminateCarbon::class, $impedimentData->endDatetime);
+        $this->assertSame('End only test', $impedimentData->reason);

         $arrayData = $impedimentData->toArray();
         $this->assertArrayNotHasKey('start_datetime', $arrayData);
    ----------- end diff -----------

Applied rules:
 * RemoveAlwaysTrueIfConditionRector
 * AssertEmptyNullableObjectToAssertInstanceofRector
 * AssertEqualsToSameRector
 * StringCastAssertStringContainsStringRector


31) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/DTOs/ScheduleDataTest.php:4

    ---------- begin diff ----------
@@ @@

 namespace Tests\Unit\DTOs;

-use Illuminate\Database\Eloquent\Model;
+use Exception;
+use ReflectionClass;
 use Illuminate\Support\Carbon;
 use InvalidArgumentException;
 use Roster\DTOs\ScheduleData;
@@ @@
         $scheduleData = ScheduleData::fromArray($rawData);

         // Assert: Verify all properties are correctly set with UTC timezone
-        $this->assertEquals(123, $scheduleData->id);
-        $this->assertEquals(456, $scheduleData->availabilityId);
-        $this->assertEquals('Team Meeting', $scheduleData->title);
-        $this->assertEquals('Weekly team sync to discuss project progress', $scheduleData->description);
-        $this->assertEquals('2038-01-15 09:00:00', $scheduleData->startDatetime?->format('Y-m-d H:i:s'));
+        $this->assertSame(123, $scheduleData->id);
+        $this->assertSame(456, $scheduleData->availabilityId);
+        $this->assertSame('Team Meeting', $scheduleData->title);
+        $this->assertSame('Weekly team sync to discuss project progress', $scheduleData->description);
+        $this->assertSame('2038-01-15 09:00:00', $scheduleData->startDatetime?->format('Y-m-d H:i:s'));
         $this->assertEquals('UTC', $scheduleData->startDatetime?->getTimezone()->getName());
-        $this->assertEquals('2038-01-15 10:30:00', $scheduleData->endDatetime?->format('Y-m-d H:i:s'));
+        $this->assertSame('2038-01-15 10:30:00', $scheduleData->endDatetime?->format('Y-m-d H:i:s'));
         $this->assertEquals('UTC', $scheduleData->endDatetime?->getTimezone()->getName());
-        $this->assertEquals(['room' => 'Conference A', 'priority' => 'high'], $scheduleData->metadata);
-        $this->assertEquals(ScheduleStatus::BOOKED, $scheduleData->status);
-        $this->assertEquals(789, $scheduleData->schedulableId);
-        $this->assertEquals('team', $scheduleData->schedulableType);
+        $this->assertSame(['room' => 'Conference A', 'priority' => 'high'], $scheduleData->metadata);
+        $this->assertSame(ScheduleStatus::BOOKED, $scheduleData->status);
+        $this->assertSame(789, $scheduleData->schedulableId);
+        $this->assertSame('team', $scheduleData->schedulableType);
     }

     /**
@@ @@
         // Assert: Verify provided properties are set with defaults applied
         $this->assertNull($scheduleData->id);
         $this->assertNull($scheduleData->availabilityId);
-        $this->assertEquals('Client Call', $scheduleData->title);
+        $this->assertSame('Client Call', $scheduleData->title);
         $this->assertNull($scheduleData->description);
-        $this->assertEquals('2038-02-01 14:00:00', $scheduleData->startDatetime?->format('Y-m-d H:i:s'));
-        $this->assertEquals('2038-02-01 15:00:00', $scheduleData->endDatetime?->format('Y-m-d H:i:s'));
-        $this->assertEquals([], $scheduleData->metadata);
-        $this->assertEquals(ScheduleStatus::AVAILABLE, $scheduleData->status);
+        $this->assertSame('2038-02-01 14:00:00', $scheduleData->startDatetime?->format('Y-m-d H:i:s'));
+        $this->assertSame('2038-02-01 15:00:00', $scheduleData->endDatetime?->format('Y-m-d H:i:s'));
+        $this->assertSame([], $scheduleData->metadata);
+        $this->assertSame(ScheduleStatus::AVAILABLE, $scheduleData->status);
         $this->assertNull($scheduleData->schedulableId);
         $this->assertNull($scheduleData->schedulableType);
     }
@@ @@
         $scheduleData = ScheduleData::fromArray($rawData);

         // Assert: Verify Carbon instances are correctly handled
-        $this->assertEquals('Training Session', $scheduleData->title);
-        $this->assertEquals('2038-03-10 10:00:00', $scheduleData->startDatetime?->format('Y-m-d H:i:s'));
-        $this->assertEquals('2038-03-10 12:00:00', $scheduleData->endDatetime?->format('Y-m-d H:i:s'));
+        $this->assertSame('Training Session', $scheduleData->title);
+        $this->assertSame('2038-03-10 10:00:00', $scheduleData->startDatetime?->format('Y-m-d H:i:s'));
+        $this->assertSame('2038-03-10 12:00:00', $scheduleData->endDatetime?->format('Y-m-d H:i:s'));
         $this->assertEquals('UTC', $scheduleData->startDatetime?->getTimezone()->getName());
         $this->assertEquals('UTC', $scheduleData->endDatetime?->getTimezone()->getName());
     }
@@ @@
         $scheduleData = ScheduleData::fromArray($rawData);

         // Assert: Verify instances sont correctement gérés (même si conversion)
-        $this->assertEquals('Carbon Carbon Test', $scheduleData->title);
-        $this->assertEquals('2038-06-10 10:00:00', $scheduleData->startDatetime?->format('Y-m-d H:i:s'));
-        $this->assertEquals('2038-06-10 12:00:00', $scheduleData->endDatetime?->format('Y-m-d H:i:s'));
+        $this->assertSame('Carbon Carbon Test', $scheduleData->title);
+        $this->assertSame('2038-06-10 10:00:00', $scheduleData->startDatetime?->format('Y-m-d H:i:s'));
+        $this->assertSame('2038-06-10 12:00:00', $scheduleData->endDatetime?->format('Y-m-d H:i:s'));
         $this->assertEquals('UTC', $scheduleData->startDatetime?->getTimezone()->getName());
     }

@@ @@
         ]);

         // Act & Assert: Verify metadata is empty array
-        $this->assertEquals([], $scheduleData->metadata);
+        $this->assertSame([], $scheduleData->metadata);
         $this->assertEquals([], $scheduleData->toArray()['metadata']);
     }

@@ @@
         ]);

         // Act & Assert: Verify default status is AVAILABLE
-        $this->assertEquals(ScheduleStatus::AVAILABLE, $scheduleData->status);
+        $this->assertSame(ScheduleStatus::AVAILABLE, $scheduleData->status);
         $this->assertEquals(ScheduleStatus::AVAILABLE, $scheduleData->toArray()['status']);
     }

@@ @@
         ];

         // Act & Assert: Verify exception is thrown for invalid datetime
-        $this->expectException(\Exception::class);
+        $this->expectException(Exception::class);

         ScheduleData::fromArray($rawData);
     }
@@ @@
         $scheduleData = ScheduleData::fromArray($rawData);

         // Assert: Verify empty string creates Carbon instance (current date), null remains null
-        $this->assertNotNull($scheduleData->startDatetime);
         $this->assertInstanceOf(Carbon::class, $scheduleData->startDatetime);
-        $this->assertNull($scheduleData->endDatetime);
+        $this->assertInstanceOf(Carbon::class, $scheduleData->startDatetime);
+        $this->assertNotInstanceOf(Carbon::class, $scheduleData->endDatetime);
     }

     /**
@@ @@

         // Verify timezone is preserved in array conversion
         $arrayData = $scheduleData->toArray();
-        $this->assertStringContainsString('2038-12-25 14:00:00', $arrayData['start_datetime']);
-        $this->assertStringContainsString('2038-12-25 15:30:00', $arrayData['end_datetime']);
+        $this->assertStringContainsString('2038-12-25 14:00:00', (string) $arrayData['start_datetime']);
+        $this->assertStringContainsString('2038-12-25 15:30:00', (string) $arrayData['end_datetime']);
     }

     /**
@@ @@
         ]);

         // Act & Assert: Verify properties are readonly (cannot be modified)
-        $reflection = new \ReflectionClass($scheduleData);
+        $reflection = new ReflectionClass($scheduleData);

         foreach ($reflection->getProperties() as $property) {
             $this->assertTrue($property->isReadOnly());
@@ @@
         ]);

         // Act: Create new DTO with schedulable info
-        /**  @var \Roster\DTOs\ScheduleData $updatedData */
+        /** @var ScheduleData $updatedData */
         $updatedData = $originalData->withSchedulable(456, 'team');

         // Assert: Verify new instance has schedulable info, original unchanged
@@ @@
     public function test_parse_datetime_throws_exception_for_invalid_input_type(): void
     {
         // Arrange: Use reflection to test protected method
-        $reflectionClass = new \ReflectionClass(ScheduleData::class);
+        $reflectionClass = new ReflectionClass(ScheduleData::class);
         $method = $reflectionClass->getMethod('parseDateTime');
         $method->setAccessible(true);

@@ @@
     public function test_parse_datetime_returns_null_for_null_input(): void
     {
         // Arrange: Use reflection to test protected method
-        $reflectionClass = new \ReflectionClass(ScheduleData::class);
+        $reflectionClass = new ReflectionClass(ScheduleData::class);
         $method = $reflectionClass->getMethod('parseDateTime');
         $method->setAccessible(true);

@@ @@
         $carbon = Carbon::create(2039, 4, 1, 10, 0, 0, 'UTC');

         // Use reflection to test protected method
-        $reflectionClass = new \ReflectionClass(ScheduleData::class);
+        $reflectionClass = new ReflectionClass(ScheduleData::class);
         $method = $reflectionClass->getMethod('parseDateTime');
         $method->setAccessible(true);

@@ @@

         // Assert: Verify all non-null data is preserved
         foreach ($originalData as $key => $value) {
-            if ($value !== null) {
-                $this->assertArrayHasKey($key, $convertedData);
-                $this->assertEquals($value, $convertedData[$key]);
-            }
+            $this->assertArrayHasKey($key, $convertedData);
+            $this->assertEquals($value, $convertedData[$key]);
         }
     }
 }
    ----------- end diff -----------

Applied rules:
 * RemoveAlwaysTrueIfConditionRector
 * AssertEmptyNullableObjectToAssertInstanceofRector
 * AssertEqualsToSameRector
 * StringCastAssertStringContainsStringRector


32) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Domain/Helpers/TimezoneHelperTest.php:4

    ---------- begin diff ----------
@@ @@

 namespace Tests\Unit\Domain\Helpers;

+use PHPUnit\Framework\Attributes\CoversClass;
 use Illuminate\Support\Carbon;
 use InvalidArgumentException;
 use Roster\Domain\Helpers\TimezoneHelper;
@@ @@

 /**
  * Test suite for TimezoneHelper functionality.
- * @covers \Roster\Domain\Helpers\TimezoneHelper
  */
+#[CoversClass(\Roster\Domain\Helpers\TimezoneHelper::class)]
 final class TimezoneHelperTest extends TestCase
 {
     /**
@@ @@
      */
     public function test_to_system_conversion_with_null(): void
     {
-        $this->assertNull(TimezoneHelper::toSystem(null));
+        $this->assertNotInstanceOf(Carbon::class, TimezoneHelper::toSystem(null));
     }

     /**
@@ @@
      */
     public function test_to_user_conversion_with_null(): void
     {
-        $this->assertNull(TimezoneHelper::toUser(null));
+        $this->assertNotInstanceOf(Carbon::class, TimezoneHelper::toUser(null));
     }

     /**
@@ @@
     public function test_with_only_app_timezone_configured(): void
     {
         // Arrange: Clear roster timezone, set app timezone
-        Config::set('roster.timezone', null);
+        Config::set('roster.timezone');
         Config::set('app.timezone', 'Australia/Sydney');

         // Act: Initialize helper
    ----------- end diff -----------

Applied rules:
 * RemoveNullArgOnNullDefaultParamRector
 * CoversAnnotationWithValueToAttributeRector
 * AssertEmptyNullableObjectToAssertInstanceofRector


33) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Domain/MutationContextAllowsMutationTest.php:47

    ---------- begin diff ----------
@@ @@
         ];

         // Act: Create availability within mutation context
-        $availability = RosterMutationContext::allow(function () use ($testData) {
+        $availability = RosterMutationContext::allow(function () use ($testData): Availability {
             return $this->createAvailability($testData);
         });

@@ @@
     public function test_update_inside_context_is_allowed(): void
     {
         // Arrange: Create initial availability
-        $availability = RosterMutationContext::allow(function () {
+        $availability = RosterMutationContext::allow(function (): Availability {
             return $this->createAvailability([
                 'schedulable_id' => 1,
                 'schedulable_type' => TestSchedulable::class,
@@ @@
     public function test_delete_inside_context_is_allowed(): void
     {
         // Arrange: Create availability
-        $availability = RosterMutationContext::allow(function () {
+        $availability = RosterMutationContext::allow(function (): Availability {
             return $this->createAvailability([
                 'schedulable_id' => 1,
                 'schedulable_type' => TestSchedulable::class,
@@ @@
      * Create an availability with the given data.
      *
      * @param array<string, mixed> $data
-     * @return Availability
      */
     private function createAvailability(array $data): Availability
     {
    ----------- end diff -----------

Applied rules:
 * RemoveUselessReturnTagRector
 * ClosureReturnTypeRector


34) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Exceptions/ValidationFailedExceptionTest.php:145

    ---------- begin diff ----------
@@ @@
             '- [time_after] validity_end: Validity end must be after start',
         ];

-        $this->assertEquals(implode("\n", $expectedLines), $message);
+        $this->assertSame(implode("\n", $expectedLines), $message);
     }

     /**
@@ @@
             '',
         ];

-        $this->assertEquals(implode("\n", $expectedLines), $message);
+        $this->assertSame(implode("\n", $expectedLines), $message);
     }

     /**
@@ @@
         $firstViolation = $exception->getFirstViolation();

         // Assert: Verify first violation message
-        $this->assertEquals('Daily start is required', $firstViolation);
+        $this->assertSame('Daily start is required', $firstViolation);
     }

     /**
    ----------- end diff -----------

Applied rules:
 * AssertEqualsToSameRector


35) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/HelpersTest.php:4

    ---------- begin diff ----------
@@ @@

 namespace Tests\Unit;

+use Illuminate\Database\Eloquent\Model;
 use Illuminate\Support\Carbon;
 use Illuminate\Support\Facades\Config;
 use InvalidArgumentException;
@@ @@
      */
     public function test_roster_to_utc_with_null(): void
     {
-        $this->assertNull(roster_to_utc(null));
+        $this->assertNotInstanceOf(Carbon::class, roster_to_utc(null));
     }

     /**
@@ @@
      */
     public function test_roster_to_user_timezone_with_null(): void
     {
-        $this->assertNull(roster_to_user_timezone(null));
+        $this->assertNotInstanceOf(Carbon::class, roster_to_user_timezone(null));
     }

     /**
@@ @@

     /**
      * Create a mock schedulable model for testing.
-     *
-     * @return \Illuminate\Database\Eloquent\Model
      */
-    private function createMockSchedulable(): \Illuminate\Database\Eloquent\Model
+    private function createMockSchedulable(): Model
     {
-        return new class extends \Illuminate\Database\Eloquent\Model {};
+        return new class extends Model {};
     }

     /**
    ----------- end diff -----------

Applied rules:
 * RemoveUselessReturnTagRector
 * AssertEmptyNullableObjectToAssertInstanceofRector


36) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Http/Middleware/SetUserTimezoneTest.php:4

    ---------- begin diff ----------
@@ @@

 namespace Tests\Unit\Http\Middleware;

+use Illuminate\Contracts\Routing\ResponseFactory;
+use Illuminate\Http\Response;
+use stdClass;
 use Illuminate\Http\Request;
 use Illuminate\Session\Store;
 use Mockery;
@@ @@
     public function testSetsTimezoneFromHeader(): void
     {
         // Arrange: Create request with timezone header
-        $request = Request::create(uri: '/', method: 'GET');
+        $request = Request::create(uri: '/', method: \Symfony\Component\HttpFoundation\Request::METHOD_GET);
         $request->headers->set('X-Timezone', 'America/New_York');

-        $next = function ($req) {
+        $next = function ($req): ResponseFactory|Response {
             // Assert: Verify timezone is set correctly during request handling
             $this->assertSame('America/New_York', TimezoneHelper::getEffectiveTimezone());
             return response('OK');
@@ @@
         $response = $this->middleware->handle($request, $next);

         // Assert: Verify successful response
-        $this->assertSame(200, $response->getStatusCode());
+        $this->assertSame(\Symfony\Component\HttpFoundation\Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());
     }

     /**
@@ @@
             ->with('timezone')
             ->andReturn('Asia/Tokyo');

-        $request = Request::create(uri: '/', method: 'GET');
+        $request = Request::create(uri: '/', method: \Symfony\Component\HttpFoundation\Request::METHOD_GET);
         $request->setLaravelSession($session);

-        $next = function ($req) {
+        $next = function ($req): ResponseFactory|Response {
             // Assert: Verify session timezone is applied
             $this->assertSame('Asia/Tokyo', TimezoneHelper::getEffectiveTimezone());
             return response('OK');
@@ @@
         $response = $this->middleware->handle($request, $next);

         // Assert: Verify successful response
-        $this->assertSame(200, $response->getStatusCode());
+        $this->assertSame(\Symfony\Component\HttpFoundation\Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());
     }

     /**
@@ @@
     {
         // Arrange: Create user with timezone preference
         $user = new class {
-            public function getTimezone()
+            public function getTimezone(): string
             {
                 return 'Australia/Sydney';
             }
         };

-        $request = Request::create(uri: '/', method: 'GET');
-        $request->setUserResolver(fn() => $user);
+        $request = Request::create(uri: '/', method: \Symfony\Component\HttpFoundation\Request::METHOD_GET);
+        $request->setUserResolver(fn(): object => $user);

-        $next = function ($req) {
+        $next = function ($req): ResponseFactory|Response {
             // Assert: Verify user timezone is applied
             $this->assertSame('Australia/Sydney', TimezoneHelper::getEffectiveTimezone());
             return response('OK');
@@ @@
         $response = $this->middleware->handle($request, $next);

         // Assert: Verify successful response
-        $this->assertSame(200, $response->getStatusCode());
+        $this->assertSame(\Symfony\Component\HttpFoundation\Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());
     }

     /**
@@ @@
     public function testSetsTimezoneFromClientHeader(): void
     {
         // Arrange: Create request with client timezone header
-        $request = Request::create(uri: '/', method: 'GET');
+        $request = Request::create(uri: '/', method: \Symfony\Component\HttpFoundation\Request::METHOD_GET);
         $request->headers->set('X-Client-Timezone', 'Pacific/Honolulu');

-        $next = function ($req) {
+        $next = function ($req): ResponseFactory|Response {
             // Assert: Verify client timezone is applied
             $this->assertSame('Pacific/Honolulu', TimezoneHelper::getEffectiveTimezone());
             return response('OK');
@@ @@
         $response = $this->middleware->handle($request, $next);

         // Assert: Verify successful response
-        $this->assertSame(200, $response->getStatusCode());
+        $this->assertSame(\Symfony\Component\HttpFoundation\Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());
     }

     /**
@@ @@
     public function testUsesDefaultWhenNoTimezoneSource(): void
     {
         // Arrange: Create request without any timezone sources
-        $request = Request::create(uri: '/', method: 'GET');
+        $request = Request::create(uri: '/', method: \Symfony\Component\HttpFoundation\Request::METHOD_GET);

-        $next = function ($req) {
+        $next = function ($req): ResponseFactory|Response {
             // Assert: Verify default timezone is used
             $this->assertSame('Europe/Paris', TimezoneHelper::getEffectiveTimezone());
             return response('OK');
@@ @@
         $response = $this->middleware->handle($request, $next);

         // Assert: Verify successful response
-        $this->assertSame(200, $response->getStatusCode());
+        $this->assertSame(\Symfony\Component\HttpFoundation\Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());
     }

     /**
@@ @@
             ->with('timezone')
             ->andReturn('Europe/London');

-        $request = Request::create(uri: '/', method: 'GET');
+        $request = Request::create(uri: '/', method: \Symfony\Component\HttpFoundation\Request::METHOD_GET);
         $request->setLaravelSession($session);
         $request->headers->set('X-Timezone', 'America/Chicago');

-        $next = function ($req) {
+        $next = function ($req): ResponseFactory|Response {
             // Assert: Verify header takes priority over session
             $this->assertSame('America/Chicago', TimezoneHelper::getEffectiveTimezone());
             return response('OK');
@@ @@
         $response = $this->middleware->handle($request, $next);

         // Assert: Verify successful response
-        $this->assertSame(200, $response->getStatusCode());
+        $this->assertSame(\Symfony\Component\HttpFoundation\Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());
     }

     /**
@@ @@
     public function testHandlesInvalidTimezoneInHeader(): void
     {
         // Arrange: Create request with invalid timezone header
-        $request = Request::create(uri: '/', method: 'GET');
+        $request = Request::create(uri: '/', method: \Symfony\Component\HttpFoundation\Request::METHOD_GET);
         $request->headers->set('X-Timezone', 'Invalid/Timezone');

-        $next = function ($req) {
+        $next = function ($req): ResponseFactory|Response {
             // Assert: Verify fallback to default timezone
             $this->assertSame('Europe/Paris', TimezoneHelper::getEffectiveTimezone());
             return response('OK');
@@ @@
         $response = $this->middleware->handle($request, $next);

         // Assert: Verify successful response
-        $this->assertSame(200, $response->getStatusCode());
+        $this->assertSame(\Symfony\Component\HttpFoundation\Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());
     }

     /**
@@ @@
     public function testNormalizesTimezoneNames(): void
     {
         // Arrange: Create request with lowercase timezone name
-        $request = Request::create(uri: '/', method: 'GET');
+        $request = Request::create(uri: '/', method: \Symfony\Component\HttpFoundation\Request::METHOD_GET);
         $request->headers->set('X-Timezone', 'america/new_york');

-        $next = function ($req) {
+        $next = function ($req): ResponseFactory|Response {
             // Assert: Verify timezone is properly normalized
             $this->assertSame('America/New_York', TimezoneHelper::getEffectiveTimezone());
             return response('OK');
@@ @@
         $response = $this->middleware->handle($request, $next);

         // Assert: Verify successful response
-        $this->assertSame(200, $response->getStatusCode());
+        $this->assertSame(\Symfony\Component\HttpFoundation\Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());
     }

     /**
@@ @@
     public function testWithUserWithoutGetTimezoneMethod(): void
     {
         // Arrange: Create user object without timezone method
-        $user = new \stdClass();
+        $user = new stdClass();

-        $request = Request::create(uri: '/', method: 'GET');
-        $request->setUserResolver(fn() => $user);
+        $request = Request::create(uri: '/', method: \Symfony\Component\HttpFoundation\Request::METHOD_GET);
+        $request->setUserResolver(fn(): stdClass => $user);

-        $next = function ($req) {
+        $next = function ($req): ResponseFactory|Response {
             // Assert: Verify default timezone is used
             $this->assertSame('Europe/Paris', TimezoneHelper::getEffectiveTimezone());
             return response('OK');
@@ @@
         $response = $this->middleware->handle($request, $next);

         // Assert: Verify successful response
-        $this->assertSame(200, $response->getStatusCode());
+        $this->assertSame(\Symfony\Component\HttpFoundation\Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());
     }

     /**
@@ @@
     {
         // Arrange: Create user that returns null timezone
         $user = new class {
-            public function getTimezone()
+            public function getTimezone(): null
             {
                 return null;
             }
         };

-        $request = Request::create(uri: '/', method: 'GET');
-        $request->setUserResolver(fn() => $user);
+        $request = Request::create(uri: '/', method: \Symfony\Component\HttpFoundation\Request::METHOD_GET);
+        $request->setUserResolver(fn(): object => $user);

-        $next = function ($req) {
+        $next = function ($req): ResponseFactory|Response {
             // Assert: Verify default timezone is used
             $this->assertSame('Europe/Paris', TimezoneHelper::getEffectiveTimezone());
             return response('OK');
@@ @@
         $response = $this->middleware->handle($request, $next);

         // Assert: Verify successful response
-        $this->assertSame(200, $response->getStatusCode());
+        $this->assertSame(\Symfony\Component\HttpFoundation\Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());
     }

     /**
@@ @@
             ->with('timezone')
             ->andReturn('Invalid/Timezone');

-        $request = Request::create(uri: '/', method: 'GET');
+        $request = Request::create(uri: '/', method: \Symfony\Component\HttpFoundation\Request::METHOD_GET);
         $request->setLaravelSession($session);

-        $next = function ($req) {
+        $next = function ($req): ResponseFactory|Response {
             // Assert: Verify fallback to default timezone
             $this->assertSame('Europe/Paris', TimezoneHelper::getEffectiveTimezone());
             return response('OK');
@@ @@
         $response = $this->middleware->handle($request, $next);

         // Assert: Verify successful response
-        $this->assertSame(200, $response->getStatusCode());
+        $this->assertSame(\Symfony\Component\HttpFoundation\Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());
     }

     /**
@@ @@
     public function testWithoutSession(): void
     {
         // Arrange: Create request without session
-        $request = Request::create(uri: '/', method: 'GET');
+        $request = Request::create(uri: '/', method: \Symfony\Component\HttpFoundation\Request::METHOD_GET);

-        $next = function ($req) {
+        $next = function ($req): ResponseFactory|Response {
             // Assert: Verify default timezone is used
             $this->assertSame('Europe/Paris', TimezoneHelper::getEffectiveTimezone());
             return response('OK');
@@ @@
         $response = $this->middleware->handle($request, $next);

         // Assert: Verify successful response
-        $this->assertSame(200, $response->getStatusCode());
+        $this->assertSame(\Symfony\Component\HttpFoundation\Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());
     }

     /**
@@ @@
             ->andReturn('Europe/London');

         $user = new class {
-            public function getTimezone()
+            public function getTimezone(): string
             {
                 return 'Asia/Dubai';
             }
         };

-        $request = Request::create(uri: '/', method: 'GET');
+        $request = Request::create(uri: '/', method: \Symfony\Component\HttpFoundation\Request::METHOD_GET);
         $request->setLaravelSession($session);
-        $request->setUserResolver(fn() => $user);
+        $request->setUserResolver(fn(): object => $user);
         $request->headers->set('X-Timezone', 'America/Los_Angeles');
         $request->headers->set('X-Client-Timezone', 'Pacific/Auckland');

-        $next = function ($req) {
+        $next = function ($req): ResponseFactory|Response {
             // Assert: Verify X-Timezone header has highest priority
             $this->assertSame('America/Los_Angeles', TimezoneHelper::getEffectiveTimezone());
             return response('OK');
@@ @@
         $response = $this->middleware->handle($request, $next);

         // Assert: Verify successful response
-        $this->assertSame(200, $response->getStatusCode());
+        $this->assertSame(\Symfony\Component\HttpFoundation\Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());
     }

     /**
@@ @@
     public function testWithOnlyClientTimezoneHeader(): void
     {
         // Arrange: Create request with only client timezone header
-        $request = Request::create(uri: '/', method: 'GET');
+        $request = Request::create(uri: '/', method: \Symfony\Component\HttpFoundation\Request::METHOD_GET);
         $request->headers->set('X-Client-Timezone', 'Asia/Shanghai');

-        $next = function ($req) {
+        $next = function ($req): ResponseFactory|Response {
             // Assert: Verify client timezone is used
             $this->assertSame('Asia/Shanghai', TimezoneHelper::getEffectiveTimezone());
             return response('OK');
@@ @@
         $response = $this->middleware->handle($request, $next);

         // Assert: Verify successful response
-        $this->assertSame(200, $response->getStatusCode());
+        $this->assertSame(\Symfony\Component\HttpFoundation\Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());
     }

     /**
@@ @@
     public function testWithEmptyTimezoneHeader(): void
     {
         // Arrange: Create request with empty timezone header
-        $request = Request::create(uri: '/', method: 'GET');
+        $request = Request::create(uri: '/', method: \Symfony\Component\HttpFoundation\Request::METHOD_GET);
         $request->headers->set('X-Timezone', '');

-        $next = function ($req) {
+        $next = function ($req): ResponseFactory|Response {
             // Assert: Verify default timezone is used
             $this->assertSame('Europe/Paris', TimezoneHelper::getEffectiveTimezone());
             return response('OK');
@@ @@
         $response = $this->middleware->handle($request, $next);

         // Assert: Verify successful response
-        $this->assertSame(200, $response->getStatusCode());
+        $this->assertSame(\Symfony\Component\HttpFoundation\Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());
     }

     /**
@@ @@
             ->with('timezone')
             ->andReturn('');

-        $request = Request::create(uri: '/', method: 'GET');
+        $request = Request::create(uri: '/', method: \Symfony\Component\HttpFoundation\Request::METHOD_GET);
         $request->setLaravelSession($session);

-        $next = function ($req) {
+        $next = function ($req): ResponseFactory|Response {
             // Assert: Verify default timezone is used
             $this->assertSame('Europe/Paris', TimezoneHelper::getEffectiveTimezone());
             return response('OK');
@@ @@
         $response = $this->middleware->handle($request, $next);

         // Assert: Verify successful response
-        $this->assertSame(200, $response->getStatusCode());
+        $this->assertSame(\Symfony\Component\HttpFoundation\Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());
     }

     /**
@@ @@
     public function testResetsTimezoneAfterRequest(): void
     {
         // Arrange: First request with specific timezone
-        $request1 = Request::create(uri: '/', method: 'GET');
+        $request1 = Request::create(uri: '/', method: \Symfony\Component\HttpFoundation\Request::METHOD_GET);
         $request1->headers->set('X-Timezone', 'America/New_York');

         // Arrange: Second request with different timezone
-        $request2 = Request::create(uri: '/', method: 'GET');
+        $request2 = Request::create(uri: '/', method: \Symfony\Component\HttpFoundation\Request::METHOD_GET);
         $request2->headers->set('X-Timezone', 'Europe/Berlin');

         // First request handler
-        $next1 = function ($req) {
+        $next1 = function ($req): ResponseFactory|Response {
             // Assert: Verify first request timezone
             $this->assertSame('America/New_York', TimezoneHelper::getEffectiveTimezone());
             return response('OK');
@@ @@
         };

         // Second request handler
-        $next2 = function ($req) {
+        $next2 = function ($req): ResponseFactory|Response {
             // Assert: Verify second request timezone
             $this->assertSame('Europe/Berlin', TimezoneHelper::getEffectiveTimezone());
             return response('OK');
@@ @@

         // Act & Assert: Process first request
         $response1 = $this->middleware->handle($request1, $next1);
-        $this->assertSame(200, $response1->getStatusCode());
+        $this->assertSame(\Symfony\Component\HttpFoundation\Response::HTTP_OK, $response1->getStatusCode(), (string) $response1->getContent());

         // Reset timezone state between requests
         TimezoneHelper::resetUserTimezone();
@@ @@

         // Act & Assert: Process second request
         $response2 = $this->middleware->handle($request2, $next2);
-        $this->assertSame(200, $response2->getStatusCode());
+        $this->assertSame(\Symfony\Component\HttpFoundation\Response::HTTP_OK, $response2->getStatusCode(), (string) $response2->getContent());
     }
 }
    ----------- end diff -----------

Applied rules:
 * ResponseStatusCodeRector
 * AssertSameResponseCodeWithDebugContentsRector
 * LiteralGetToRequestClassConstantRector
 * AddArrowFunctionReturnTypeRector
 * ReturnTypeFromStrictConstantReturnRector
 * StringReturnTypeFromStrictScalarReturnsRector
 * ClosureReturnTypeRector


37) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Models/ScheduleTest.php:155

    ---------- begin diff ----------
@@ @@
             // Act: Create schedule with specific status
             $schedule = schedule_for($this->testAvailability)->create([
                 'title' => 'Test Schedule',
-                'start_datetime' => "2038-07-01 {$startTimes[$index]}:00",
-                'end_datetime'   => "2038-07-01 {$endTimes[$index]}:00",
+                'start_datetime' => sprintf('2038-07-01 %s:00', $startTimes[$index]),
+                'end_datetime'   => sprintf('2038-07-01 %s:00', $endTimes[$index]),
                 'status' => ScheduleStatus::from($testCase['input']),
                 'metadata' => null,
             ]);
@@ @@
         $schedule = $this->createScheduleModelInstance();

         // Act: Soft delete schedule inside allowed mutation context
-        RosterMutationContext::allow(function () use ($schedule) {
+        RosterMutationContext::allow(function () use ($schedule): void {
             $schedule->delete();
         });

@@ @@
         ]);

         // Restore the schedule inside allowed mutation context
-        RosterMutationContext::allow(function () use ($schedule) {
+        RosterMutationContext::allow(function () use ($schedule): void {
             $schedule->restore();
         });

@@ @@
             // Act: Create schedule with specific status
             $schedule = schedule_for($this->testAvailability)->create([
                 'title' => $testCase['title'],
-                'start_datetime' => "2038-07-01 {$startTimes[$index]}:00",
-                'end_datetime' => "2038-07-01 {$endTimes[$index]}:00",
+                'start_datetime' => sprintf('2038-07-01 %s:00', $startTimes[$index]),
+                'end_datetime' => sprintf('2038-07-01 %s:00', $endTimes[$index]),
                 'status' => $testCase['status'],
                 'metadata' => null,
             ]);
    ----------- end diff -----------

Applied rules:
 * EncapsedStringsToSprintfRector
 * AddClosureVoidReturnTypeWhereNoReturnRector


38) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/DTOs/ViolationDataTest.php:26

    ---------- begin diff ----------
@@ @@
         $violation = new ViolationData($field, $message, $rule);

         // Assert: Verify all fields are correctly set
-        $this->assertEquals($field, $violation->getField());
-        $this->assertEquals($message, $violation->getMessage());
-        $this->assertEquals($rule, $violation->getRule());
+        $this->assertSame($field, $violation->getField());
+        $this->assertSame($message, $violation->getMessage());
+        $this->assertSame($rule, $violation->getRule());
         $this->assertNull($violation->getRuleDescription());
         $this->assertFalse($violation->hasRuleDescription());
     }
@@ @@
         $violation = new ViolationData($field, $message, $rule, $description);

         // Assert: Verify all fields including description
-        $this->assertEquals($field, $violation->getField());
-        $this->assertEquals($message, $violation->getMessage());
-        $this->assertEquals($rule, $violation->getRule());
-        $this->assertEquals($description, $violation->getRuleDescription());
+        $this->assertSame($field, $violation->getField());
+        $this->assertSame($message, $violation->getMessage());
+        $this->assertSame($rule, $violation->getRule());
+        $this->assertSame($description, $violation->getRuleDescription());
         $this->assertTrue($violation->hasRuleDescription());
     }

@@ @@
         $violation = new ViolationData($field, $message);

         // Assert: Verify default rule name
-        $this->assertEquals('unknown', $violation->getRule());
+        $this->assertSame('unknown', $violation->getRule());
         $this->assertNull($violation->getRuleDescription());
     }

@@ @@
         $violation = new ViolationData($field, $message, $rule, $description);

         // Assert: Verify empty description is not considered as having description
-        $this->assertEquals('', $violation->getRuleDescription());
+        $this->assertSame('', $violation->getRuleDescription());
         $this->assertFalse($violation->hasRuleDescription());
     }

@@ @@
         $array = $violation->toArray();

         // Assert: Verify array structure with description
-        $this->assertEquals([
+        $this->assertSame([
             'field' => 'email',
             'rule' => 'required',
             'message' => 'Required field',
@@ @@
         $violation = new ViolationData('email', 'Invalid field', 'format', $description);

         // Assert: Verify new lines are preserved
-        $this->assertEquals($description, $violation->getRuleDescription());
+        $this->assertSame($description, $violation->getRuleDescription());
     }

     /**
@@ @@
             new ViolationData('email', 'Required', 'required'),
             new ViolationData('email', 'Invalid format', 'email', 'Validates email format'),
             new ViolationData('password', 'Too short', 'min:8', 'Ensures minimum length of 8 characters'),
-            new ViolationData('name', 'Required', null), // No rule specified
+            new ViolationData('name', 'Required'), // No rule specified
         ];

         // Act & Assert: Verify each instance
-        $this->assertEquals('email', $violations[0]->getField());
-        $this->assertEquals('required', $violations[0]->getRule());
+        $this->assertSame('email', $violations[0]->getField());
+        $this->assertSame('required', $violations[0]->getRule());
         $this->assertNull($violations[0]->getRuleDescription());

-        $this->assertEquals('email', $violations[1]->getField());
-        $this->assertEquals('email', $violations[1]->getRule());
-        $this->assertEquals('Validates email format', $violations[1]->getRuleDescription());
+        $this->assertSame('email', $violations[1]->getField());
+        $this->assertSame('email', $violations[1]->getRule());
+        $this->assertSame('Validates email format', $violations[1]->getRuleDescription());

-        $this->assertEquals('password', $violations[2]->getField());
-        $this->assertEquals('min:8', $violations[2]->getRule());
-        $this->assertEquals('Ensures minimum length of 8 characters', $violations[2]->getRuleDescription());
+        $this->assertSame('password', $violations[2]->getField());
+        $this->assertSame('min:8', $violations[2]->getRule());
+        $this->assertSame('Ensures minimum length of 8 characters', $violations[2]->getRuleDescription());

-        $this->assertEquals('name', $violations[3]->getField());
-        $this->assertEquals('unknown', $violations[3]->getRule()); // Default when null
+        $this->assertSame('name', $violations[3]->getField());
+        $this->assertSame('unknown', $violations[3]->getRule()); // Default when null
         $this->assertNull($violations[3]->getRuleDescription());
     }
 }
    ----------- end diff -----------

Applied rules:
 * RemoveNullArgOnNullDefaultParamRector
 * AssertEqualsToSameRector


39) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/Rules/AbstractRuleTest.php:62

    ---------- begin diff ----------
@@ @@
         $description = $rule->getDescription();

         // Assert: Verify custom description is returned
-        $this->assertEquals($customDescription, $description);
+        $this->assertSame($customDescription, $description);
     }

     /**
    ----------- end diff -----------

Applied rules:
 * AssertEqualsToSameRector


40) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/Rules/AvailabilityDateRangeRuleTest.php:102

    ---------- begin diff ----------
@@ @@
                 $this->assertSame($this->rule, $rule);

                 if ($violationCount === 1) {
-                    $this->assertEquals('daily_time_range', $field);
-                    $this->assertEquals('Daily end time must be after start time', $message);
+                    $this->assertSame('daily_time_range', $field);
+                    $this->assertSame('Daily end time must be after start time', $message);
                 } else {
-                    $this->assertEquals('min_duration', $field);
-                    $this->assertEquals('Daily time slot duration must be at least 15 minutes', $message);
+                    $this->assertSame('min_duration', $field);
+                    $this->assertSame('Daily time slot duration must be at least 15 minutes', $message);
                 }
             });

@@ @@
     /**
      * Create a validation context mock for UPDATE operation.
      *
-     * @param Model|null $existingEntity
      * @return MockObject&ValidationContextInterface
      */
     private function createValidationContextWithUpdateOperation(?Model $existingEntity): MockObject
@@ @@
      * Configure context with all data fields.
      *
      * @param MockObject&ValidationContextInterface $context
-     * @param string $validityStart
-     * @param string $validityEnd
-     * @param string $dailyStart
-     * @param string $dailyEnd
      */
     private function configureContextWithData(
         MockObject $context,
@@ @@
      *
      * @param MockObject&ValidationContextInterface $context
      * @param array<int, string> $fieldsToProvide
-     * @param string|null $validityStart
-     * @param string|null $validityEnd
-     * @param string|null $dailyStart
-     * @param string|null $dailyEnd
      */
     private function configureContextWithPartialData(
         MockObject $context,
@@ @@
     /**
      * Create a stub entity with the given date and time values.
      *
-     * @param string $validityStart
-     * @param string $validityEnd
-     * @param string $dailyStart
-     * @param string $dailyEnd
      *
-     * @return Model
      */
     private function createEntityStub(
         string $validityStart,
@@ @@
     ): Model {
         $entity = new class extends Model {
             public $validity_start;
+
             public $validity_end;
+
             public $daily_start;
+
             public $daily_end;

             public function __construct()
    ----------- end diff -----------

Applied rules:
 * NewlineBetweenClassLikeStmtsRector
 * RemoveUselessParamTagRector
 * RemoveUselessReturnTagRector
 * AssertEqualsToSameRector


41) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/Rules/AvailabilityDaysCoherenceRuleTest.php:555

    ---------- begin diff ----------
@@ @@
     {
         $entity = new class extends Model {
             public $validity_start;
+
             public $validity_end;

             public function __construct()
             {
-                parent::__construct([]);
+                parent::__construct();
             }
         };
    ----------- end diff -----------

Applied rules:
 * NewlineBetweenClassLikeStmtsRector
 * RemoveArgumentFromDefaultParentCallRector


42) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/Rules/AvailabilityOverlapRuleTest.php:591

    ---------- begin diff ----------
@@ @@
     {
         return new class {
             public $id = 123;
+
             public $daily_start = '09:00:00';
+
             public $daily_end = '17:00:00';
+
             public $days = ['monday', 'tuesday'];
+
             public $validity_start = '2038-01-01';
+
             public $validity_end = '2038-12-31';
+
             public $type = 'consultation';
         };
     }
    ----------- end diff -----------

Applied rules:
 * NewlineBetweenClassLikeStmtsRector


43) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/Rules/AvailabilityOwnershipRuleTest.php:154

    ---------- begin diff ----------
@@ @@
     {
         // Arrange: Create context with null schedulable
         $context = $this->createValidationContext(
-            operationType: OperationType::CREATE,
-            schedulable: null
+            operationType: OperationType::CREATE
         );

         $this->configureContextHasMethod($context, hasAvailabilityId: true);
@@ @@

         $context = $this->createValidationContext(
             operationType: OperationType::UPDATE,
-            schedulable: $schedulable,
-            currentEntity: null
+            schedulable: $schedulable
         );

         $this->configureContextHasMethod($context, hasAvailabilityId: false);
@@ @@

     /**
      * Create a schedulable model mock.
-     *
-     * @param int $id
-     * @return Model
      */
     private function createSchedulableMock(int $id): Model
     {
@@ @@

     /**
      * Create an availability model mock.
-     *
-     * @param int $id
-     * @param int $schedulableId
-     * @param string|null $schedulableClass
-     * @return Availability
      */
     private function createAvailabilityMock(int $id, int $schedulableId, ?string $schedulableClass = null): Availability
     {
@@ @@

     /**
      * Create a stub schedule entity.
-     *
-     * @param int|null $availabilityId
-     * @return object
      */
     private function createScheduleEntityStub(?int $availabilityId): object
     {
         return new class($availabilityId) {
+            /**
+             * @var int|null
+             */
             public $availability_id;

             public function __construct(?int $availabilityId)
@@ @@
     /**
      * Create a validation context mock with given parameters.
      *
-     * @param OperationType $operationType
-     * @param Model|null $schedulable
-     * @param object|null $currentEntity
-     * @param AvailabilityService|null $availabilityService
      * @return MockObject&ValidationContextInterface
      */
     private function createValidationContext(
@@ @@
         $context->method('getSchedulable')->willReturn($schedulable);
         $context->method('getCurrentEntity')->willReturn($currentEntity);

-        if ($availabilityService !== null) {
+        if ($availabilityService instanceof AvailabilityService) {
             $context->method('getAvailabilityService')->willReturn($availabilityService);
         }

@@ @@
      * Configure the has() method on the validation context.
      *
      * @param MockObject&ValidationContextInterface $context
-     * @param bool $hasAvailabilityId
      */
     private function configureContextHasMethod(MockObject $context, bool $hasAvailabilityId): void
     {
@@ @@
      * Configure the get() method on the validation context.
      *
      * @param MockObject&ValidationContextInterface $context
-     * @param int|null $availabilityId
      */
     private function configureContextGetMethod(MockObject $context, ?int $availabilityId): void
     {
@@ @@

     /**
      * Configure an availability service mock with find() method expectation.
-     *
-     * @param int $availabilityId
-     * @param Availability|null $returnValue
-     * @return AvailabilityService
      */
     private function configureAvailabilityServiceWithFind(int $availabilityId, ?Availability $returnValue): AvailabilityService
     {
    ----------- end diff -----------

Applied rules:
 * FlipTypeControlToUseExclusiveTypeRector
 * RemoveUselessParamTagRector
 * RemoveUselessReturnTagRector
 * RemoveNullArgOnNullDefaultParamRector
 * TypedPropertyFromStrictConstructorRector


44) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/Rules/AvailabilityRulesTest.php:4

    ---------- begin diff ----------
@@ @@

 namespace Tests\Unit\Validation\Rules;

+use Roster\Validation\DTOs\ViolationData;
 use Roster\Enums\EntityType;
 use Roster\Enums\OperationType;
 use Roster\Validation\Context\ValidationContext;
@@ @@
 final class AvailabilityRulesTest extends TestCase
 {
     private RequiredFieldsRule $requiredFieldsRule;
+
     private AvailabilityOverlapRule $availabilityOverlapRule;
+
     private TestSchedulable $testSchedulable;

     /**
@@ @@

         $validityStartViolation = array_values(array_filter(
             $violations,
-            fn($v) => $v->getField() === 'validity_start'
+            fn(ViolationData $v): bool => $v->getField() === 'validity_start'
         ))[0] ?? null;
-        $this->assertNotNull($validityStartViolation);
+        $this->assertInstanceOf(ViolationData::class, $validityStartViolation);
         $this->assertStringContainsString('required', $validityStartViolation->getMessage());

         $dailyEndViolation = array_values(array_filter(
             $violations,
-            fn($v) => $v->getField() === 'daily_end'
+            fn(ViolationData $v): bool => $v->getField() === 'daily_end'
         ))[0] ?? null;
-        $this->assertNotNull($dailyEndViolation);
+        $this->assertInstanceOf(ViolationData::class, $dailyEndViolation);
         $this->assertStringContainsString('required', $dailyEndViolation->getMessage());
     }

@@ @@

         $schedulableIdViolation = array_values(array_filter(
             $violations,
-            fn($v) => $v->getField() === 'schedulable_id'
+            fn(ViolationData $v): bool => $v->getField() === 'schedulable_id'
         ))[0] ?? null;
-        $this->assertNotNull($schedulableIdViolation);
+        $this->assertInstanceOf(ViolationData::class, $schedulableIdViolation);
         $this->assertStringContainsString('cannot be changed', $schedulableIdViolation->getMessage());

         $schedulableTypeViolation = array_values(array_filter(
             $violations,
-            fn($v) => $v->getField() === 'schedulable_type'
+            fn(ViolationData $v): bool => $v->getField() === 'schedulable_type'
         ))[0] ?? null;
-        $this->assertNotNull($schedulableTypeViolation);
+        $this->assertInstanceOf(ViolationData::class, $schedulableTypeViolation);
         $this->assertStringContainsString('cannot be changed', $schedulableTypeViolation->getMessage());
     }
    ----------- end diff -----------

Applied rules:
 * NewlineBetweenClassLikeStmtsRector
 * AssertEmptyNullableObjectToAssertInstanceofRector
 * AddArrowFunctionReturnTypeRector
 * AddArrayFunctionClosureParamTypeRector


45) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/Rules/AvailabilityTemporalCoherenceRuleTest.php:4

    ---------- begin diff ----------
@@ @@

 namespace Tests\Unit\Validation\Rules;

+use Illuminate\Support\Carbon;
 use Illuminate\Database\Eloquent\Model;
 use Illuminate\Foundation\Testing\RefreshDatabase;
 use Roster\Validation\Exceptions\ValidationFailedException;
@@ @@
         $this->assertTrue($result);
         $this->assertDatabaseHas('roster_availabilities', [
             'id' => $availability->id,
-            'validity_end' => \Illuminate\Support\Carbon::parse('2038-01-20')->startOfDay(),
+            'validity_end' => Carbon::parse('2038-01-20')->startOfDay(),
         ]);
     }
    ----------- end diff -----------

Applied rules:


46) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/Rules/DateRangeRulesTest.php:4

    ---------- begin diff ----------
@@ @@

 namespace Tests\Unit\Validation\Rules;

+use Roster\Validation\DTOs\ViolationData;
 use stdClass;
 use Roster\Enums\EntityType;
 use Roster\Enums\OperationType;
@@ @@
 final class DateRangeRulesTest extends TestCase
 {
     private AvailabilityDateRangeRule $availabilityDateRangeRule;
+
     private TimeSlotDateTimeRule $timeSlotDateTimeRule;
+
     private TestSchedulable $testSchedulable;

     /**
@@ @@

         $violation = array_values(array_filter(
             $validationContext->getViolations(),
-            fn($v) => $v->getField() === 'max_duration'
+            fn(ViolationData $v): bool => $v->getField() === 'max_duration'
         ))[0] ?? null;

-        $this->assertNotNull($violation);
+        $this->assertInstanceOf(ViolationData::class, $violation);
         $this->assertStringContainsString('cannot exceed 365 days', $violation->getMessage());
     }
    ----------- end diff -----------

Applied rules:
 * NewlineBetweenClassLikeStmtsRector
 * AssertEmptyNullableObjectToAssertInstanceofRector
 * AddArrowFunctionReturnTypeRector
 * AddArrayFunctionClosureParamTypeRector


47) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/Rules/DurationRuleTest.php:5

    ---------- begin diff ----------
@@ @@
 namespace Tests\Unit\Validation\Rules;

 use Illuminate\Support\Carbon;
-use PHPUnit\Framework\MockObject\MockObject;
 use Roster\Contracts\Validation\ValidationContextInterface;
 use Roster\Enums\EntityType;
 use Roster\Enums\OperationType;
@@ @@
         $this->rule->validate($context);

         // Assert: Different time formats should be handled correctly
-    }
-
-    /**
-     * Create a mock validation context with specific configuration.
-     *
-     * @param EntityType $entityType
-     * @param OperationType $operationType
-     * @param array<string, mixed> $data
-     * @return MockObject&ValidationContextInterface
-     */
-    private function createValidationContext(
-        EntityType $entityType,
-        OperationType $operationType,
-        array $data = []
-    ): MockObject {
-        $context = $this->createMock(ValidationContextInterface::class);
-        $context->method('getEntityType')->willReturn($entityType);
-        $context->method('getOperation')->willReturn($operationType);
-        $context->method('safeData')->willReturn($data);
-
-        return $context;
     }
 }
    ----------- end diff -----------

Applied rules:
 * RemoveUnusedPrivateMethodRector


48) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/Rules/FutureDateRuleTest.php:4

    ---------- begin diff ----------
@@ @@

 namespace Tests\Unit\Validation\Rules;

-use Exception;
 use Illuminate\Support\Carbon;
 use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
 use PHPUnit\Framework\MockObject\MockObject;
@@ @@
             operationType: OperationType::CREATE,
             hasValidityStart: true,
             validityStart: $futureDate,
-            hasDailyStart: false,
-            dailyStart: null
+            hasDailyStart: false
         );

         $context->expects($this->never())->method('setViolationFromRule');
@@ @@
                 if ($key === 'validity_start') {
                     return $hasValidityStart;
                 }
+
                 if ($key === 'daily_start') {
                     return $hasDailyStart;
                 }
+
                 return false;
             }
         );
@@ @@
                 if ($key === 'validity_start') {
                     return $validityStart;
                 }
+
                 if ($key === 'daily_start') {
                     return $dailyStart;
                 }
+
                 return null;
             }
         );
    ----------- end diff -----------

Applied rules:
 * NewlineAfterStatementRector
 * RemoveNullArgOnNullDefaultParamRector


49) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/Rules/ImpedimentScheduleDaysCoherenceRuleTest.php:27

    ---------- begin diff ----------
@@ @@
     use RefreshDatabase;

     private Model $schedulable;
+
     private ImpedimentScheduleDaysCoherenceRule $rule;

     /**
    ----------- end diff -----------

Applied rules:
 * NewlineBetweenClassLikeStmtsRector


50) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/Rules/RequiredFieldsRuleTest.php:4

    ---------- begin diff ----------
@@ @@

 namespace Tests\Unit\Validation\Rules;

-use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
 use PHPUnit\Framework\MockObject\MockObject;
 use Roster\Contracts\Validation\ValidationContextInterface;
 use Roster\Enums\EntityType;
@@ @@
         $expectedFields = ['daily_start', 'daily_end', 'days', 'validity_start', 'validity_end'];
         foreach ($expectedFields as $field) {
             $this->assertArrayHasKey($field, $violations);
-            $this->assertSame("Field '{$field}' is required", $violations[$field]);
+            $this->assertSame(sprintf("Field '%s' is required", $field), $violations[$field]);
         }
     }

@@ @@
             ->method('setViolationFromRule')
             ->willReturnCallback(function ($rule) use (&$violationCount): void {
                 $this->assertSame($this->requiredFieldsRule, $rule);
-                $violationCount++;
+                ++$violationCount;
             });

         // Act: Execute validation
    ----------- end diff -----------

Applied rules:
 * EncapsedStringsToSprintfRector
 * PostIncDecToPreIncDecRector


51) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/Rules/SchedulableConsistencyRuleTest.php:571

    ---------- begin diff ----------
@@ @@

         // Assert: Should fail with empty schedulable_type
     }
+
     /**
      * Test that rule description is available.
      */
    ----------- end diff -----------

Applied rules:
 * NewlineBetweenClassLikeStmtsRector


52) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/Rules/SchedulableValidationRuleTest.php:911

    ---------- begin diff ----------
@@ @@

     /**
      * Create a mock model stub with specified ID.
-     *
-     * @param int $id
-     * @return Model
      */
     private function createModelStub(int $id): Model
     {
@@ @@
     /**
      * Create a validation context mock for CREATE operation.
      *
-     * @param EntityType $entityType
-     * @param Model|null $schedulable
      * @param array<string, mixed> $data
      * @return MockObject&ValidationContextInterface
      */
@@ @@
     /**
      * Create a validation context mock for UPDATE operation.
      *
-     * @param EntityType $entityType
-     * @param Model $schedulable
      * @param array<string, mixed> $data
      * @return MockObject&ValidationContextInterface
      */
@@ @@
     /**
      * Create a validation context mock for DELETE operation.
      *
-     * @param EntityType $entityType
-     * @param Model $schedulable
      * @return MockObject&ValidationContextInterface
      */
     private function createContextForDeleteOperation(
@@ @@
      * Configure context get method for schedulable fields.
      *
      * @param MockObject&ValidationContextInterface $context
-     * @param mixed $schedulableId
-     * @param string|null $schedulableType
      */
     private function configureContextGetMethod(
         MockObject $context,
    ----------- end diff -----------

Applied rules:
 * RemoveUselessParamTagRector
 * RemoveUselessReturnTagRector


 [OK] 52 files would have been changed (dry-run) by Rector                                                              

