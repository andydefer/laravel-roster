# Pint Code Formatter Report
*Generated: mer. 24 déc. 2025 05:30:09 WAT*


  ..⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯...⨯.⨯⨯⨯..⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯..⨯....⨯⨯⨯⨯⨯⨯⨯⨯⨯⨯.....⨯...⨯⨯⨯⨯.............⨯....⨯⨯⨯.........⨯......⨯⨯...⨯......⨯.....⨯....

  ──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────── Laravel  
    FAIL   ................................................................................................................................................ 144 files, 72 style issues  
  ⨯ config/roster-validation.php                                                                                                                                  no_extra_blank_lines  
  @@ -75,6 +75,4 @@
           'always_cache_in_production' => true,
       ],
   
  -
  -
   ];
  
  ⨯ config/roster.php                                                                                                                                             no_extra_blank_lines  
  @@ -24,7 +24,6 @@
           'validation_field' => 'start_datetime',
       ],
   
  -
       // Duration constraints
       'durations' => [
           // Minimum durations in minutes
  @@ -112,6 +111,5 @@
   
           'use_tags' => env('ROSTER_CACHE_USE_TAGS', true),
       ],
  -
   
   ];
  
  ⨯ src/Commands/CacheRulesCommand.php                        single_quote, blank_line_after_opening_tag, concat_space, not_operator_with_successor_space, blank_line_before_statement  
  @@ -1,4 +1,5 @@
   <?php
  +
   // src/Commands/CacheRulesCommand.php
   
   declare(strict_types=1);
  @@ -41,7 +42,7 @@
   
           if ($generator->generate()) {
               $duration = round((microtime(true) - $start) * 1000, 2);
  -            $this->info("✅ Cache generated successfully at: " . $generator->getCachePath());
  +            $this->info('✅ Cache generated successfully at: '.$generator->getCachePath());
               $this->info("⏱️  Duration: {$duration}ms");
   
               // Afficher des stats
  @@ -51,6 +52,7 @@
           }
   
           $this->error('Failed to generate cache');
  +
           return self::FAILURE;
       }
   
  @@ -68,6 +70,7 @@
           }
   
           $this->error('Failed to clear cache');
  +
           return self::FAILURE;
       }
   
  @@ -75,19 +78,19 @@
       {
           $cacheFile = $generator->getCachePath();
   
  -        if (!file_exists($cacheFile)) {
  -            $this->warn('Cache file does not exist: ' . $cacheFile);
  +        if (! file_exists($cacheFile)) {
  +            $this->warn('Cache file does not exist: '.$cacheFile);
               $this->info('Generating cache automatically...');
   
               // Générer le cache automatiquement
               $generator->generate();
   
  -            $this->info("✅ Cache generated successfully at: " . $cacheFile);
  +            $this->info('✅ Cache generated successfully at: '.$cacheFile);
           }
   
           // Maintenant on est sûr que le fichier existe, on peut l'afficher
           $rules = require $cacheFile;
  -        $this->info('Rules count: ' . count($rules));
  +        $this->info('Rules count: '.count($rules));
   
           // Préparer les lignes du tableau avec index
           $rows = [];
  @@ -118,10 +121,10 @@
               $size = filesize($cacheFile);
               $rules = require $cacheFile;
   
  -            $this->line("📊 Cache stats:");
  -            $this->line("   Size: " . $this->formatBytes($size));
  -            $this->line("   Rules: " . count($rules));
  -            $this->line("   Path: " . $cacheFile);
  +            $this->line('📊 Cache stats:');
  +            $this->line('   Size: '.$this->formatBytes($size));
  +            $this->line('   Rules: '.count($rules));
  +            $this->line('   Path: '.$cacheFile);
           }
       }
   
  @@ -135,6 +138,6 @@
               $i++;
           }
   
  -        return round($bytes, 2) . ' ' . $units[$i];
  +        return round($bytes, 2).' '.$units[$i];
       }
   }
  
  ⨯ src/Commands/InstallRosterCommand.php                                                        unary_operator_spaces, not_operator_with_successor_space, blank_line_before_statement  
  @@ -43,6 +43,7 @@
   
               if (! $this->confirm('Continue?', true)) {
                   $this->info('Installation cancelled.');
  +
                   return self::SUCCESS;
               }
           }
  
  ⨯ src/Contracts/Repository/AvailabilityRepositoryInterface.php                                                                                                          phpdoc_align  
  @@ -38,8 +38,8 @@
       /**
        * Find availabilities for a specific schedulable entity.
        *
  -     * @param Model $model The schedulable entity
  -     * @param string|null $type Optional availability type filter
  +     * @param  Model  $model  The schedulable entity
  +     * @param  string|null  $type  Optional availability type filter
        * @return Collection<int, Availability> Collection of availabilities for the schedulable
        */
       public function findForSchedulable(Model $model, ?string $type = null): Collection;
  
  ⨯ src/Contracts/Repository/ImpedimentRepositoryInterface.php                                                                                                            phpdoc_align  
  @@ -16,7 +16,7 @@
       /**
        * Create a new impediment.
        *
  -     * @param array<string, mixed> $data
  +     * @param  array<string, mixed>  $data
        */
       public function create(array $data): Impediment;
   
  @@ -23,7 +23,7 @@
       /**
        * Update an existing impediment.
        *
  -     * @param array<string, mixed> $data
  +     * @param  array<string, mixed>  $data
        */
       public function update(int $id, array $data): bool;
   
  @@ -47,9 +47,9 @@
       /**
        * Find impediments for a specific time slot.
        *
  -     * @param int $availabilityId The availability ID
  -     * @param Carbon $start Start of time slot
  -     * @param Carbon $end End of time slot
  +     * @param  int  $availabilityId  The availability ID
  +     * @param  Carbon  $start  Start of time slot
  +     * @param  Carbon  $end  End of time slot
        * @return Collection<int, Impediment>
        */
       public function findForTimeSlot(int $availabilityId, Carbon $start, Carbon $end): Collection;
  @@ -57,10 +57,10 @@
       /**
        * Check if a time slot has overlapping impediments.
        *
  -     * @param int $availabilityId The availability ID
  -     * @param Carbon $start Start of time slot
  -     * @param Carbon $end End of time slot
  -     * @param int|null $excludeId Impediment ID to exclude
  +     * @param  int  $availabilityId  The availability ID
  +     * @param  Carbon  $start  Start of time slot
  +     * @param  Carbon  $end  End of time slot
  +     * @param  int|null  $excludeId  Impediment ID to exclude
        * @return bool True if overlapping impediments exist
        */
       public function hasOverlappingImpediments(
  @@ -73,10 +73,10 @@
       /**
        * Find overlapping impediments with a time range.
        *
  -     * @param int $availabilityId The availability ID
  -     * @param Carbon $start Start of time range
  -     * @param Carbon $end End of time range
  -     * @param int|null $excludeId Impediment ID to exclude
  +     * @param  int  $availabilityId  The availability ID
  +     * @param  Carbon  $start  Start of time range
  +     * @param  Carbon  $end  End of time range
  +     * @param  int|null  $excludeId  Impediment ID to exclude
        * @return Collection<int, Impediment>
        */
       public function findOverlappingImpediments(
  
  ⨯ src/Contracts/Services/AvailabilityValidatorInterface.php                                                                                              class_attributes_separation  
  @@ -19,7 +19,6 @@
        */
       public function validateBasicData(array $data): void;
   
  -
       /**
        * Check if two time ranges overlap within a single day.
        *
  
  ⨯ src/Contracts/Services/ConfigurableInterface.php                                                                                         class_attributes_separation, phpdoc_align  
  @@ -10,7 +10,6 @@
    */
   interface ConfigurableInterface
   {
  -
       /**
        * Get the entity type identifier.
        *
  @@ -28,7 +27,7 @@
       /**
        * Get date and time fields for a specific entity type.
        *
  -     * @param string $entityType The entity type identifier
  +     * @param  string  $entityType  The entity type identifier
        * @return array<string> List of date/time field names
        */
       public function getDateTimeFields(string $entityType): array;
  
  ⨯ src/Contracts/Services/SlotFinderInterface.php                                                                                                         class_attributes_separation  
  @@ -10,7 +10,6 @@
   
   interface SlotFinderInterface
   {
  -
       /**
        * Check if an entire time period is available without interruptions.
        *
  @@ -26,7 +25,6 @@
           Carbon $end,
           ?string $type = null
       ): bool;
  -
   
       /**
        * Calculate available time slots between impediments.
  
  ⨯ src/DTOs/AvailabilityData.php class_attributes_separation, function_declaration, braces_position, cast_spaces, not_operator_with_successor_space, single_line_empty_body, phpdoc_a  
  @@ -23,7 +23,7 @@
       ) {}
   
       /**
  -     * @param array<string, mixed> $data
  +     * @param  array<string, mixed>  $data
        */
       public static function fromArray(array $data): self
       {
  @@ -30,7 +30,7 @@
           return new self(
               id: $data['id'] ?? null,
               type: $data['type'] ?? null,
  -            days: isset($data['days']) ? (array)$data['days'] : null,
  +            days: isset($data['days']) ? (array) $data['days'] : null,
               validityStart: isset($data['validity_start']) ? Carbon::parse($data['validity_start']) : null,
               validityEnd: isset($data['validity_end']) ? Carbon::parse($data['validity_end']) : null,
               dailyStart: isset($data['daily_start']) ? Carbon::parse($data['daily_start']) : null,
  @@ -70,10 +70,9 @@
               'daily_end' => $this->dailyEnd?->format('H:i:s'),
               'schedulable_id' => $this->schedulableId,
               'schedulable_type' => $this->schedulableType,
  -        ], static fn(int|string|array|null $value): bool => $value !== null);
  +        ], static fn (int|string|array|null $value): bool => $value !== null);
       }
   
  -
       public function withSchedulableInfo(?int $schedulableId, ?string $schedulableType): self
       {
           return new self(
  @@ -138,7 +137,7 @@
           $newValidityEnd = $this->validityEnd ?? $existingValidityEnd;
   
           // Si aucune date de validité n'est fournie ou si les dates n'ont pas changé, retourner tel quel
  -        if (!$newValidityStart instanceof Carbon || !$newValidityEnd instanceof Carbon) {
  +        if (! $newValidityStart instanceof Carbon || ! $newValidityEnd instanceof Carbon) {
               return $this->withDaysInfo($existingDays);
           }
   
  @@ -148,7 +147,7 @@
           $datesChanged = $startChanged || $endChanged;
   
           // Si les dates n'ont pas changé, retourner les jours existants
  -        if (!$datesChanged) {
  +        if (! $datesChanged) {
               return $this->withDaysInfo($existingDays);
           }
   
  @@ -169,12 +168,12 @@
           }
   
           // Si pas de dates de validité, utiliser tous les jours
  -        if (!$this->validityStart instanceof Carbon || !$this->validityEnd instanceof Carbon) {
  +        if (! $this->validityStart instanceof Carbon || ! $this->validityEnd instanceof Carbon) {
               return DaysOfWeek::values();
           }
   
           // Utiliser le helper pour déterminer si on doit ajuster
  -        if (!roster_should_auto_adjust_days($this->validityStart, $this->validityEnd)) {
  +        if (! roster_should_auto_adjust_days($this->validityStart, $this->validityEnd)) {
               return DaysOfWeek::values();
           }
   
  @@ -189,7 +188,7 @@
       {
           $daysToFilter = $existingDays ?? $this->days ?? [];
   
  -        if ($daysToFilter === [] || !$this->validityStart instanceof Carbon || !$this->validityEnd instanceof Carbon) {
  +        if ($daysToFilter === [] || ! $this->validityStart instanceof Carbon || ! $this->validityEnd instanceof Carbon) {
               return $daysToFilter;
           }
   
  @@ -202,13 +201,13 @@
        */
       public function hasValidDays(): bool
       {
  -        if (!is_array($this->days) || $this->days === []) {
  +        if (! is_array($this->days) || $this->days === []) {
               return false;
           }
   
           $validDays = DaysOfWeek::values();
           foreach ($this->days as $day) {
  -            if (!in_array($day, $validDays, true)) {
  +            if (! in_array($day, $validDays, true)) {
                   return false;
               }
           }
  @@ -233,7 +232,7 @@
        */
       public function isDayInPeriod(string $day): bool
       {
  -        if (!$this->validityStart instanceof Carbon || !$this->validityEnd instanceof Carbon) {
  +        if (! $this->validityStart instanceof Carbon || ! $this->validityEnd instanceof Carbon) {
               return false;
           }
   
  @@ -245,7 +244,7 @@
        */
       public function getPeriodDurationInDays(): ?int
       {
  -        if (!$this->validityStart instanceof Carbon || !$this->validityEnd instanceof Carbon) {
  +        if (! $this->validityStart instanceof Carbon || ! $this->validityEnd instanceof Carbon) {
               return null;
           }
   
  
  ⨯ src/DTOs/ImpedimentData.php                                                                            function_declaration, braces_position, single_line_empty_body, phpdoc_align  
  @@ -21,7 +21,7 @@
       ) {}
   
       /**
  -     * @param array<string, mixed> $data
  +     * @param  array<string, mixed>  $data
        */
       public static function fromArray(array $data): self
       {
  @@ -72,7 +72,7 @@
               'metadata' => $this->metadata,
               'schedulable_id' => $this->schedulableId,
               'schedulable_type' => $this->schedulableType,
  -        ], static fn(int|string|array|null $value): bool => $value !== null);
  +        ], static fn (int|string|array|null $value): bool => $value !== null);
       }
   
       public function withSchedulableInfo(?int $schedulableId, ?string $schedulableType): self
  
  ⨯ src/DTOs/ScheduleData.php                                                                                    function_declaration, not_operator_with_successor_space, phpdoc_align  
  @@ -31,7 +31,7 @@
       }
   
       /**
  -     * @param array<string, mixed> $data
  +     * @param  array<string, mixed>  $data
        */
       public static function fromArray(array $data): self
       {
  @@ -49,7 +49,7 @@
           }
   
           // Si c'est déjà un enum, le garder tel quel
  -        if (!$status instanceof ScheduleStatus && !is_string($status)) {
  +        if (! $status instanceof ScheduleStatus && ! is_string($status)) {
               $status = ScheduleStatus::AVAILABLE;
           }
   
  @@ -109,7 +109,7 @@
               'status' => $status,
               'schedulable_id' => $this->schedulableId,
               'schedulable_type' => $this->schedulableType,
  -        ], static fn($value) => $value !== null);
  +        ], static fn ($value) => $value !== null);
       }
   
       public function withSchedulableInfo(?int $schedulableId, ?string $schedulableType): self
  
  ⨯ src/Enums/EntityType.php                                                                                                                                              concat_space  
  @@ -38,7 +38,7 @@
               'availability' => self::AVAILABILITY,
               'schedule' => self::SCHEDULE,
               'impediment' => self::IMPEDIMENT,
  -            default => throw new InvalidArgumentException('Unknown entity type: ' . $type)
  +            default => throw new InvalidArgumentException('Unknown entity type: '.$type)
           };
       }
   }
  
  ⨯ src/Exceptions/Messages/ErrorMessageFactory.php                                                                                                         concat_space, phpdoc_align  
  @@ -12,8 +12,8 @@
       /**
        * Create error message for date/time values in the past.
        *
  -     * @param string $entity Entity name (e.g., 'availability', 'shift')
  -     * @param string $field Field name (e.g., 'start_date', 'end_time')
  +     * @param  string  $entity  Entity name (e.g., 'availability', 'shift')
  +     * @param  string  $field  Field name (e.g., 'start_date', 'end_time')
        * @return string Formatted error message
        */
       public static function pastDate(string $entity, string $field): string
  @@ -28,8 +28,8 @@
       /**
        * Create error message for minimum duration requirement.
        *
  -     * @param string $entity Entity name
  -     * @param int $minutes Minimum required minutes
  +     * @param  string  $entity  Entity name
  +     * @param  int  $minutes  Minimum required minutes
        * @return string Formatted error message
        */
       public static function minimumDuration(string $entity, int $minutes): string
  @@ -40,7 +40,7 @@
       /**
        * Create error message for overlapping entities.
        *
  -     * @param string $entity Entity name
  +     * @param  string  $entity  Entity name
        * @return string Formatted error message
        */
       public static function overlap(string $entity): string
  @@ -51,18 +51,18 @@
       /**
        * Create error message for entity not found.
        *
  -     * @param string $entity Entity name
  +     * @param  string  $entity  Entity name
        * @return string Formatted error message
        */
       public static function notFound(string $entity): string
       {
  -        return ucfirst($entity) . ' not found';
  +        return ucfirst($entity).' not found';
       }
   
       /**
        * Create error message for required field.
        *
  -     * @param string $field Field name
  +     * @param  string  $field  Field name
        * @return string Formatted error message
        */
       public static function requiredField(string $field): string
  @@ -73,11 +73,11 @@
       /**
        * Create error message for invalid timezone.
        *
  -     * @param string $timezone Invalid timezone string
  +     * @param  string  $timezone  Invalid timezone string
        * @return string Formatted error message
        */
       public static function invalidTimezone(string $timezone): string
       {
  -        return 'Invalid timezone: ' . $timezone;
  +        return 'Invalid timezone: '.$timezone;
       }
   }
  
  ⨯ src/Exceptions/NotFoundException.php                                                                                        cast_spaces, blank_line_before_statement, phpdoc_align  
  @@ -24,9 +24,9 @@
       /**
        * Create a new NotFoundException instance.
        *
  -     * @param string $message Custom error message
  -     * @param int $code Custom HTTP status code
  -     * @param Exception|null $previous Previous exception
  +     * @param  string  $message  Custom error message
  +     * @param  int  $code  Custom HTTP status code
  +     * @param  Exception|null  $previous  Previous exception
        */
       public function __construct(string $message = '', int $code = 0, ?Exception $previous = null)
       {
  @@ -44,13 +44,13 @@
       /**
        * Create a NotFoundException for a specific entity.
        *
  -     * @param string $entityType Type of entity (e.g., 'Availability', 'Schedule')
  -     * @param int|string $identifier Entity ID or identifier
  -     * @param Exception|null $exception Previous exception
  +     * @param  string  $entityType  Type of entity (e.g., 'Availability', 'Schedule')
  +     * @param  int|string  $identifier  Entity ID or identifier
  +     * @param  Exception|null  $exception  Previous exception
        */
       public static function forEntity(string $entityType, $identifier, ?Exception $exception = null): self
       {
  -        $message = sprintf('%s with ID %s not found', $entityType, (string)$identifier);
  +        $message = sprintf('%s with ID %s not found', $entityType, (string) $identifier);
   
           return new self($message, 404, $exception);
       }
  @@ -58,10 +58,10 @@
       /**
        * Create a NotFoundException for a schedulable entity.
        *
  -     * @param string $entityType Type of entity
  -     * @param int $entityId Entity ID
  -     * @param string $schedulableType Type of schedulable
  -     * @param int $schedulableId Schedulable ID
  +     * @param  string  $entityType  Type of entity
  +     * @param  int  $entityId  Entity ID
  +     * @param  string  $schedulableType  Type of schedulable
  +     * @param  int  $schedulableId  Schedulable ID
        */
       public static function forSchedulableEntity(
           string $entityType,
  @@ -83,10 +83,10 @@
       /**
        * Create a NotFoundException for a relationship.
        *
  -     * @param string $parentEntity Parent entity type
  -     * @param int $parentId Parent entity ID
  -     * @param string $childEntity Child entity type
  -     * @param int $childId Child entity ID
  +     * @param  string  $parentEntity  Parent entity type
  +     * @param  int  $parentId  Parent entity ID
  +     * @param  string  $childEntity  Child entity type
  +     * @param  int  $childId  Child entity ID
        */
       public static function forRelationship(
           string $parentEntity,
  @@ -108,7 +108,7 @@
       /**
        * Create a NotFoundException for availability.
        *
  -     * @param int $availabilityId Availability ID
  +     * @param  int  $availabilityId  Availability ID
        */
       public static function forAvailability(int $availabilityId): self
       {
  @@ -118,7 +118,7 @@
       /**
        * Create a NotFoundException for schedule.
        *
  -     * @param int $scheduleId Schedule ID
  +     * @param  int  $scheduleId  Schedule ID
        */
       public static function forSchedule(int $scheduleId): self
       {
  @@ -128,7 +128,7 @@
       /**
        * Create a NotFoundException for impediment.
        *
  -     * @param int $impedimentId Impediment ID
  +     * @param  int  $impedimentId  Impediment ID
        */
       public static function forImpediment(int $impedimentId): self
       {
  @@ -146,12 +146,13 @@
       /**
        * Create a NotFoundException for time slot.
        *
  -     * @param string $start Start datetime
  -     * @param string $end End datetime
  +     * @param  string  $start  Start datetime
  +     * @param  string  $end  End datetime
        */
       public static function forTimeSlot(string $start, string $end): self
       {
           $message = sprintf('No availability found for time slot %s to %s', $start, $end);
  +
           return new self($message);
       }
   
  @@ -172,7 +173,7 @@
       /**
        * Check if the exception is for a specific entity type.
        *
  -     * @param string $entityType Entity type to check
  +     * @param  string  $entityType  Entity type to check
        */
       public function isForEntity(string $entityType): bool
       {
  @@ -206,7 +207,7 @@
           $message = $this->getMessage();
   
           if (preg_match('/ID (\d+)/', $message, $matches)) {
  -            return (int)$matches[1];
  +            return (int) $matches[1];
           }
   
           return null;
  
  ⨯ src/Exceptions/RosterException.php                                                                                                                                    phpdoc_align  
  @@ -18,11 +18,11 @@
       /**
        * Create a new RosterException instance.
        *
  -     * @param string $type Unique identifier for the exception type
  -     * @param string $message Human-readable error message
  -     * @param array $context Additional context data for debugging
  -     * @param int $code Error code
  -     * @param Throwable|null $previous Previous exception in the chain
  +     * @param  string  $type  Unique identifier for the exception type
  +     * @param  string  $message  Human-readable error message
  +     * @param  array  $context  Additional context data for debugging
  +     * @param  int  $code  Error code
  +     * @param  Throwable|null  $previous  Previous exception in the chain
        */
       public function __construct(
           protected string $type,
  
  ⨯ src/Models/Availability.php                                                                           trailing_comma_in_multiline, not_operator_with_successor_space, phpdoc_align  
  @@ -54,7 +54,7 @@
           'daily_end' => 'datetime:H:i',
           'validity_start' => 'date',
           'validity_end' => 'date',
  -        'days' => 'array'
  +        'days' => 'array',
       ];
   
       /**
  @@ -87,17 +87,17 @@
        * Checks if the given time period falls within this availability's
        * defined days, time window, and date range.
        *
  -     * @param Carbon $start Start time of the period to check
  -     * @param Carbon $end End time of the period to check
  +     * @param  Carbon  $start  Start time of the period to check
  +     * @param  Carbon  $end  End time of the period to check
        * @return bool True if the period is available, false otherwise
        */
       public function isAvailableForSchedule(Carbon $start, Carbon $end): bool
       {
  -        if (!$this->isAvailableOnDay($start)) {
  +        if (! $this->isAvailableOnDay($start)) {
               return false;
           }
   
  -        if (!$this->isWithinDailyWindow($start, $end)) {
  +        if (! $this->isWithinDailyWindow($start, $end)) {
               return false;
           }
   
  @@ -107,7 +107,7 @@
       /**
        * Check if the availability includes the given day of week.
        *
  -     * @param Carbon $date Date to check
  +     * @param  Carbon  $date  Date to check
        * @return bool True if the day is available, false otherwise
        */
       private function isAvailableOnDay(Carbon $date): bool
  @@ -120,8 +120,8 @@
       /**
        * Check if the time period falls within the availability's daily time window.
        *
  -     * @param Carbon $start Start time to check
  -     * @param Carbon $end End time to check
  +     * @param  Carbon  $start  Start time to check
  +     * @param  Carbon  $end  End time to check
        * @return bool True if within daily time window, false otherwise
        */
       private function isWithinDailyWindow(Carbon $start, Carbon $end): bool
  @@ -137,8 +137,8 @@
       /**
        * Check if the time period falls within the availability's validity period.
        *
  -     * @param Carbon $start Start date to check
  -     * @param Carbon $end End date to check
  +     * @param  Carbon  $start  Start date to check
  +     * @param  Carbon  $end  End date to check
        * @return bool True if within validity period, false otherwise
        */
       private function isWithinValidityPeriod(Carbon $start, Carbon $end): bool
  @@ -147,18 +147,18 @@
               return false;
           }
   
  -        return !($this->validity_end && $end->gt($this->validity_end));
  +        return ! ($this->validity_end && $end->gt($this->validity_end));
       }
   
       /**
        * Check if the availability is active on a specific date.
        *
  -     * @param Carbon $date Date to check
  +     * @param  Carbon  $date  Date to check
        * @return bool True if active on the given date, false otherwise
        */
       public function isActiveOnDate(Carbon $date): bool
       {
  -        if (!$this->isAvailableOnDay($date)) {
  +        if (! $this->isAvailableOnDay($date)) {
               return false;
           }
   
  @@ -166,7 +166,7 @@
               return false;
           }
   
  -        return !($this->validity_end && $date->gt($this->validity_end));
  +        return ! ($this->validity_end && $date->gt($this->validity_end));
       }
   
       /**
  @@ -186,7 +186,7 @@
        */
       public function getValidityDurationDays(): ?int
       {
  -        if (!$this->validity_start || !$this->validity_end) {
  +        if (! $this->validity_start || ! $this->validity_end) {
               return null;
           }
   
  @@ -206,7 +206,7 @@
       /**
        * Check if the validity period has started.
        *
  -     * @param Carbon|null $date Optional date to check (defaults to now)
  +     * @param  Carbon|null  $date  Optional date to check (defaults to now)
        * @return bool True if validity period has started
        */
       public function hasValidityStarted(?Carbon $date = null): bool
  @@ -223,7 +223,7 @@
       /**
        * Check if the validity period has ended.
        *
  -     * @param Carbon|null $date Optional date to check (defaults to now)
  +     * @param  Carbon|null  $date  Optional date to check (defaults to now)
        * @return bool True if validity period has ended
        */
       public function hasValidityEnded(?Carbon $date = null): bool
  @@ -240,7 +240,7 @@
       /**
        * Check if the validity period is currently active.
        *
  -     * @param Carbon|null $date Optional date to check (defaults to now)
  +     * @param  Carbon|null  $date  Optional date to check (defaults to now)
        * @return bool True if currently within validity period
        */
       public function isValidityActive(?Carbon $date = null): bool
  @@ -247,6 +247,6 @@
       {
           $date = $date ?? Carbon::now();
   
  -        return $this->hasValidityStarted($date) && !$this->hasValidityEnded($date);
  +        return $this->hasValidityStarted($date) && ! $this->hasValidityEnded($date);
       }
   }
  
  ⨯ src/Models/Impediment.php                                                                         class_attributes_separation, function_declaration, ordered_imports, phpdoc_align  
  @@ -5,9 +5,9 @@
   namespace Roster\Models;
   
   use Illuminate\Database\Eloquent\Casts\Attribute;
  -use Illuminate\Database\Eloquent\Relations\MorphTo;
   use Illuminate\Database\Eloquent\Model;
   use Illuminate\Database\Eloquent\Relations\BelongsTo;
  +use Illuminate\Database\Eloquent\Relations\MorphTo;
   use Illuminate\Support\Carbon;
   use Roster\Traits\BelongsToSchedulable;
   
  @@ -49,7 +49,6 @@
           'end_datetime' => 'datetime',
       ];
   
  -
       /**
        * Accessor & mutator for metadata.
        * Accepts either a JSON string or an array from the user.
  @@ -57,8 +56,8 @@
       protected function metadata(): Attribute
       {
           return Attribute::make(
  -            get: fn($value) => is_string($value) ? json_decode($value, true) : $value,
  -            set: fn($value) => is_array($value) ? json_encode($value) : $value
  +            get: fn ($value) => is_string($value) ? json_decode($value, true) : $value,
  +            set: fn ($value) => is_array($value) ? json_encode($value) : $value
           );
       }
   
  @@ -85,8 +84,8 @@
       /**
        * Determine if this impediment overlaps with a given time period.
        *
  -     * @param Carbon $start The start of the period to check
  -     * @param Carbon $end The end of the period to check
  +     * @param  Carbon  $start  The start of the period to check
  +     * @param  Carbon  $end  The end of the period to check
        * @return bool True if the impediment overlaps with the period
        */
       public function overlapsWith(Carbon $start, Carbon $end): bool
  
  ⨯ src/Models/Schedule.php                                                                                                                              ordered_imports, phpdoc_align  
  @@ -4,9 +4,9 @@
   
   namespace Roster\Models;
   
  -use Illuminate\Database\Eloquent\Relations\Relation;
   use Illuminate\Database\Eloquent\Model;
   use Illuminate\Database\Eloquent\Relations\BelongsTo;
  +use Illuminate\Database\Eloquent\Relations\Relation;
   use Illuminate\Support\Carbon;
   use Roster\Enums\ScheduleStatus;
   use Roster\Traits\BelongsToSchedulable;
  @@ -87,8 +87,8 @@
       /**
        * Determine if this schedule overlaps with a given time period.
        *
  -     * @param Carbon $start The start of the period to check
  -     * @param Carbon $end The end of the period to check
  +     * @param  Carbon  $start  The start of the period to check
  +     * @param  Carbon  $end  The end of the period to check
        * @return bool True if the schedule overlaps with the period
        */
       public function overlapsWith(Carbon $start, Carbon $end): bool
  
  ⨯ src/Observers/SchedulableObserver.php                                                                                                                                 phpdoc_align  
  @@ -12,7 +12,7 @@
       /**
        * Ensure schedulable entity is set before creating the model.
        *
  -     * @param Model $model The model being created
  +     * @param  Model  $model  The model being created
        *
        * @throws MissingSchedulableException If schedulable_id or schedulable_type are empty
        */
  
  ⨯ src/Repositories/AbstractRepository.php                                                                                                                               phpdoc_align  
  @@ -17,7 +17,7 @@
       /**
        * Create a new record.
        *
  -     * @param array<string, mixed> $data
  +     * @param  array<string, mixed>  $data
        * @return TModel
        */
       abstract public function create(array $data): Model;
  @@ -25,7 +25,7 @@
       /**
        * Update an existing record.
        *
  -     * @param array<string, mixed> $data
  +     * @param  array<string, mixed>  $data
        * @return bool True if update was successful
        */
       abstract public function update(int $id, array $data): bool;
  
  ⨯ src/Repositories/AvailabilityRepository.php function_declaration, no_multiline_whitespace_around_double_arrow, trailing_comma_in_multiline, phpdoc_separation, not_operator_with_s  
  @@ -4,11 +4,11 @@
   
   namespace Roster\Repositories;
   
  -use InvalidArgumentException;
   use Illuminate\Database\Eloquent\Builder;
   use Illuminate\Database\Eloquent\Model;
   use Illuminate\Support\Carbon;
   use Illuminate\Support\Collection;
  +use InvalidArgumentException;
   use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
   use Roster\Models\Availability;
   use Roster\Traits\DateRangeOverlapTrait;
  @@ -60,7 +60,7 @@
       /**
        * Delete multiple availabilities by their IDs.
        *
  -     * @param array<int> $ids Array of availability IDs to delete
  +     * @param  array<int>  $ids  Array of availability IDs to delete
        * @return bool True if any records were deleted
        */
       public function deleteMultiple(array $ids): bool
  @@ -79,8 +79,8 @@
       /**
        * Find availabilities for a specific schedulable entity.
        *
  -     * @param Model $model The schedulable entity
  -     * @param string|null $type Optional availability type filter
  +     * @param  Model  $model  The schedulable entity
  +     * @param  string|null  $type  Optional availability type filter
        * @return Collection<int, Availability> Collection of availabilities for the schedulable
        */
       public function findForSchedulable(Model $model, ?string $type = null): Collection
  @@ -97,10 +97,10 @@
       /**
        * Get availabilities for a specific date range.
        *
  -     * @param Model $model The schedulable entity
  -     * @param Carbon $start Start of date range
  -     * @param Carbon $end End of date range
  -     * @param string|null $type Optional availability type filter
  +     * @param  Model  $model  The schedulable entity
  +     * @param  Carbon  $start  Start of date range
  +     * @param  Carbon  $end  End of date range
  +     * @param  string|null  $type  Optional availability type filter
        * @return Collection<int, Availability> Collection of availabilities within the date range
        */
       public function getForDateRange(
  @@ -129,11 +129,12 @@
       /**
        * Find availability for a time slot with conflict information.
        *
  -     * @param Model $model The schedulable entity
  -     * @param Carbon $start Start of time slot
  -     * @param Carbon $end End of time slot
  -     * @param string|null $type Optional availability type filter
  +     * @param  Model  $model  The schedulable entity
  +     * @param  Carbon  $start  Start of time slot
  +     * @param  Carbon  $end  End of time slot
  +     * @param  string|null  $type  Optional availability type filter
        * @return Availability|null The matching availability with conflict info or null
  +     *
        * @throws InvalidArgumentException If the time range is invalid
        */
       public function findForTimeSlotWithConflictInfo(
  @@ -167,7 +168,7 @@
                   'impediments as has_overlapping_impediments' => function ($query) use ($start, $end): void {
                       $query->where('start_datetime', '<', $end)
                           ->where('end_datetime', '>', $start);
  -                }
  +                },
               ])
               ->first();
       }
  @@ -175,11 +176,12 @@
       /**
        * Find availability for a specific time slot.
        *
  -     * @param Model $model The schedulable entity
  -     * @param Carbon $start Start of time slot
  -     * @param Carbon $end End of time slot
  -     * @param string|null $type Optional availability type filter
  +     * @param  Model  $model  The schedulable entity
  +     * @param  Carbon  $start  Start of time slot
  +     * @param  Carbon  $end  End of time slot
  +     * @param  string|null  $type  Optional availability type filter
        * @return Availability|null The matching availability or null
  +     *
        * @throws InvalidArgumentException If the time range is invalid
        */
       public function findForTimeSlot(
  @@ -206,9 +208,9 @@
       /**
        * Get availabilities for a specific date.
        *
  -     * @param Model $model The schedulable entity
  -     * @param Carbon $date The date to check
  -     * @param string|null $type Optional availability type filter
  +     * @param  Model  $model  The schedulable entity
  +     * @param  Carbon  $date  The date to check
  +     * @param  string|null  $type  Optional availability type filter
        * @return Collection<int, Availability> Collection of availabilities for the date
        */
       public function getForDate(
  @@ -244,9 +246,9 @@
       /**
        * Get all availabilities for a schedulable entity.
        *
  -     * @param Model $model The schedulable entity
  -     * @param string|null $type Optional availability type filter
  -     * @param string|null $day Optional day filter
  +     * @param  Model  $model  The schedulable entity
  +     * @param  string|null  $type  Optional availability type filter
  +     * @param  string|null  $day  Optional day filter
        * @return Collection<int, Availability> Collection of availabilities
        */
       public function getAllForSchedulable(
  @@ -271,8 +273,8 @@
       /**
        * Check if schedulable is available at specific datetime.
        *
  -     * @param Model $model The schedulable entity
  -     * @param Carbon $datetime The datetime to check
  +     * @param  Model  $model  The schedulable entity
  +     * @param  Carbon  $datetime  The datetime to check
        * @return bool True if available at the given datetime
        */
       public function isAvailableAt(Model $model, Carbon $datetime): bool
  @@ -293,10 +295,11 @@
       /**
        * Find overlapping availabilities.
        *
  -     * @param Model $model The schedulable entity
  -     * @param array<string, mixed> $data The availability data to check
  -     * @param int|null $exceptId ID to exclude from search
  +     * @param  Model  $model  The schedulable entity
  +     * @param  array<string, mixed>  $data  The availability data to check
  +     * @param  int|null  $exceptId  ID to exclude from search
        * @return Collection<int, Availability> Collection of overlapping availabilities
  +     *
        * @throws InvalidArgumentException If time range is invalid
        */
       public function findOverlapping(
  @@ -330,10 +333,10 @@
       /**
        * Check if time ranges overlap.
        *
  -     * @param Carbon $existingStart Existing start time
  -     * @param Carbon $existingEnd Existing end time
  -     * @param Carbon $newStart New start time
  -     * @param Carbon $newEnd New end time
  +     * @param  Carbon  $existingStart  Existing start time
  +     * @param  Carbon  $existingEnd  Existing end time
  +     * @param  Carbon  $newStart  New start time
  +     * @param  Carbon  $newEnd  New end time
        * @return bool True if time ranges overlap
        */
       public function doTimeRangesOverlap(
  @@ -348,8 +351,8 @@
       /**
        * Find related availabilities based on search criteria.
        *
  -     * @param Model $model The schedulable entity
  -     * @param array<string, mixed> $data Search criteria
  +     * @param  Model  $model  The schedulable entity
  +     * @param  array<string, mixed>  $data  Search criteria
        * @return Collection<int, Availability> Collection of related availabilities
        */
       public function findByType(Model $model, array $data): Collection
  @@ -371,8 +374,8 @@
       /**
        * Build filtered query for availabilities.
        *
  -     * @param Model $model The schedulable entity
  -     * @param array<string, mixed> $filters Filters to apply
  +     * @param  Model  $model  The schedulable entity
  +     * @param  array<string, mixed>  $filters  Filters to apply
        * @return Builder Eloquent query builder
        */
       public function buildQueryWithFilters(Model $model, array $filters = []): Builder
  @@ -380,15 +383,12 @@
           $builder = $this->buildBaseQuery($model);
   
           match (true) {
  -            isset($filters['type']) && isset($filters['day']) =>
  -            $builder->where('type', $filters['type'])
  +            isset($filters['type']) && isset($filters['day']) => $builder->where('type', $filters['type'])
                   ->whereJsonContains('days', strtolower($filters['day'])),
   
  -            isset($filters['type']) =>
  -            $builder->where('type', $filters['type']),
  +            isset($filters['type']) => $builder->where('type', $filters['type']),
   
  -            isset($filters['day']) =>
  -            $builder->whereJsonContains('days', strtolower($filters['day'])),
  +            isset($filters['day']) => $builder->whereJsonContains('days', strtolower($filters['day'])),
   
               default => null,
           };
  @@ -399,8 +399,8 @@
       /**
        * Check if an availability applies to a specific date.
        *
  -     * @param Availability $availability The availability to check
  -     * @param Carbon $date The date to check
  +     * @param  Availability  $availability  The availability to check
  +     * @param  Carbon  $date  The date to check
        * @return bool True if the availability applies to the date
        */
       public function isAvailableOnDate(Availability $availability, Carbon $date): bool
  @@ -407,7 +407,7 @@
       {
           $dayOfWeek = strtolower($date->englishDayOfWeek);
   
  -        if (!in_array($dayOfWeek, $availability->days)) {
  +        if (! in_array($dayOfWeek, $availability->days)) {
               return false;
           }
   
  @@ -414,16 +414,16 @@
           $isBeforeValidityStart = $availability->validity_start !== null && $date->lt($availability->validity_start);
           $isAfterValidityEnd = $availability->validity_end !== null && $date->gt($availability->validity_end);
   
  -        return !$isBeforeValidityStart && !$isAfterValidityEnd;
  +        return ! $isBeforeValidityStart && ! $isAfterValidityEnd;
       }
   
       /**
        * Load availabilities with pre-loaded schedule and impediment conflicts.
        *
  -     * @param Model $model The schedulable entity
  -     * @param Carbon $start Start of the date range
  -     * @param Carbon $end End of the date range
  -     * @param string|null $type Optional availability type filter
  +     * @param  Model  $model  The schedulable entity
  +     * @param  Carbon  $start  Start of the date range
  +     * @param  Carbon  $end  End of the date range
  +     * @param  string|null  $type  Optional availability type filter
        * @return Collection<int, Availability> Collection of availabilities with conflict info
        */
       public function getAvailabilitiesWithConflictInfo(
  @@ -440,14 +440,14 @@
       /**
        * Filter availabilities for a specific date.
        *
  -     * @param Collection<int, Availability> $availabilities Collection of availabilities
  -     * @param Carbon $date Date to filter for
  +     * @param  Collection<int, Availability>  $availabilities  Collection of availabilities
  +     * @param  Carbon  $date  Date to filter for
        * @return Collection<int, Availability> Filtered availabilities
        */
       public function filterAvailabilitiesForDate(Collection $availabilities, Carbon $date): Collection
       {
           return $availabilities->filter(
  -            fn(Availability $availability): bool => $this->isAvailableOnDate($availability, $date)
  +            fn (Availability $availability): bool => $this->isAvailableOnDate($availability, $date)
           );
       }
   
  @@ -454,7 +454,7 @@
       /**
        * Build base query for availabilities of a schedulable entity.
        *
  -     * @param Model $model The schedulable entity
  +     * @param  Model  $model  The schedulable entity
        * @return Builder Base query builder
        */
       private function buildBaseQuery(Model $model): Builder
  @@ -466,9 +466,9 @@
       /**
        * Apply time slot filters to query.
        *
  -     * @param Builder $builder Query builder
  -     * @param Carbon $start Start time
  -     * @param Carbon $end End time
  +     * @param  Builder  $builder  Query builder
  +     * @param  Carbon  $start  Start time
  +     * @param  Carbon  $end  End time
        */
       private function applyTimeSlotFilters(Builder $builder, Carbon $start, Carbon $end): void
       {
  @@ -482,8 +482,8 @@
       /**
        * Apply date filters to query.
        *
  -     * @param Builder $builder Query builder
  -     * @param Carbon $date Date to filter for
  +     * @param  Builder  $builder  Query builder
  +     * @param  Carbon  $date  Date to filter for
        */
       private function applyDateFilters(Builder $builder, Carbon $date): void
       {
  @@ -493,9 +493,9 @@
       /**
        * Apply date range filters to query.
        *
  -     * @param Builder $builder Query builder
  -     * @param Carbon $startDate Start date
  -     * @param Carbon $endDate End date
  +     * @param  Builder  $builder  Query builder
  +     * @param  Carbon  $startDate  Start date
  +     * @param  Carbon  $endDate  End date
        */
       private function applyDateRangeFilters(Builder $builder, Carbon $startDate, Carbon $endDate): void
       {
  @@ -511,8 +511,8 @@
       /**
        * Apply day filters to query.
        *
  -     * @param Builder $builder Query builder
  -     * @param array<string> $days Days to filter for
  +     * @param  Builder  $builder  Query builder
  +     * @param  array<string>  $days  Days to filter for
        */
       private function applyDayFilters(Builder $builder, array $days): void
       {
  @@ -530,9 +530,9 @@
       /**
        * Apply time overlap filters to query.
        *
  -     * @param Builder $builder Query builder
  -     * @param Carbon $startTime Start time
  -     * @param Carbon $endTime End time
  +     * @param  Builder  $builder  Query builder
  +     * @param  Carbon  $startTime  Start time
  +     * @param  Carbon  $endTime  End time
        */
       private function applyTimeOverlapFilters(Builder $builder, Carbon $startTime, Carbon $endTime): void
       {
  @@ -545,32 +545,28 @@
       /**
        * Apply date overlap filters to query using strategy pattern.
        *
  -     * @param Builder $builder Query builder
  -     * @param Carbon|null $startDate Optional start date
  -     * @param Carbon|null $endDate Optional end date
  +     * @param  Builder  $builder  Query builder
  +     * @param  Carbon|null  $startDate  Optional start date
  +     * @param  Carbon|null  $endDate  Optional end date
        */
       private function applyDateOverlapFilters(Builder $builder, ?Carbon $startDate, ?Carbon $endDate): void
       {
           $builder->where(function ($query) use ($startDate, $endDate): void {
               match (true) {
  -                $startDate instanceof Carbon && $endDate instanceof Carbon =>
  -                $query->where('validity_start', '<=', $endDate)
  +                $startDate instanceof Carbon && $endDate instanceof Carbon => $query->where('validity_start', '<=', $endDate)
                       ->where('validity_end', '>=', $startDate),
   
  -                $startDate instanceof Carbon =>
  -                $query->where(function ($subQuery) use ($startDate): void {
  +                $startDate instanceof Carbon => $query->where(function ($subQuery) use ($startDate): void {
                       $subQuery->where('validity_end', '>=', $startDate)
                           ->orWhereNull('validity_end');
                   }),
   
  -                $endDate instanceof Carbon =>
  -                $query->where(function ($subQuery) use ($endDate): void {
  +                $endDate instanceof Carbon => $query->where(function ($subQuery) use ($endDate): void {
                       $subQuery->where('validity_start', '<=', $endDate)
                           ->orWhereNull('validity_start');
                   }),
   
  -                default =>
  -                $query->where(function ($subQuery): void {
  +                default => $query->where(function ($subQuery): void {
                       $subQuery->whereNull('validity_start')
                           ->orWhereNull('validity_end');
                   }),
  @@ -581,9 +577,9 @@
       /**
        * Eager load relations with date filtering.
        *
  -     * @param Builder $builder Query builder
  -     * @param Carbon|null $startDate Optional start date for filtering
  -     * @param Carbon|null $endDate Optional end date for filtering
  +     * @param  Builder  $builder  Query builder
  +     * @param  Carbon|null  $startDate  Optional start date for filtering
  +     * @param  Carbon|null  $endDate  Optional end date for filtering
        */
       private function eagerLoadRelations(Builder $builder, ?Carbon $startDate, ?Carbon $endDate): void
       {
  @@ -611,15 +607,14 @@
       /**
        * Apply date filter to relation query using strategy pattern.
        *
  -     * @param Builder $builder Relation query builder
  -     * @param Carbon|null $startDate Optional start date
  -     * @param Carbon|null $endDate Optional end date
  +     * @param  Builder  $builder  Relation query builder
  +     * @param  Carbon|null  $startDate  Optional start date
  +     * @param  Carbon|null  $endDate  Optional end date
        */
       private function applyRelationDateFilter(Builder $builder, ?Carbon $startDate, ?Carbon $endDate): void
       {
           match (true) {
  -            $startDate instanceof Carbon && $endDate instanceof Carbon =>
  -            $builder->where(function ($q) use ($startDate, $endDate): void {
  +            $startDate instanceof Carbon && $endDate instanceof Carbon => $builder->where(function ($q) use ($startDate, $endDate): void {
                   $q->whereBetween('start_datetime', [$startDate, $endDate])
                       ->orWhereBetween('end_datetime', [$startDate, $endDate])
                       ->orWhere(function ($subQuery) use ($startDate, $endDate): void {
  
  ⨯ src/Repositories/ImpedimentRepository.php                                                                                    no_unused_imports, no_extra_blank_lines, phpdoc_align  
  @@ -7,7 +7,6 @@
   use Illuminate\Database\Eloquent\Builder;
   use Illuminate\Support\Carbon;
   use Illuminate\Support\Collection;
  -use Illuminate\Support\Facades\Log;
   use Roster\Contracts\Repository\ImpedimentRepositoryInterface;
   use Roster\Models\Impediment;
   
  @@ -67,9 +66,9 @@
       /**
        * Find impediments for a time slot.
        *
  -     * @param int $availabilityId The availability ID
  -     * @param Carbon $start Start of time slot
  -     * @param Carbon $end End of time slot
  +     * @param  int  $availabilityId  The availability ID
  +     * @param  Carbon  $start  Start of time slot
  +     * @param  Carbon  $end  End of time slot
        * @return Collection<int, Impediment>
        */
       public function findForTimeSlot(
  @@ -87,10 +86,10 @@
       /**
        * Check if a time slot has overlapping impediments.
        *
  -     * @param int $availabilityId The availability ID
  -     * @param Carbon $start Start of time slot
  -     * @param Carbon $end End of time slot
  -     * @param int|null $excludeId Impediment ID to exclude
  +     * @param  int  $availabilityId  The availability ID
  +     * @param  Carbon  $start  Start of time slot
  +     * @param  Carbon  $end  End of time slot
  +     * @param  int|null  $excludeId  Impediment ID to exclude
        * @return bool True if overlapping impediments exist
        */
       public function hasOverlappingImpediments(
  @@ -107,8 +106,6 @@
               $query->where('id', '!=', $excludeId);
           }
   
  -
  -
           return $query->exists();
       }
   
  @@ -115,10 +112,10 @@
       /**
        * Find overlapping impediments with time range.
        *
  -     * @param int $availabilityId The availability ID
  -     * @param Carbon $start Start of time range
  -     * @param Carbon $end End of time range
  -     * @param int|null $excludeId Impediment ID to exclude
  +     * @param  int  $availabilityId  The availability ID
  +     * @param  Carbon  $start  Start of time range
  +     * @param  Carbon  $end  End of time range
  +     * @param  int|null  $excludeId  Impediment ID to exclude
        * @return Collection<int, Impediment>
        */
       public function findOverlappingImpediments(
  
  ⨯ src/Repositories/ScheduleRepository.php                                                                                                         no_extra_blank_lines, phpdoc_align  
  @@ -75,9 +75,9 @@
       /**
        * Find schedules for a time slot.
        *
  -     * @param int $availabilityId The availability ID
  -     * @param Carbon $start Start of time slot
  -     * @param Carbon $end End of time slot
  +     * @param  int  $availabilityId  The availability ID
  +     * @param  Carbon  $start  Start of time slot
  +     * @param  Carbon  $end  End of time slot
        * @return Collection<int, Schedule>
        */
       public function findForTimeSlot(
  @@ -95,10 +95,10 @@
       /**
        * Check if a time slot has overlapping schedules.
        *
  -     * @param int $availabilityId The availability ID
  -     * @param Carbon $start Start of time slot
  -     * @param Carbon $end End of time slot
  -     * @param int|null $excludeId Schedule ID to exclude
  +     * @param  int  $availabilityId  The availability ID
  +     * @param  Carbon  $start  Start of time slot
  +     * @param  Carbon  $end  End of time slot
  +     * @param  int|null  $excludeId  Schedule ID to exclude
        * @return bool True if overlapping schedules exist
        */
       public function hasOverlappingSchedule(
  @@ -119,12 +119,8 @@
           $sql = $query->toSql();
           $bindings = $query->getBindings();
   
  -
  -
           $result = $query->exists();
   
  -
  -
           return $result;
       }
   
  @@ -131,10 +127,10 @@
       /**
        * Find overlapping schedules with time range.
        *
  -     * @param int $availabilityId The availability ID
  -     * @param Carbon $start Start of time range
  -     * @param Carbon $end End of time range
  -     * @param int|null $excludeId Schedule ID to exclude
  +     * @param  int  $availabilityId  The availability ID
  +     * @param  Carbon  $start  Start of time range
  +     * @param  Carbon  $end  End of time range
  +     * @param  int|null  $excludeId  Schedule ID to exclude
        * @return Collection<int, Schedule>
        */
       public function findOverlappingSchedules(
  @@ -159,9 +155,9 @@
       /**
        * Get all schedules for a schedulable.
        *
  -     * @param int $schedulableId The schedulable ID
  -     * @param string $schedulableType The schedulable class type
  -     * @param array<string, mixed> $filters Additional filters
  +     * @param  int  $schedulableId  The schedulable ID
  +     * @param  string  $schedulableType  The schedulable class type
  +     * @param  array<string, mixed>  $filters  Additional filters
        * @return Collection<int, Schedule>
        */
       public function getAllForSchedulable(
  @@ -180,11 +176,11 @@
       /**
        * Get schedules between dates.
        *
  -     * @param int $schedulableId The schedulable ID
  -     * @param string $schedulableType The schedulable class type
  -     * @param Carbon $start Start of date range
  -     * @param Carbon $end End of date range
  -     * @param array<string, mixed> $filters Additional filters
  +     * @param  int  $schedulableId  The schedulable ID
  +     * @param  string  $schedulableType  The schedulable class type
  +     * @param  Carbon  $start  Start of date range
  +     * @param  Carbon  $end  End of date range
  +     * @param  array<string, mixed>  $filters  Additional filters
        * @return Collection<int, Schedule>
        */
       public function getForDateRange(
  @@ -206,9 +202,9 @@
       /**
        * Apply filters to query.
        *
  -     * @param int $schedulableId The schedulable ID
  -     * @param string $schedulableType The schedulable class type
  -     * @param array<string, mixed> $filters Filters to apply
  +     * @param  int  $schedulableId  The schedulable ID
  +     * @param  string  $schedulableType  The schedulable class type
  +     * @param  array<string, mixed>  $filters  Filters to apply
        */
       public function buildQueryWithFilters(
           int $schedulableId,
  @@ -235,8 +231,8 @@
       /**
        * Apply common filters to query.
        *
  -     * @param Builder $builder The query builder
  -     * @param array<string, mixed> $filters Filters to apply
  +     * @param  Builder  $builder  The query builder
  +     * @param  array<string, mixed>  $filters  Filters to apply
        */
       private function applyCommonFilters(Builder $builder, array $filters): void
       {
  
  ⨯ src/RosterServiceProvider.php                                                                                                                   new_with_parentheses, concat_space  
  @@ -49,8 +49,8 @@
   
       public function register(): void
       {
  -        $this->mergeConfigFrom(__DIR__ . '/../config/roster.php', 'roster');
  -        $this->mergeConfigFrom(__DIR__ . '/../config/roster-validation.php', 'roster-validation');
  +        $this->mergeConfigFrom(__DIR__.'/../config/roster.php', 'roster');
  +        $this->mergeConfigFrom(__DIR__.'/../config/roster-validation.php', 'roster-validation');
   
           $this->loadHelpers();
           $this->registerCoreServices();
  @@ -67,7 +67,7 @@
   
       protected function loadHelpers(): void
       {
  -        $helpersFile = __DIR__ . '/helpers.php';
  +        $helpersFile = __DIR__.'/helpers.php';
           if (file_exists($helpersFile)) {
               require_once $helpersFile;
           }
  @@ -92,7 +92,7 @@
   
           $this->app->singleton(ValidatorInterface::class, function ($app) use ($useFileCache): Validator {
               $directories = array_merge(
  -                [__DIR__ . '/Validation/Rules'],
  +                [__DIR__.'/Validation/Rules'],
                   config('roster-validation.rule_directories', [])
               );
   
  @@ -103,7 +103,7 @@
   
           $this->app->singleton(RuleScanner::class, function ($app) use ($useFileCache): RuleScanner {
               return new RuleScanner(
  -                array_merge([__DIR__ . '/Validation/Rules'], config('roster-validation.rule_directories', [])),
  +                array_merge([__DIR__.'/Validation/Rules'], config('roster-validation.rule_directories', [])),
                   $useFileCache
               );
           });
  @@ -147,7 +147,7 @@
           $this->app->singleton(ResourcePublisherService::class, function ($app): ResourcePublisherService {
               return new ResourcePublisherService(
                   application: $app,
  -                filesystem: new Filesystem()
  +                filesystem: new Filesystem
               );
           });
       }
  @@ -156,17 +156,17 @@
       {
           // Configuration de validation
           $this->publishes([
  -            __DIR__ . '/../config/roster-validation.php' => config_path('roster-validation.php'),
  +            __DIR__.'/../config/roster-validation.php' => config_path('roster-validation.php'),
           ], 'roster-validation-config');
   
           // Configuration principale
           $this->publishes([
  -            __DIR__ . '/../config/roster.php' => config_path('roster.php'),
  +            __DIR__.'/../config/roster.php' => config_path('roster.php'),
           ], 'roster-config');
   
           // Migrations
           $this->publishes([
  -            __DIR__ . '/../database/migrations/' => database_path('migrations'),
  +            __DIR__.'/../database/migrations/' => database_path('migrations'),
           ], 'roster-migrations');
       }
   }
  
  ⨯ src/Services/AvailabilityService.php        function_declaration, trailing_comma_in_multiline, phpdoc_separation, not_operator_with_successor_space, ordered_imports, phpdoc_align  
  @@ -4,8 +4,8 @@
   
   namespace Roster\Services;
   
  +use Illuminate\Database\Eloquent\Builder;
   use Illuminate\Database\Eloquent\Model;
  -use Illuminate\Database\Eloquent\Builder;
   use Illuminate\Support\Carbon;
   use Illuminate\Support\Collection;
   use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
  @@ -42,7 +42,7 @@
       /**
        * Create a new availability.
        *
  -     * @param array $data The data for creation
  +     * @param  array  $data  The data for creation
        * @return Availability The created entity
        */
       public function create(array $data): Availability
  @@ -85,7 +85,8 @@
   
       /**
        * Update an existing availability.
  -     * @param array<string, mixed> $data
  +     *
  +     * @param  array<string, mixed>  $data
        */
       public function update(int $id, array $data): bool
       {
  @@ -92,7 +93,7 @@
           parent::update($id, $data);
           $entity = $this->find($id);
   
  -        if (!$entity instanceof Availability) {
  +        if (! $entity instanceof Availability) {
               throw ValidationFailedException::fromViolations(
                   [
                       'id' => sprintf(
  @@ -148,7 +149,7 @@
       public function delete(int $id): bool
       {
           $entity = $this->find($id);
  -        if (!$entity instanceof Availability) {
  +        if (! $entity instanceof Availability) {
               throw ValidationFailedException::fromViolations(
                   [
                       'id' => sprintf(
  @@ -217,12 +218,12 @@
        * This method identifies availabilities that are adjacent to the new data,
        * merges them when possible, and removes the merged entities to avoid duplicates.
        *
  -     * @param array<string, mixed> $data The new availability data to merge
  +     * @param  array<string, mixed>  $data  The new availability data to merge
        * @return array<string, mixed> The merged availability data
        */
       private function mergeWithAdjacentAvailabilities(array $data): array
       {
  -        if (!$this->schedulable instanceof Model) {
  +        if (! $this->schedulable instanceof Model) {
               return $data;
           }
   
  @@ -251,8 +252,8 @@
        * Two availabilities are adjacent if they share common properties
        * and their time ranges touch exactly.
        *
  -     * @param Availability $availability Existing availability
  -     * @param array<string, mixed> $newData New availability data
  +     * @param  Availability  $availability  Existing availability
  +     * @param  array<string, mixed>  $newData  New availability data
        * @return bool True if availabilities are adjacent
        */
       private function areAvailabilitiesAdjacent(Availability $availability, array $newData): bool
  @@ -288,10 +289,10 @@
       /**
        * Check if two time ranges touch exactly.
        *
  -     * @param Carbon $firstStart First time range start
  -     * @param Carbon $firstEnd First time range end
  -     * @param Carbon $secondStart Second time range start
  -     * @param Carbon $secondEnd Second time range end
  +     * @param  Carbon  $firstStart  First time range start
  +     * @param  Carbon  $firstEnd  First time range end
  +     * @param  Carbon  $secondStart  Second time range start
  +     * @param  Carbon  $secondEnd  Second time range end
        * @return bool True if time ranges touch
        */
       private function timeRangesTouch(
  @@ -310,8 +311,8 @@
       /**
        * Merge two adjacent availability data arrays.
        *
  -     * @param Availability $availability Existing availability
  -     * @param array<string, mixed> $newData New availability data
  +     * @param  Availability  $availability  Existing availability
  +     * @param  array<string, mixed>  $newData  New availability data
        * @return array<string, mixed> Merged availability data
        */
       private function mergeAdjacentAvailabilityData(Availability $availability, array $newData): array
  @@ -319,11 +320,11 @@
           // Fusionner les heures de début et fin
           $startTimes = [
               Carbon::parse($availability->daily_start),
  -            Carbon::parse($newData['daily_start'])
  +            Carbon::parse($newData['daily_start']),
           ];
           $endTimes = [
               Carbon::parse($availability->daily_end),
  -            Carbon::parse($newData['daily_end'])
  +            Carbon::parse($newData['daily_end']),
           ];
   
           $mergedStartTime = min($startTimes[0]->timestamp, $startTimes[1]->timestamp);
  @@ -343,11 +344,11 @@
   
           $mergedValidityStart = $startDates === []
               ? null
  -            : Carbon::createFromTimestamp(min(array_map(fn($date) => $date->timestamp, $startDates)));
  +            : Carbon::createFromTimestamp(min(array_map(fn ($date) => $date->timestamp, $startDates)));
   
           $mergedValidityEnd = $endDates === []
               ? null
  -            : Carbon::createFromTimestamp(max(array_map(fn($date) => $date->timestamp, $endDates)));
  +            : Carbon::createFromTimestamp(max(array_map(fn ($date) => $date->timestamp, $endDates)));
   
           return [
               'type' => $availability->type,
  
  ⨯ src/Services/Core/AbstractAvailabilityValidatingService.php                                                                        not_operator_with_successor_space, phpdoc_align  
  @@ -53,7 +53,7 @@
   
           $validationResult = $this->validator->validate($validationContext);
   
  -        if (!$validationResult->isValid()) {
  +        if (! $validationResult->isValid()) {
               throw ValidationFailedException::fromViolations(
                   $validationResult->getViolations(),
                   $operationType,
  @@ -75,8 +75,8 @@
       /**
        * Get entities between two dates.
        *
  -     * @param Carbon $start Start date
  -     * @param Carbon $end End date
  +     * @param  Carbon  $start  Start date
  +     * @param  Carbon  $end  End date
        * @return Collection Entities within the date range
        */
       public function between(Carbon $start, Carbon $end): Collection
  @@ -111,7 +111,7 @@
   
           $validationResult = $this->validator->validate($validationContext);
   
  -        if (!$validationResult->isValid()) {
  +        if (! $validationResult->isValid()) {
               throw ValidationFailedException::fromViolations(
                   $validationResult->getViolations(),
                   OperationType::CREATE,
  
  ⨯ src/Services/Core/AbstractEntityScopingService.php class_attributes_separation, unary_operator_spaces, not_operator_with_successor_space, blank_line_before_statement, phpdoc_alig  
  @@ -26,12 +26,13 @@
       /**
        * Set multiple filters at once.
        *
  -     * @param array<string, mixed> $filters Associative array of filter key-value pairs
  +     * @param  array<string, mixed>  $filters  Associative array of filter key-value pairs
        * @return $this
        */
       final public function setFilters(array $filters): static
       {
           $this->filters = array_merge($this->filters, $filters);
  +
           return $this;
       }
   
  @@ -38,12 +39,13 @@
       /**
        * Filter by entity type.
        *
  -     * @param string $type Entity type to filter by
  +     * @param  string  $type  Entity type to filter by
        * @return $this
        */
       final public function whereType(string $type): static
       {
           $this->filters['type'] = $type;
  +
           return $this;
       }
   
  @@ -55,6 +57,7 @@
       final public function resetFilters(): static
       {
           $this->filters = [];
  +
           return $this;
       }
   
  @@ -68,12 +71,10 @@
           return $this->get();
       }
   
  -
  -
       /**
        * Apply configuration rules specific to create operation.
        *
  -     * @param array<string, mixed> $data The input data
  +     * @param  array<string, mixed>  $data  The input data
        * @return array<string, mixed> The processed data
        */
       final protected function applyCreateConfigurationRules(array $data): array
  @@ -84,7 +85,7 @@
       /**
        * Apply configuration rules specific to update operation.
        *
  -     * @param array<string, mixed> $data The input data
  +     * @param  array<string, mixed>  $data  The input data
        * @return array<string, mixed> The processed data
        */
       final protected function applyUpdateConfigurationRules(array $data): array
  @@ -95,7 +96,7 @@
       /**
        * Apply entity-specific default values.
        *
  -     * @param array<string, mixed> $data The input data
  +     * @param  array<string, mixed>  $data  The input data
        * @return array<string, mixed> The processed data
        */
       final protected function applyEntitySpecificDefaults(array $data): array
  @@ -120,6 +121,7 @@
       final public function getEntityType(): string
       {
           $className = class_basename(static::class);
  +
           return strtolower(str_replace('Service', '', $className));
       }
   
  @@ -132,8 +134,6 @@
       {
           return ucfirst($this->getEntityType());
       }
  -
  -
   
       // Abstract methods that must be implemented by child classes
       abstract protected function buildQueryWithFilters(): Builder;
  
  ⨯ src/Services/Core/AbstractService.php                                                                       class_attributes_separation, blank_line_before_statement, phpdoc_align  
  @@ -46,7 +46,7 @@
       /**
        * Find entity by ID.
        *
  -     * @param int $id Entity ID
  +     * @param  int  $id  Entity ID
        * @return mixed Entity or null if not found
        */
       abstract public function find(int $id): mixed;
  @@ -64,12 +64,13 @@
       /**
        * Set the data payload.
        *
  -     * @param array<string, mixed> $data The data to set
  +     * @param  array<string, mixed>  $data  The data to set
        * @return $this
        */
       public function setData(array $data): self
       {
           $this->data = $data;
  +
           return $this;
       }
   
  @@ -86,12 +87,13 @@
       /**
        * Set the filters.
        *
  -     * @param array<string, mixed> $filters The filters to set
  +     * @param  array<string, mixed>  $filters  The filters to set
        * @return $this
        */
       public function setFilters(array $filters): self
       {
           $this->filters = $filters;
  +
           return $this;
       }
   
  @@ -108,12 +110,13 @@
       /**
        * Set the schedulable model instance.
        *
  -     * @param Model $model The schedulable model
  +     * @param  Model  $model  The schedulable model
        * @return $this
        */
       public function setSchedulable(Model $model): self
       {
           $this->schedulable = $model;
  +
           return $this;
       }
   
  @@ -122,19 +125,20 @@
   
           // Supprime les clés spécifiées si elles existent
           $data = array_diff_key($data, array_flip(['schedulable_id', 'schedulable_type', 'availability_id']));
  +
           return true;
       }
   
  -
       /**
        * Scope the service to a specific schedulable model.
        *
  -     * @param Model $model The parent model to scope operations to
  +     * @param  Model  $model  The parent model to scope operations to
        * @return $this
        */
       public function for(Model $model): static
       {
           $this->schedulable = $model;
  +
           return $this;
       }
   
  @@ -141,13 +145,14 @@
       /**
        * Set a single filter.
        *
  -     * @param string $key Filter key
  -     * @param mixed $value Filter value
  +     * @param  string  $key  Filter key
  +     * @param  mixed  $value  Filter value
        * @return $this
        */
       public function setFilter(string $key, mixed $value): self
       {
           $this->filters[$key] = $value;
  +
           return $this;
       }
   
  @@ -160,6 +165,7 @@
       {
           $this->data = [];
           $this->filters = [];
  +
           return $this;
       }
   }
  
  ⨯ src/Services/Core/AbstractValidatingService.php                                                              not_operator_with_successor_space, no_extra_blank_lines, phpdoc_align  
  @@ -26,8 +26,8 @@
       /**
        * Validate data against rules.
        *
  -     * @param array<string, mixed> $data
  -     * @param array<string, mixed> $flags Additional flags to pass to validation context
  +     * @param  array<string, mixed>  $data
  +     * @param  array<string, mixed>  $flags  Additional flags to pass to validation context
        */
       protected function validate(
           array $data,
  @@ -47,9 +47,8 @@
   
           $validationResult = $this->validator->validate($validationContext);
   
  -
           // End date must be after start date
  -        if (!$validationResult->isValid()) {
  +        if (! $validationResult->isValid()) {
               throw ValidationFailedException::fromViolations(
                   $validationResult->getViolations(),
                   $operationType,
  
  ⨯ src/Services/Core/AvailabilityChecker.php                                                braces_position, phpdoc_separation, single_line_empty_body, ordered_imports, phpdoc_align  
  @@ -4,9 +4,9 @@
   
   namespace Roster\Services\Core;
   
  -use InvalidArgumentException;
   use Illuminate\Database\Eloquent\Model;
   use Illuminate\Support\Carbon;
  +use InvalidArgumentException;
   use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
   use Roster\Models\Availability;
   
  @@ -19,8 +19,8 @@
       /**
        * Check if the schedulable resource is available at a specific datetime.
        *
  -     * @param Model $model The schedulable resource (user, equipment, room, etc.)
  -     * @param Carbon $datetime The datetime to check availability for
  +     * @param  Model  $model  The schedulable resource (user, equipment, room, etc.)
  +     * @param  Carbon  $datetime  The datetime to check availability for
        * @return bool True if the resource is available at the given datetime
        */
       public function isAvailableAt(Model $model, Carbon $datetime): bool
  @@ -31,11 +31,12 @@
       /**
        * Check if the schedulable resource is available for a continuous time period.
        *
  -     * @param Model $model The schedulable resource
  -     * @param Carbon $start Start of the time period
  -     * @param Carbon $end End of the time period
  -     * @param string|null $type Optional availability type filter
  +     * @param  Model  $model  The schedulable resource
  +     * @param  Carbon  $start  Start of the time period
  +     * @param  Carbon  $end  End of the time period
  +     * @param  string|null  $type  Optional availability type filter
        * @return bool True if the resource is available for the entire period
  +     *
        * @throws InvalidArgumentException If the time range is invalid
        */
       public function isAvailableForPeriod(
  
  ⨯ src/Services/Core/ResourcePublisherService.php             increment_style, concat_space, braces_position, not_operator_with_successor_space, single_line_empty_body, phpdoc_align  
  @@ -16,8 +16,8 @@
       /**
        * Create a new ResourcePublisherService instance.
        *
  -     * @param Application $application Laravel application instance
  -     * @param Filesystem $filesystem Filesystem instance for file operations
  +     * @param  Application  $application  Laravel application instance
  +     * @param  Filesystem  $filesystem  Filesystem instance for file operations
        */
       public function __construct(
           private readonly Application $application,
  @@ -58,9 +58,9 @@
       /**
        * Publish a specific resource type.
        *
  -     * @param string $resourceType Type of resource to publish (config, migrations, views, routes)
  -     * @param bool $force Whether to force overwrite existing files
  -     * @param OutputInterface|null $output Console output interface for logging
  +     * @param  string  $resourceType  Type of resource to publish (config, migrations, views, routes)
  +     * @param  bool  $force  Whether to force overwrite existing files
  +     * @param  OutputInterface|null  $output  Console output interface for logging
        * @return bool True if any files were published, false otherwise
        */
       public function publishResource(
  @@ -70,7 +70,7 @@
       ): bool {
           $resources = $this->getPublishableResources();
   
  -        if (!array_key_exists($resourceType, $resources)) {
  +        if (! array_key_exists($resourceType, $resources)) {
               return false;
           }
   
  @@ -96,7 +96,7 @@
       /**
        * Check if a resource type has already been published.
        *
  -     * @param string $resourceType Type of resource to check
  +     * @param  string  $resourceType  Type of resource to check
        * @return bool True if the resource exists at the destination
        */
       public function isPublished(string $resourceType): bool
  @@ -103,7 +103,7 @@
       {
           $resources = $this->getPublishableResources();
   
  -        if (!array_key_exists($resourceType, $resources)) {
  +        if (! array_key_exists($resourceType, $resources)) {
               return false;
           }
   
  @@ -120,7 +120,7 @@
       /**
        * Determine if a source path should be treated as a directory.
        *
  -     * @param string $source Source path to check
  +     * @param  string  $source  Source path to check
        * @return bool True if the source should be published as a directory
        */
       private function shouldTreatAsDirectory(string $source): bool
  @@ -131,10 +131,10 @@
       /**
        * Publish all files from a source directory to a destination directory.
        *
  -     * @param string $source Source directory path
  -     * @param string $destination Destination directory path
  -     * @param bool $force Whether to force overwrite existing files
  -     * @param OutputInterface|null $output Console output interface for logging
  +     * @param  string  $source  Source directory path
  +     * @param  string  $destination  Destination directory path
  +     * @param  bool  $force  Whether to force overwrite existing files
  +     * @param  OutputInterface|null  $output  Console output interface for logging
        * @return bool True if any files were published, false otherwise
        */
       private function publishDirectory(
  @@ -143,7 +143,7 @@
           bool $force,
           ?OutputInterface $output
       ): bool {
  -        if (!$this->filesystem->exists($source)) {
  +        if (! $this->filesystem->exists($source)) {
               return false;
           }
   
  @@ -152,15 +152,15 @@
   
           foreach ($files as $file) {
               $relativePath = $file->getRelativePathname();
  -            $targetPath = $destination . '/' . $relativePath;
  +            $targetPath = $destination.'/'.$relativePath;
   
               if ($this->shouldCopyFile(targetPath: $targetPath, force: $force)) {
                   $this->filesystem->ensureDirectoryExists(dirname($targetPath));
                   $this->filesystem->copy($file->getPathname(), $targetPath);
  -                ++$publishedCount;
  +                $publishedCount++;
   
                   if ($output instanceof OutputInterface) {
  -                    $output->writeln('Published: ' . $relativePath);
  +                    $output->writeln('Published: '.$relativePath);
                   }
               }
           }
  @@ -171,10 +171,10 @@
       /**
        * Publish a single file from source to destination.
        *
  -     * @param string $source Source file path
  -     * @param string $destination Destination file path
  -     * @param bool $force Whether to force overwrite existing file
  -     * @param OutputInterface|null $output Console output interface for logging
  +     * @param  string  $source  Source file path
  +     * @param  string  $destination  Destination file path
  +     * @param  bool  $force  Whether to force overwrite existing file
  +     * @param  OutputInterface|null  $output  Console output interface for logging
        * @return bool True if the file was published, false otherwise
        */
       private function publishSingleFile(
  @@ -183,7 +183,7 @@
           bool $force,
           ?OutputInterface $output
       ): bool {
  -        if (!$this->filesystem->exists($source)) {
  +        if (! $this->filesystem->exists($source)) {
               return false;
           }
   
  @@ -192,7 +192,7 @@
               $this->filesystem->copy($source, $destination);
   
               if ($output instanceof OutputInterface) {
  -                $output->writeln('Published: ' . basename($destination));
  +                $output->writeln('Published: '.basename($destination));
               }
   
               return true;
  @@ -204,8 +204,8 @@
       /**
        * Determine if a file should be copied based on existence and force flag.
        *
  -     * @param string $targetPath Destination path to check
  -     * @param bool $force Whether to force overwrite
  +     * @param  string  $targetPath  Destination path to check
  +     * @param  bool  $force  Whether to force overwrite
        * @return bool True if the file should be copied
        */
       private function shouldCopyFile(string $targetPath, bool $force): bool
  @@ -214,6 +214,6 @@
               return true;
           }
   
  -        return !$this->filesystem->exists($targetPath);
  +        return ! $this->filesystem->exists($targetPath);
       }
   }
  
  ⨯ src/Services/Core/SlotFinderService.php class_attributes_separation, function_declaration, unary_operator_spaces, braces_position, not_operator_with_successor_space, single_line_  
  @@ -21,14 +21,13 @@
           private readonly AvailabilityRepositoryInterface $availabilityRepository,
       ) {}
   
  -
       /**
        * Check if an entire time period is available without interruptions.
        *
  -     * @param Model $model Schedulable model instance
  -     * @param Carbon $start Period start datetime
  -     * @param Carbon $end Period end datetime
  -     * @param string|null $type Optional availability type filter
  +     * @param  Model  $model  Schedulable model instance
  +     * @param  Carbon  $start  Period start datetime
  +     * @param  Carbon  $end  Period end datetime
  +     * @param  string|null  $type  Optional availability type filter
        * @return bool True if the entire period is available
        */
       public function isPeriodAvailable(
  @@ -75,14 +74,12 @@
           return true;
       }
   
  -
  -
       /**
        * Calculate available time slots by removing impediments from a time range.
        *
  -     * @param Carbon $start Start of the time range
  -     * @param Carbon $end End of the time range
  -     * @param Collection $impediments Collection of impediments
  +     * @param  Carbon  $start  Start of the time range
  +     * @param  Carbon  $end  End of the time range
  +     * @param  Collection  $impediments  Collection of impediments
        * @return Collection<int, array<string, mixed>> Available time slots
        */
       public function getAvailableSlotsFromImpediments(
  @@ -122,14 +119,12 @@
           return $availableSlots;
       }
   
  -
  -
       /**
        * Check if a time slot has no conflicts with schedules or impediments.
        *
  -     * @param Availability $availability The availability containing conflict data
  -     * @param Carbon $start Start of the time slot
  -     * @param Carbon $end End of the time slot
  +     * @param  Availability  $availability  The availability containing conflict data
  +     * @param  Carbon  $start  Start of the time slot
  +     * @param  Carbon  $end  End of the time slot
        * @return bool True if the slot has no conflicts
        */
       private function isTimeSlotConflictFree(
  @@ -138,11 +133,11 @@
           Carbon $end
       ): bool {
           $hasOverlappingSchedule = $availability->schedules->contains(
  -            fn($schedule): bool => $schedule->overlapsWith($start, $end)
  +            fn ($schedule): bool => $schedule->overlapsWith($start, $end)
           );
   
           $hasOverlappingImpediments = $availability->impediments->contains(
  -            fn($impediment): bool => $impediment->overlapsWith($start, $end)
  +            fn ($impediment): bool => $impediment->overlapsWith($start, $end)
           );
   
           return ! $hasOverlappingSchedule && ! $hasOverlappingImpediments;
  
  ⨯ src/Services/ImpedimentService.php                           trailing_comma_in_multiline, phpdoc_separation, not_operator_with_successor_space, no_extra_blank_lines, phpdoc_align  
  @@ -66,8 +66,8 @@
       /**
        * Create a new impediment with explicit availability.
        *
  -     * @param Availability $availability The availability to link to
  -     * @param array $data Entity data
  +     * @param  Availability  $availability  The availability to link to
  +     * @param  array  $data  Entity data
        * @return Impediment Created entity
        */
       public function create(Availability $availability, array $data): Impediment
  @@ -75,7 +75,7 @@
           $this->data = array_merge($data, [
               'availability_id' => $availability->id,
               'schedulable_id' => $this->schedulable->id,
  -            'schedulable_type' => get_class($this->schedulable)
  +            'schedulable_type' => get_class($this->schedulable),
           ]);
   
           // Convert to DTO and validate with new system
  @@ -104,7 +104,8 @@
   
       /**
        * Update an existing impediment.
  -     * @param array<string, mixed> $data
  +     *
  +     * @param  array<string, mixed>  $data
        */
       public function update(int $id, array $data): bool
       {
  @@ -113,7 +114,7 @@
   
           // Trouver l'impediment existant
           $existingImpediment = $this->find($id);
  -        if (!$existingImpediment instanceof Impediment) {
  +        if (! $existingImpediment instanceof Impediment) {
               throw ValidationFailedException::fromViolations(
                   [
                       'id' => sprintf(
  @@ -132,7 +133,6 @@
           // Créer le DTO initial
           $impedimentData = $this->createDTOFromArray($data, OperationType::UPDATE);
   
  -
           // Valider le DTO avec les infos schedulable
           $this->validate($impedimentData->toArray(), OperationType::UPDATE, $id);
   
  @@ -156,7 +156,7 @@
       public function delete(int $id): bool
       {
           $entity = $this->find($id);
  -        if (!$entity instanceof Impediment) {
  +        if (! $entity instanceof Impediment) {
               throw ValidationFailedException::fromViolations(
                   [
                       'id' => sprintf(
  @@ -292,7 +292,7 @@
       {
           $availability = $this->availabilityRepository->findForTimeSlot($this->schedulable, $start, $end, $type);
   
  -        if (!$availability instanceof Availability) {
  +        if (! $availability instanceof Availability) {
               return false;
           }
   
  @@ -306,7 +306,7 @@
       {
           $availability = $this->availabilityRepository->findForTimeSlot($this->schedulable, $start, $end, $type);
   
  -        if (!$availability instanceof Availability) {
  +        if (! $availability instanceof Availability) {
               return collect();
           }
   
  
  ⨯ src/Services/ScheduleService.php trailing_comma_in_multiline, phpdoc_separation, cast_spaces, not_operator_with_successor_space, blank_line_before_statement, ordered_imports, php  
  @@ -4,7 +4,6 @@
   
   namespace Roster\Services;
   
  -use Roster\Models\Impediment;
   use Illuminate\Database\Eloquent\Builder;
   use Illuminate\Support\Carbon;
   use Illuminate\Support\Collection;
  @@ -16,6 +15,7 @@
   use Roster\Enums\EntityType;
   use Roster\Enums\OperationType;
   use Roster\Models\Availability;
  +use Roster\Models\Impediment;
   use Roster\Models\Schedule;
   use Roster\Services\Core\AbstractAvailabilityValidatingService;
   use Roster\Validation\Exceptions\ValidationFailedException;
  @@ -62,8 +62,8 @@
       /**
        * Create a new schedule with explicit availability.
        *
  -     * @param Availability $availability The availability to link to
  -     * @param array $data Entity data
  +     * @param  Availability  $availability  The availability to link to
  +     * @param  array  $data  Entity data
        * @return Schedule Created entity
        */
       public function create(Availability $availability, array $data): Schedule
  @@ -71,7 +71,7 @@
           $this->data = array_merge($data, [
               'availability_id' => $availability->id,
               'schedulable_id' => $this->schedulable->id,
  -            'schedulable_type' => get_class($this->schedulable)
  +            'schedulable_type' => get_class($this->schedulable),
           ]);
   
           // Convert to DTO and validate with new system
  @@ -99,7 +99,8 @@
   
       /**
        * Update an existing schedule.
  -     * @param array<string, mixed> $data
  +     *
  +     * @param  array<string, mixed>  $data
        */
       public function update(int $id, array $data): bool
       {
  @@ -108,7 +109,7 @@
           // Récupérer l'entité existante AVANT validation
           $existingEntity = $this->find($id);
   
  -        if (!$existingEntity instanceof Schedule) {
  +        if (! $existingEntity instanceof Schedule) {
               throw ValidationFailedException::fromViolations(
                   [
                       'id' => sprintf(
  @@ -125,7 +126,7 @@
           $data['id'] = $id;
   
           // Assurez-vous que availability_id est présent dans les données
  -        if (!isset($data['availability_id']) && $existingEntity->availability_id) {
  +        if (! isset($data['availability_id']) && $existingEntity->availability_id) {
               $data['availability_id'] = $existingEntity->availability_id;
           }
   
  @@ -155,7 +156,7 @@
       public function delete(int $id): bool
       {
           $entity = $this->find($id);
  -        if (!$entity instanceof Schedule) {
  +        if (! $entity instanceof Schedule) {
               throw ValidationFailedException::fromViolations(
                   [
                       'id' => sprintf(
  @@ -325,8 +326,8 @@
           );
   
           return $availability instanceof Availability
  -            && !$availability->has_overlapping_schedules
  -            && !$availability->has_overlapping_impediments;
  +            && ! $availability->has_overlapping_schedules
  +            && ! $availability->has_overlapping_impediments;
       }
   
       /**
  @@ -376,12 +377,12 @@
           ?Carbon $searchStart = null
       ): ?array {
           // Vérifier que daily_start et daily_end ne sont pas null
  -        if (!$availability->daily_start || !$availability->daily_end) {
  +        if (! $availability->daily_start || ! $availability->daily_end) {
               return null;
           }
   
           // Vérifier que l'accessibilité est disponible ce jour
  -        if (!$availability->isActiveOnDate($day)) {
  +        if (! $availability->isActiveOnDate($day)) {
               return null;
           }
   
  @@ -413,7 +414,7 @@
           if ($slotStart->minute > 0 || $slotStart->second > 0) {
               $minutes = $slotStart->minute;
               $roundedMinutes = ceil($minutes / $slotInterval) * $slotInterval;
  -            $slotStart->setMinute((int)$roundedMinutes)->setSecond(0);
  +            $slotStart->setMinute((int) $roundedMinutes)->setSecond(0);
           }
   
           // Vérifier que slotStart + durée ne dépasse pas availabilityEnd
  @@ -483,12 +484,12 @@
               $type
           );
   
  -        if (!$availability instanceof Availability) {
  +        if (! $availability instanceof Availability) {
               return false;
           }
   
           // Vérifier que la période est dans les limites de l'accessibilité
  -        if (!$availability->isAvailableForSchedule($start, $end)) {
  +        if (! $availability->isAvailableForSchedule($start, $end)) {
               return false;
           }
   
  @@ -505,7 +506,7 @@
               $end
           );
   
  -        return !$hasScheduleConflict && !$hasImpedimentConflict;
  +        return ! $hasScheduleConflict && ! $hasImpedimentConflict;
       }
   
       /**
  @@ -524,6 +525,7 @@
                   'start' => $start->copy(),
                   'end' => $end->copy(),
               ]);
  +
               return $availableSlots;
           }
   
  
  ⨯ src/Traits/BelongsToSchedulable.php                                                                                                                                ordered_imports  
  @@ -4,9 +4,9 @@
   
   namespace Roster\Traits;
   
  -use Illuminate\Database\Eloquent\Relations\MorphTo;
   use Illuminate\Database\Eloquent\Builder;
   use Illuminate\Database\Eloquent\Model;
  +use Illuminate\Database\Eloquent\Relations\MorphTo;
   use Roster\Exceptions\MissingSchedulableException;
   
   /**
  
  ⨯ src/Traits/DateRangeOverlapTrait.php                                                     unary_operator_spaces, phpdoc_separation, not_operator_with_successor_space, phpdoc_align  
  @@ -18,11 +18,10 @@
        * - Null start becomes year 0001-01-01
        * - Null end becomes year 9999-12-31
        *
  -     * @param Carbon|null $existingStartDate Start of the existing range
  -     * @param Carbon|null $existingEndDate End of the existing range
  -     * @param Carbon|null $newStartDate Start of the new range
  -     * @param Carbon|null $newEndDate End of the new range
  -     *
  +     * @param  Carbon|null  $existingStartDate  Start of the existing range
  +     * @param  Carbon|null  $existingEndDate  End of the existing range
  +     * @param  Carbon|null  $newStartDate  Start of the new range
  +     * @param  Carbon|null  $newEndDate  End of the new range
        * @return bool True if ranges overlap, false otherwise
        */
       public function dateRangesOverlap(
  
  ⨯ src/Traits/FilterableTrait.php                                             concat_space, unary_operator_spaces, phpdoc_separation, not_operator_with_successor_space, phpdoc_align  
  @@ -29,10 +29,9 @@
       /**
        * Applies date range filters to a query builder.
        *
  -     * @param Builder $builder Query builder instance
  -     * @param string $startField Field name for start date
  -     * @param string $endField Field name for end date
  -     *
  +     * @param  Builder  $builder  Query builder instance
  +     * @param  string  $startField  Field name for start date
  +     * @param  string  $endField  Field name for end date
        * @return Builder The modified query builder
        */
       protected function applyDateFilters(
  @@ -56,9 +55,8 @@
        *
        * Supports filtering directly on the model or through a relation.
        *
  -     * @param Builder $builder Query builder instance
  -     * @param string $relation Optional relation name for nested filtering
  -     *
  +     * @param  Builder  $builder  Query builder instance
  +     * @param  string  $relation  Optional relation name for nested filtering
        * @return Builder The modified query builder
        */
       protected function applyTypeFilter(Builder $builder, string $relation = ''): Builder
  @@ -81,8 +79,7 @@
       /**
        * Filters query by a specific day in a JSON days array.
        *
  -     * @param Builder $builder Query builder instance
  -     *
  +     * @param  Builder  $builder  Query builder instance
        * @return Builder The modified query builder
        */
       protected function applyDayFilter(Builder $builder): Builder
  @@ -97,8 +94,7 @@
       /**
        * Applies status filter to a query builder.
        *
  -     * @param Builder $builder Query builder instance
  -     *
  +     * @param  Builder  $builder  Query builder instance
        * @return Builder The modified query builder
        */
       protected function applyStatusFilter(Builder $builder): Builder
  @@ -113,14 +109,13 @@
       /**
        * Applies reason filter with partial matching to a query builder.
        *
  -     * @param Builder $builder Query builder instance
  -     *
  +     * @param  Builder  $builder  Query builder instance
        * @return Builder The modified query builder
        */
       protected function applyReasonFilter(Builder $builder): Builder
       {
           if (isset($this->filters['reason'])) {
  -            $builder->where('reason', 'like', '%' . $this->filters['reason'] . '%');
  +            $builder->where('reason', 'like', '%'.$this->filters['reason'].'%');
           }
   
           return $builder;
  @@ -129,8 +124,7 @@
       /**
        * Filters query by availability ID.
        *
  -     * @param Builder $builder Query builder instance
  -     *
  +     * @param  Builder  $builder  Query builder instance
        * @return Builder The modified query builder
        */
       protected function applyAvailabilityIdFilter(Builder $builder): Builder
  @@ -145,9 +139,8 @@
       /**
        * Filters query by a specific schedulable model.
        *
  -     * @param Builder $builder Query builder instance
  -     * @param Model|null $model The model to filter by
  -     *
  +     * @param  Builder  $builder  Query builder instance
  +     * @param  Model|null  $model  The model to filter by
        * @return Builder The modified query builder
        */
       protected function applySchedulableFilter(Builder $builder, ?Model $model = null): Builder
  @@ -163,7 +156,7 @@
       /**
        * Adds a start date filter.
        *
  -     * @param Carbon $date Start date threshold
  +     * @param  Carbon  $date  Start date threshold
        */
       public function whereStartDate(Carbon $date): self
       {
  @@ -175,7 +168,7 @@
       /**
        * Adds an end date filter.
        *
  -     * @param Carbon $date End date threshold
  +     * @param  Carbon  $date  End date threshold
        */
       public function whereEndDate(Carbon $date): self
       {
  @@ -187,7 +180,7 @@
       /**
        * Adds a status filter.
        *
  -     * @param string $status Status value to filter by
  +     * @param  string  $status  Status value to filter by
        */
       public function whereStatus(string $status): self
       {
  @@ -199,7 +192,7 @@
       /**
        * Adds a reason filter for impediments.
        *
  -     * @param string $reason Reason text to filter by (partial match)
  +     * @param  string  $reason  Reason text to filter by (partial match)
        */
       public function whereReason(string $reason): self
       {
  @@ -211,7 +204,7 @@
       /**
        * Adds an availability ID filter.
        *
  -     * @param int $availabilityId Availability ID to filter by
  +     * @param  int  $availabilityId  Availability ID to filter by
        */
       public function whereAvailabilityId(int $availabilityId): self
       {
  @@ -243,8 +236,7 @@
       /**
        * Checks if a specific filter is currently set.
        *
  -     * @param string $key Filter key to check
  -     *
  +     * @param  string  $key  Filter key to check
        * @return bool True if the filter is set, false otherwise
        */
       public function hasFilter(string $key): bool
  
  ⨯ src/Validation/Attributes/ValidationRule.php                                                                                 braces_position, single_line_empty_body, phpdoc_align  
  @@ -12,9 +12,9 @@
   class ValidationRule
   {
       /**
  -     * @param int|null $priority Priorité d'exécution (plus haut = exécuté en premier)
  -     * @param array<EntityType> $entities Types d'entités supportés
  -     * @param array<OperationType> $operations Types d'opérations supportés
  +     * @param  int|null  $priority  Priorité d'exécution (plus haut = exécuté en premier)
  +     * @param  array<EntityType>  $entities  Types d'entités supportés
  +     * @param  array<OperationType>  $operations  Types d'opérations supportés
        */
       public function __construct(
           public ?int $priority = 50,
  
  ⨯ src/Validation/Cache/RuleCacheGenerator.php function_declaration, blank_line_after_opening_tag, concat_space, no_unused_imports, not_operator_with_successor_space, blank_line_bef  
  @@ -1,4 +1,5 @@
   <?php
  +
   // src/Validation/Cache/RuleCacheGenerator.php
   
   declare(strict_types=1);
  @@ -5,9 +6,8 @@
   
   namespace Roster\Validation\Cache;
   
  +use Roster\Validation\Attributes\ValidationRule;
   use Roster\Validation\RuleScanner;
  -use Roster\Validation\Attributes\ValidationRule;
  -use ReflectionClass;
   
   class RuleCacheGenerator
   {
  @@ -27,12 +27,12 @@
   
           // Créer le répertoire si nécessaire
           $directory = dirname($this->cachePath);
  -        if (!is_dir($directory)) {
  +        if (! is_dir($directory)) {
               mkdir($directory, 0755, true);
           }
   
           // Écrire le fichier atomiquement
  -        $tempFile = $this->cachePath . '.tmp';
  +        $tempFile = $this->cachePath.'.tmp';
           if (file_put_contents($tempFile, $content) === false) {
               return false;
           }
  @@ -47,12 +47,13 @@
   
       public function isCacheFresh(): bool
       {
  -        if (!file_exists($this->cachePath)) {
  +        if (! file_exists($this->cachePath)) {
               return false;
           }
   
           // Vérifier si le fichier a été généré il y a moins de X heures
           $maxAge = config('roster-validation.cache_max_age_hours', 24);
  +
           return (time() - filemtime($this->cachePath)) < ($maxAge * 3600);
       }
   
  @@ -96,16 +97,16 @@
   
       private function buildRuleEntry(string $className, ValidationRule $attribute): string
       {
  -        $entities = array_map(fn($e) => $e->value, $attribute->entities);
  -        $operations = array_map(fn($o) => $o->value, $attribute->operations);
  +        $entities = array_map(fn ($e) => $e->value, $attribute->entities);
  +        $operations = array_map(fn ($o) => $o->value, $attribute->operations);
   
           $indent = '    ';
  -        $entry = $indent . "'" . addslashes($className) . "' => [\n";
  -        $entry .= $indent . $indent . "'priority' => " . $attribute->priority . ",\n";
  -        $entry .= $indent . $indent . "'entities' => [" . implode(', ', array_map(fn($e) => "'$e'", $entities)) . "],\n";
  -        $entry .= $indent . $indent . "'operations' => [" . implode(', ', array_map(fn($o) => "'$o'", $operations)) . "],\n";
  -        $entry .= $indent . $indent . "'class' => '" . addslashes($className) . "',\n";
  -        $entry .= $indent . "],\n";
  +        $entry = $indent."'".addslashes($className)."' => [\n";
  +        $entry .= $indent.$indent."'priority' => ".$attribute->priority.",\n";
  +        $entry .= $indent.$indent."'entities' => [".implode(', ', array_map(fn ($e) => "'$e'", $entities))."],\n";
  +        $entry .= $indent.$indent."'operations' => [".implode(', ', array_map(fn ($o) => "'$o'", $operations))."],\n";
  +        $entry .= $indent.$indent."'class' => '".addslashes($className)."',\n";
  +        $entry .= $indent."],\n";
   
           return $entry;
       }
  
  ⨯ src/Validation/Context/ValidationContext.php                                                       function_declaration, not_operator_with_successor_space, binary_operator_spaces  
  @@ -43,11 +43,11 @@
           ?Model $model = null,
           mixed $currentEntity = null
       ) {
  -        $this->operationType  = $operationType;
  -        $this->entityType     = $entityType;
  -        $this->data           = $data;
  -        $this->model          = $model;
  -        $this->currentEntity  = $currentEntity;
  +        $this->operationType = $operationType;
  +        $this->entityType = $entityType;
  +        $this->data = $data;
  +        $this->model = $model;
  +        $this->currentEntity = $currentEntity;
       }
   
       /* -----------------------------------------------------------------
  @@ -97,7 +97,7 @@
       {
           return array_filter(
               $this->data,
  -            static fn($value): bool => $value !== null
  +            static fn ($value): bool => $value !== null
           );
       }
   
  @@ -162,7 +162,7 @@
   
       public function addViolation(string $field, string $message): void
       {
  -        if (!isset($this->violations[$field])) {
  +        if (! isset($this->violations[$field])) {
               $this->violations[$field] = [];
           }
   
  
  ⨯ src/Validation/Exceptions/ValidationFailedException.php                                                                                              concat_space, ordered_imports  
  @@ -4,10 +4,10 @@
   
   namespace Roster\Validation\Exceptions;
   
  -use Throwable;
  +use InvalidArgumentException;
   use Roster\Enums\EntityType;
   use Roster\Enums\OperationType;
  -use InvalidArgumentException;
  +use Throwable;
   
   class ValidationFailedException extends InvalidArgumentException
   {
  @@ -122,6 +122,6 @@
               }
           }
   
  -        return $base . ': ' . implode(' ; ', $formattedViolations);
  +        return $base.': '.implode(' ; ', $formattedViolations);
       }
   }
  
  ⨯ src/Validation/RuleScanner.php class_attributes_separation, new_with_parentheses, function_declaration, concat_space, trailing_comma_in_multiline, not_operator_with_successor_spa  
  @@ -6,11 +6,11 @@
   
   use Illuminate\Support\Facades\Log;
   use ReflectionClass;
  -use Throwable;
   use Roster\Contracts\Validation\RuleInterface;
   use Roster\Validation\Attributes\ValidationRule;
   use Roster\Validation\Cache\RuleCacheGenerator;
   use Symfony\Component\Finder\Finder;
  +use Throwable;
   
   class RuleScanner
   {
  @@ -24,6 +24,7 @@
       private ?array $cachedRules = null;
   
       private bool $useCacheFile;
  +
       private ?string $cacheFile;
   
       public function __construct(
  @@ -43,9 +44,10 @@
   
           return $this->doScan();
       }
  +
       private function shouldUseCache(): bool
       {
  -        if (!$this->cacheFile || !file_exists($this->cacheFile)) {
  +        if (! $this->cacheFile || ! file_exists($this->cacheFile)) {
               return false;
           }
   
  @@ -56,6 +58,7 @@
   
           // En développement, vérifier si le cache est frais
           $cacheGenerator = new RuleCacheGenerator($this);
  +
           return $cacheGenerator->isCacheFresh();
       }
   
  @@ -65,7 +68,7 @@
               $rules = require $this->cacheFile;
   
               // Valider la structure du cache
  -            if (!is_array($rules)) {
  +            if (! is_array($rules)) {
                   throw new \RuntimeException('Invalid cache file structure');
               }
   
  @@ -75,11 +78,11 @@
                   $result[$className] = new Attributes\ValidationRule(
                       priority: $data['priority'],
                       entities: array_map(
  -                        fn($e) => \Roster\Enums\EntityType::from($e),
  +                        fn ($e) => \Roster\Enums\EntityType::from($e),
                           $data['entities']
                       ),
                       operations: array_map(
  -                        fn($o) => \Roster\Enums\OperationType::from($o),
  +                        fn ($o) => \Roster\Enums\OperationType::from($o),
                           $data['operations']
                       )
                   );
  @@ -90,7 +93,7 @@
               // Si le cache est corrompu, régénérer
               Log::warning('Roster rule cache corrupted, regenerating', [
                   'file' => $this->cacheFile,
  -                'error' => $e->getMessage()
  +                'error' => $e->getMessage(),
               ]);
   
               return $this->regenerateCache();
  @@ -106,7 +109,6 @@
           return $rules;
       }
   
  -
       private function doScan(): array
       {
   
  @@ -118,11 +120,11 @@
           $rules = [];
   
           foreach ($this->ruleDirectories as $ruleDirectory) {
  -            if (!is_dir($ruleDirectory)) {
  +            if (! is_dir($ruleDirectory)) {
                   continue;
               }
   
  -            $finder = new Finder();
  +            $finder = new Finder;
               $finder->files()->in($ruleDirectory)->name('*Rule.php');
   
               foreach ($finder as $file) {
  @@ -149,9 +151,10 @@
               }
           }
   
  -        uasort($rules, fn($a, $b): int => $b->priority <=> $a->priority);
  +        uasort($rules, fn ($a, $b): int => $b->priority <=> $a->priority);
   
           $this->cachedRules = $rules;
  +
           return $rules;
       }
   
  @@ -162,7 +165,7 @@
               try {
                   $rules[] = app()->make($className);
               } catch (Throwable $e) {
  -                $rules[] = new $className();
  +                $rules[] = new $className;
               }
           }
   
  @@ -175,7 +178,7 @@
           if (preg_match('/namespace\s+([^;]+);/', $content, $namespaceMatches)) {
               $namespace = $namespaceMatches[1];
               if (preg_match('/class\s+(\w+)/', $content, $classMatches)) {
  -                return $namespace . '\\' . $classMatches[1];
  +                return $namespace.'\\'.$classMatches[1];
               }
           }
   
  
  ⨯ src/Validation/Rules/AbstractRule.php                                                                                                                  blank_line_before_statement  
  @@ -20,6 +20,7 @@
   
           if ($attributes !== []) {
               $attribute = $attributes[0]->newInstance();
  +
               return $attribute->priority ?? 50;
           }
   
  
  ⨯ src/Validation/Rules/AvailabilityDateRangeRule.php                                                  single_quote, concat_space, not_operator_with_successor_space, ordered_imports  
  @@ -7,9 +7,9 @@
   use Exception;
   use Illuminate\Support\Carbon;
   use Roster\Contracts\Validation\ValidationContextInterface;
  -use Roster\Validation\Attributes\ValidationRule;
   use Roster\Enums\EntityType;
   use Roster\Enums\OperationType;
  +use Roster\Validation\Attributes\ValidationRule;
   
   #[ValidationRule(
       priority: 60,
  @@ -32,7 +32,7 @@
           if ($operationType === OperationType::CREATE) {
               // CREATE : les deux dates doivent être fournies dans le contexte
   
  -            if (!$validationContext->has('validity_start') || !$validationContext->has('validity_end')) {
  +            if (! $validationContext->has('validity_start') || ! $validationContext->has('validity_end')) {
                   return; // Validation des champs requis gérée par une autre règle
               }
   
  @@ -46,7 +46,7 @@
               $hasEnd = $validationContext->has('validity_end');
   
               // Si aucune des deux dates n'est modifiée, on ne valide pas
  -            if (!$hasStart && !$hasEnd) {
  +            if (! $hasStart && ! $hasEnd) {
                   return;
               }
   
  @@ -68,7 +68,7 @@
       {
           if ($operationType === OperationType::CREATE) {
               // CREATE : les deux heures doivent être fournies dans le contexte
  -            if (!$validationContext->has('daily_start') || !$validationContext->has('daily_end')) {
  +            if (! $validationContext->has('daily_start') || ! $validationContext->has('daily_end')) {
                   return; // Validation des champs requis gérée par une autre règle
               }
   
  @@ -82,7 +82,7 @@
               $hasEnd = $validationContext->has('daily_end');
   
               // Si aucune des deux heures n'est modifiée, on ne valide pas
  -            if (!$hasStart && !$hasEnd) {
  +            if (! $hasStart && ! $hasEnd) {
                   return;
               }
   
  @@ -134,7 +134,7 @@
                       $validationContext->setViolation(
   
                           'max_duration',
  -                        sprintf("Availability period cannot exceed %d days", $maxDays)
  +                        sprintf('Availability period cannot exceed %d days', $maxDays)
                       );
                   }
               }
  @@ -142,7 +142,7 @@
               $validationContext->setViolation(
   
                   'date_format',
  -                "Invalid date format: " . $exception->getMessage()
  +                'Invalid date format: '.$exception->getMessage()
               );
           }
       }
  @@ -176,7 +176,7 @@
                   $validationContext->setViolation(
   
                       'min_duration',
  -                    "Minimum duration must be at least 15 minutes"
  +                    'Minimum duration must be at least 15 minutes'
                   );
               }
           } catch (Exception $exception) {
  @@ -183,7 +183,7 @@
               $validationContext->setViolation(
   
                   'time_format',
  -                "Invalid time format: " . $exception->getMessage()
  +                'Invalid time format: '.$exception->getMessage()
               );
           }
       }
  
  ⨯ src/Validation/Rules/AvailabilityDaysCoherenceRule.php                                             not_operator_with_successor_space, blank_line_before_statement, ordered_imports  
  @@ -7,10 +7,10 @@
   use Exception;
   use Illuminate\Support\Carbon;
   use Roster\Contracts\Validation\ValidationContextInterface;
  -use Roster\Validation\Attributes\ValidationRule;
  +use Roster\Enums\DaysOfWeek;
   use Roster\Enums\EntityType;
   use Roster\Enums\OperationType;
  -use Roster\Enums\DaysOfWeek;
  +use Roster\Validation\Attributes\ValidationRule;
   
   #[ValidationRule(
       priority: 85,
  @@ -22,7 +22,7 @@
       public function validate(ValidationContextInterface $validationContext): void
       {
           // Si les jours ne sont pas fournis, pas de validation
  -        if (!$validationContext->has('days')) {
  +        if (! $validationContext->has('days')) {
               return;
           }
   
  @@ -34,11 +34,12 @@
           }
   
           // Vérification du type avant traitement métier
  -        if (!is_array($days)) {
  +        if (! is_array($days)) {
               $validationContext->setViolation(
                   'days',
                   'Days must be an array'
               );
  +
               return;
           }
   
  @@ -52,11 +53,12 @@
           $validDays = DaysOfWeek::values();
   
           foreach ($days as $day) {
  -            if (!in_array($day, $validDays, true)) {
  +            if (! in_array($day, $validDays, true)) {
                   $validationContext->setViolation(
                       'days',
                       sprintf("Day '%s' is not a valid day of week", $day)
                   );
  +
                   return;
               }
           }
  @@ -92,7 +94,7 @@
   
           // Vérifier chaque jour fourni
           foreach ($days as $day) {
  -            if (!in_array($day, $daysInPeriod, true)) {
  +            if (! in_array($day, $daysInPeriod, true)) {
                   $periodDescription = roster_format_period_days_for_display($daysInPeriod);
   
                   $validationContext->setViolation(
  
  ⨯ src/Validation/Rules/AvailabilityOverlapRule.php                                                                                not_operator_with_successor_space, ordered_imports  
  @@ -4,13 +4,13 @@
   
   namespace Roster\Validation\Rules;
   
  +use Exception;
   use Illuminate\Database\Eloquent\Model;
   use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
  -use Exception;
   use Roster\Contracts\Validation\ValidationContextInterface;
  -use Roster\Validation\Attributes\ValidationRule;
   use Roster\Enums\EntityType;
   use Roster\Enums\OperationType;
  +use Roster\Validation\Attributes\ValidationRule;
   
   #[ValidationRule(
       priority: 80,
  @@ -25,7 +25,7 @@
           $currentEntity = $validationContext->getCurrentEntity();
   
           // Pour UPDATE, si l'entité n'existe pas, on ne peut pas valider les chevauchements
  -        if ($operationType === OperationType::UPDATE && !$currentEntity) {
  +        if ($operationType === OperationType::UPDATE && ! $currentEntity) {
               return;
           }
   
  @@ -33,7 +33,7 @@
           $requiredFields = ['daily_start', 'daily_end', 'days', 'validity_start', 'validity_end'];
   
           foreach ($requiredFields as $requiredField) {
  -            if (!$validationContext->has($requiredField)) {
  +            if (! $validationContext->has($requiredField)) {
                   // Si en UPDATE et que le champ n'est pas fourni, on vérifie si l'entité existante l'a
                   if ($operationType === OperationType::UPDATE && $currentEntity) {
                       // On peut continuer car la valeur sera récupérée depuis l'entité existante
  @@ -47,7 +47,7 @@
   
           try {
               $schedulable = $validationContext->getSchedulable();
  -            if (!$schedulable instanceof Model) {
  +            if (! $schedulable instanceof Model) {
                   return;
               }
   
  
  ⨯ src/Validation/Rules/AvailabilityOwnershipRule.php                                                 not_operator_with_successor_space, blank_line_before_statement, ordered_imports  
  @@ -7,9 +7,9 @@
   use Illuminate\Database\Eloquent\Model;
   use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
   use Roster\Contracts\Validation\ValidationContextInterface;
  -use Roster\Validation\Attributes\ValidationRule;
   use Roster\Enums\EntityType;
   use Roster\Enums\OperationType;
  +use Roster\Validation\Attributes\ValidationRule;
   
   #[ValidationRule(
       priority: 90,
  @@ -24,27 +24,28 @@
           $availabilityId = $validationContext->get('availability_id');
   
           // Si CREATE, availability_id doit être présent
  -        if ($operationType === OperationType::CREATE && !$availabilityId) {
  +        if ($operationType === OperationType::CREATE && ! $availabilityId) {
               $validationContext->setViolation(
                   'availability_id',
                   'Must be linked to an availability'
               );
  +
               return;
           }
   
           // Pour UPDATE, on utilise l'entité courante si availability_id n'est pas fourni
           $currentEntity = $validationContext->getCurrentEntity();
  -        if ($operationType === OperationType::UPDATE && !$availabilityId && $currentEntity) {
  +        if ($operationType === OperationType::UPDATE && ! $availabilityId && $currentEntity) {
               $availabilityId = $currentEntity->availability_id ?? null;
           }
   
  -        if (!$availabilityId) {
  +        if (! $availabilityId) {
               // Rien à vérifier si aucun availability_id
               return;
           }
   
           $schedulable = $validationContext->getSchedulable();
  -        if (!$schedulable instanceof Model) {
  +        if (! $schedulable instanceof Model) {
               return; // SchedulableValidationRule doit déjà gérer ça
           }
   
  @@ -51,11 +52,12 @@
           $availabilityRepository = app(AvailabilityRepositoryInterface::class);
           $availability = $availabilityRepository->find($availabilityId);
   
  -        if (!$availability) {
  +        if (! $availability) {
               $validationContext->setViolation(
                   'availability_id',
                   'Invalid availability ID'
               );
  +
               return;
           }
   
  
  ⨯ src/Validation/Rules/AvailabilityTimeRangeRule.php                                                                              not_operator_with_successor_space, ordered_imports  
  @@ -4,14 +4,14 @@
   
   namespace Roster\Validation\Rules;
   
  -use Roster\Models\Availability;
  -use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
   use Exception;
   use Illuminate\Support\Carbon;
  +use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
   use Roster\Contracts\Validation\ValidationContextInterface;
  -use Roster\Validation\Attributes\ValidationRule;
   use Roster\Enums\EntityType;
   use Roster\Enums\OperationType;
  +use Roster\Models\Availability;
  +use Roster\Validation\Attributes\ValidationRule;
   
   #[ValidationRule(
       priority: 85,
  @@ -22,7 +22,7 @@
   {
       public function validate(ValidationContextInterface $validationContext): void
       {
  -        if (!$validationContext->has('start_datetime') || !$validationContext->has('end_datetime')) {
  +        if (! $validationContext->has('start_datetime') || ! $validationContext->has('end_datetime')) {
               return;
           }
   
  @@ -31,7 +31,7 @@
               $end = Carbon::parse($validationContext->get('end_datetime'));
               $availabilityId = $validationContext->get('availability_id');
   
  -            if (!$availabilityId) {
  +            if (! $availabilityId) {
                   return; // AvailabilityOwnershipRule devrait déjà avoir échoué
               }
   
  @@ -39,7 +39,7 @@
               $availabilityRepository = app(AvailabilityRepositoryInterface::class);
               $availability = $availabilityRepository->find($availabilityId);
   
  -            if (!$availability) {
  +            if (! $availability) {
                   return; // AvailabilityOwnershipRule devrait déjà avoir échoué
               }
   
  @@ -57,7 +57,7 @@
       ): void {
           // 1. Vérifie le jour de la semaine
           $dayOfWeek = strtolower($start->englishDayOfWeek);
  -        if (!in_array($dayOfWeek, $availability->days, true)) {
  +        if (! in_array($dayOfWeek, $availability->days, true)) {
               $validationContext->setViolation(
                   'start_datetime',
                   sprintf('Day %s is not available in this availability', $dayOfWeek)
  
  ⨯ src/Validation/Rules/AvailabilityTypeRule.php                                                             not_operator_with_successor_space, no_extra_blank_lines, ordered_imports  
  @@ -5,9 +5,9 @@
   namespace Roster\Validation\Rules;
   
   use Roster\Contracts\Validation\ValidationContextInterface;
  -use Roster\Validation\Attributes\ValidationRule;
   use Roster\Enums\EntityType;
   use Roster\Enums\OperationType;
  +use Roster\Validation\Attributes\ValidationRule;
   
   #[ValidationRule(
       priority: 80,
  @@ -19,7 +19,7 @@
       public function validate(ValidationContextInterface $validationContext): void
       {
           // Si le champ n’est pas présent (PATCH / UPDATE partiel)
  -        if (!$validationContext->has('type')) {
  +        if (! $validationContext->has('type')) {
               return;
           }
   
  @@ -38,9 +38,8 @@
               return;
           }
   
  -
           // Validation stricte
  -        if (!in_array($type, $allowedTypes, true)) {
  +        if (! in_array($type, $allowedTypes, true)) {
               $validationContext->setViolation(
                   'type',
                   sprintf("Invalid type '%s'", $type)
  
  ⨯ src/Validation/Rules/DaysValidationRule.php                                                        not_operator_with_successor_space, blank_line_before_statement, ordered_imports  
  @@ -6,9 +6,9 @@
   
   use Roster\Contracts\Validation\ValidationContextInterface;
   use Roster\Enums\DaysOfWeek;
  -use Roster\Validation\Attributes\ValidationRule;
   use Roster\Enums\EntityType;
   use Roster\Enums\OperationType;
  +use Roster\Validation\Attributes\ValidationRule;
   
   #[ValidationRule(
       priority: 90,
  @@ -32,7 +32,7 @@
       {
           // Pour CREATE, on valide seulement si days est fourni
           // Si non fourni, le DTO appliquera la valeur par défaut
  -        if (!$validationContext->has('days')) {
  +        if (! $validationContext->has('days')) {
               return; // Valeur par défaut sera appliquée par le DTO
           }
   
  @@ -42,7 +42,7 @@
   
       private function validateForUpdate(ValidationContextInterface $validationContext): void
       {
  -        if (!$validationContext->has('days')) {
  +        if (! $validationContext->has('days')) {
               // Pour UPDATE, si days n'est pas fourni, on ne change rien
               return;
           }
  @@ -53,11 +53,12 @@
   
       private function validateDaysArray(mixed $days, ValidationContextInterface $validationContext): void
       {
  -        if (!is_array($days)) {
  +        if (! is_array($days)) {
               $validationContext->setViolation(
                   'days',
                   'Days must be an array'
               );
  +
               return;
           }
   
  @@ -66,6 +67,7 @@
                   'days',
                   'Days array cannot be empty'
               );
  +
               return;
           }
   
  @@ -72,11 +74,12 @@
           // Valider que chaque jour est valide
           $validDays = DaysOfWeek::values();
           foreach ($days as $day) {
  -            if (!in_array($day, $validDays, true)) {
  +            if (! in_array($day, $validDays, true)) {
                   $validationContext->setViolation(
                       'days',
                       sprintf("Invalid day '%s'. Valid days are: %s", $day, implode(', ', $validDays))
                   );
  +
                   return;
               }
           }
  
  ⨯ src/Validation/Rules/DurationRule.php                                                 single_quote, concat_space, not_operator_with_successor_space, ordered_imports, phpdoc_align  
  @@ -7,9 +7,9 @@
   use Exception;
   use Illuminate\Support\Carbon;
   use Roster\Contracts\Validation\ValidationContextInterface;
  -use Roster\Validation\Attributes\ValidationRule;
   use Roster\Enums\EntityType;
   use Roster\Enums\OperationType;
  +use Roster\Validation\Attributes\ValidationRule;
   
   #[ValidationRule(
       priority: 70,
  @@ -31,17 +31,17 @@
       }
   
       /**
  -     * @param array<string, mixed> $data
  +     * @param  array<string, mixed>  $data
        */
       private function validateAvailabilityDuration(ValidationContextInterface $validationContext, array $data, OperationType $operationType): void
       {
           // CREATE : les deux champs doivent être présents
  -        if ($operationType === OperationType::CREATE && (!isset($data['start_time']) || !isset($data['end_time']))) {
  +        if ($operationType === OperationType::CREATE && (! isset($data['start_time']) || ! isset($data['end_time']))) {
               return;
           }
   
           // UPDATE : ne vérifier que si l'un des deux champs est fourni
  -        if ($operationType === OperationType::UPDATE && !isset($data['start_time']) && !isset($data['end_time'])) {
  +        if ($operationType === OperationType::UPDATE && ! isset($data['start_time']) && ! isset($data['end_time'])) {
               return;
           }
   
  @@ -49,7 +49,7 @@
               $start = isset($data['start_time']) ? Carbon::parse($data['start_time']) : null;
               $end = isset($data['end_time']) ? Carbon::parse($data['end_time']) : null;
   
  -            if (!$start instanceof Carbon || !$end instanceof Carbon) {
  +            if (! $start instanceof Carbon || ! $end instanceof Carbon) {
                   return; // on ne peut pas calculer la durée
               }
   
  @@ -59,7 +59,7 @@
                   $validationContext->setViolation(
                       'duration',
                       sprintf(
  -                        "Minimum duration of %d minutes required for availability. Got %d minutes",
  +                        'Minimum duration of %d minutes required for availability. Got %d minutes',
                           $minimumMinutes,
                           $start->diffInMinutes($end)
                       )
  @@ -66,22 +66,22 @@
                   );
               }
           } catch (Exception $exception) {
  -            $validationContext->setViolation('time_format', "Invalid time format: " . $exception->getMessage());
  +            $validationContext->setViolation('time_format', 'Invalid time format: '.$exception->getMessage());
           }
       }
   
       /**
  -     * @param array<string, mixed> $data
  +     * @param  array<string, mixed>  $data
        */
       private function validateDateTimeDuration(ValidationContextInterface $validationContext, array $data, OperationType $operationType): void
       {
           // CREATE : les deux champs doivent être présents
  -        if ($operationType === OperationType::CREATE && (!isset($data['start_datetime']) || !isset($data['end_datetime']))) {
  +        if ($operationType === OperationType::CREATE && (! isset($data['start_datetime']) || ! isset($data['end_datetime']))) {
               return;
           }
   
           // UPDATE : ne vérifier que si l'un des deux champs est fourni
  -        if ($operationType === OperationType::UPDATE && !isset($data['start_datetime']) && !isset($data['end_datetime'])) {
  +        if ($operationType === OperationType::UPDATE && ! isset($data['start_datetime']) && ! isset($data['end_datetime'])) {
               return;
           }
   
  @@ -89,7 +89,7 @@
               $start = isset($data['start_datetime']) ? Carbon::parse($data['start_datetime']) : null;
               $end = isset($data['end_datetime']) ? Carbon::parse($data['end_datetime']) : null;
   
  -            if (!$start instanceof Carbon || !$end instanceof Carbon) {
  +            if (! $start instanceof Carbon || ! $end instanceof Carbon) {
                   return; // on ne peut pas calculer la durée
               }
   
  @@ -100,7 +100,7 @@
                   $validationContext->setViolation(
                       'duration',
                       sprintf(
  -                        "Minimum duration of %d minutes required for %s. Got %d minutes",
  +                        'Minimum duration of %d minutes required for %s. Got %d minutes',
                           $minimumMinutes,
                           $entityType->displayName(),
                           $start->diffInMinutes($end)
  @@ -108,7 +108,7 @@
                   );
               }
           } catch (Exception $exception) {
  -            $validationContext->setViolation('datetime_format', "Invalid datetime format: " . $exception->getMessage());
  +            $validationContext->setViolation('datetime_format', 'Invalid datetime format: '.$exception->getMessage());
           }
       }
   }
  
  ⨯ src/Validation/Rules/FutureDateRule.php                                                                                         not_operator_with_successor_space, ordered_imports  
  @@ -7,9 +7,9 @@
   use Exception;
   use Illuminate\Support\Carbon;
   use Roster\Contracts\Validation\ValidationContextInterface;
  -use Roster\Validation\Attributes\ValidationRule;
   use Roster\Enums\EntityType;
   use Roster\Enums\OperationType;
  +use Roster\Validation\Attributes\ValidationRule;
   
   #[ValidationRule(
       priority: 40,
  @@ -20,7 +20,7 @@
   {
       public function validate(ValidationContextInterface $validationContext): void
       {
  -        if (!$this->shouldValidateFutureDates()) {
  +        if (! $this->shouldValidateFutureDates()) {
               return;
           }
   
  @@ -39,7 +39,7 @@
   
       private function validateFutureAvailability(ValidationContextInterface $validationContext): void
       {
  -        if (!$validationContext->has('start_date')) {
  +        if (! $validationContext->has('start_date')) {
               return;
           }
   
  @@ -59,7 +59,7 @@
   
       private function validateFutureDateTime(ValidationContextInterface $validationContext): void
       {
  -        if (!$validationContext->has('start_datetime')) {
  +        if (! $validationContext->has('start_datetime')) {
               return;
           }
   
  
  ⨯ src/Validation/Rules/RequiredFieldsRule.php                                                                                     not_operator_with_successor_space, ordered_imports  
  @@ -5,9 +5,9 @@
   namespace Roster\Validation\Rules;
   
   use Roster\Contracts\Validation\ValidationContextInterface;
  -use Roster\Validation\Attributes\ValidationRule;
   use Roster\Enums\EntityType;
   use Roster\Enums\OperationType;
  +use Roster\Validation\Attributes\ValidationRule;
   
   #[ValidationRule(
       priority: 100,
  @@ -42,7 +42,7 @@
           // CREATE : tous les champs doivent être présents
           $requiredFields = $this->getRequiredFields($entityType);
           foreach ($requiredFields as $requiredField) {
  -            if (!array_key_exists($requiredField, $safeData)) {
  +            if (! array_key_exists($requiredField, $safeData)) {
                   $validationContext->setViolation(
                       $requiredField,
                       sprintf("Field '%s' is required", $requiredField)
  
  ⨯ src/Validation/Rules/SchedulableConsistencyRule.php                                                not_operator_with_successor_space, blank_line_before_statement, ordered_imports  
  @@ -4,12 +4,12 @@
   
   namespace Roster\Validation\Rules;
   
  +use Illuminate\Support\Facades\App;
   use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
  -use Roster\Validation\Attributes\ValidationRule;
  +use Roster\Contracts\Validation\ValidationContextInterface;
   use Roster\Enums\EntityType;
   use Roster\Enums\OperationType;
  -use Illuminate\Support\Facades\App;
  -use Roster\Contracts\Validation\ValidationContextInterface;
  +use Roster\Validation\Attributes\ValidationRule;
   
   #[ValidationRule(
       priority: 95,
  @@ -22,16 +22,17 @@
       {
           $entityType = $validationContext->getEntityType();
   
  -        if (!in_array($entityType, [EntityType::SCHEDULE, EntityType::IMPEDIMENT])) {
  +        if (! in_array($entityType, [EntityType::SCHEDULE, EntityType::IMPEDIMENT])) {
               return;
           }
   
           // Vérifier que schedulable_id et schedulable_type sont présents
  -        if (!$validationContext->has('schedulable_id') || !$validationContext->has('schedulable_type')) {
  +        if (! $validationContext->has('schedulable_id') || ! $validationContext->has('schedulable_type')) {
               $validationContext->setViolation(
                   'schedulable',
                   'Schedulable information is required'
               );
  +
               return;
           }
   
  @@ -40,7 +41,7 @@
   
           // Vérifier que l'availability appartient au même schedulable
           $availabilityId = $validationContext->get('availability_id');
  -        if (!$availabilityId) {
  +        if (! $availabilityId) {
               return; // AvailabilityOwnershipRule gérera cela
           }
   
  @@ -47,7 +48,7 @@
           $availabilityRepository = App::make(AvailabilityRepositoryInterface::class);
           $availability = $availabilityRepository->find($availabilityId);
   
  -        if (!$availability) {
  +        if (! $availability) {
               return; // AvailabilityOwnershipRule gérera cela
           }
   
  
  ⨯ src/Validation/Rules/SchedulableValidationRule.php                                                 not_operator_with_successor_space, blank_line_before_statement, ordered_imports  
  @@ -6,9 +6,9 @@
   
   use Illuminate\Database\Eloquent\Model;
   use Roster\Contracts\Validation\ValidationContextInterface;
  -use Roster\Validation\Attributes\ValidationRule;
   use Roster\Enums\EntityType;
   use Roster\Enums\OperationType;
  +use Roster\Validation\Attributes\ValidationRule;
   
   #[ValidationRule(
       priority: 110,
  @@ -25,11 +25,12 @@
           $ownerFields = ['schedulable_id', 'schedulable_type'];
           $safeData = $validationContext->safeData();
   
  -        if (!$schedulable instanceof Model) {
  +        if (! $schedulable instanceof Model) {
               $validationContext->setViolation(
                   'schedulable',
                   'No schedulable resource specified. Call for() with a schedulable entity before executing the operation.'
               );
  +
               return;
           }
   
  @@ -64,11 +65,12 @@
           $schedulableId = $validationContext->get('schedulable_id');
           $schedulableType = $validationContext->get('schedulable_type');
   
  -        if (!$schedulableId || !$schedulableType) {
  +        if (! $schedulableId || ! $schedulableType) {
               $validationContext->setViolation(
                   'schedulable',
                   'Schedulable ID and type are required'
               );
  +
               return;
           }
   
  
  ⨯ src/Validation/Rules/ScheduleOverlapRule.php                                 not_operator_with_successor_space, no_extra_blank_lines, blank_line_before_statement, ordered_imports  
  @@ -4,14 +4,14 @@
   
   namespace Roster\Validation\Rules;
   
  -use Roster\Contracts\Repository\ScheduleRepositoryInterface;
  -use Roster\Contracts\Repository\ImpedimentRepositoryInterface;
   use Exception;
   use Illuminate\Support\Carbon;
  +use Roster\Contracts\Repository\ImpedimentRepositoryInterface;
  +use Roster\Contracts\Repository\ScheduleRepositoryInterface;
   use Roster\Contracts\Validation\ValidationContextInterface;
  -use Roster\Validation\Attributes\ValidationRule;
   use Roster\Enums\EntityType;
   use Roster\Enums\OperationType;
  +use Roster\Validation\Attributes\ValidationRule;
   
   #[ValidationRule(
       priority: 80,
  @@ -23,7 +23,7 @@
       public function validate(ValidationContextInterface $validationContext): void
       {
   
  -        if (!$validationContext->has('start_datetime') || !$validationContext->has('end_datetime')) {
  +        if (! $validationContext->has('start_datetime') || ! $validationContext->has('end_datetime')) {
               return;
           }
   
  @@ -32,24 +32,20 @@
               $end = Carbon::parse($validationContext->get('end_datetime'));
               $availabilityId = $validationContext->get('availability_id');
   
  -
  -            if (!$availabilityId) {
  +            if (! $availabilityId) {
                   return;
               }
   
               $currentEntity = $validationContext->getCurrentEntity();
   
  -
               if ($currentEntity) {
               }
   
               $excludeId = $currentEntity ? ($currentEntity->id ?? null) : null;
   
  -
               // 1. Vérifie chevauchement avec autres schedules
               $scheduleRepository = app(ScheduleRepositoryInterface::class);
   
  -
               // Vérifiez d'abord SANS exclusion pour voir ce qui existe
               $allOverlapping = $scheduleRepository->findOverlappingSchedules($availabilityId, $start, $end);
               if ($allOverlapping->count() > 0) {
  @@ -68,13 +64,12 @@
   
               $hasScheduleOverlap = $scheduleRepository->hasOverlappingSchedule($availabilityId, $start, $end, $excludeId);
   
  -
  -
               if ($hasScheduleOverlap) {
                   $validationContext->setViolation(
                       'overlap',
                       'Schedule overlaps with an existing schedule'
                   );
  +
                   return;
               }
   
  @@ -82,12 +77,12 @@
               $impedimentRepository = app(ImpedimentRepositoryInterface::class);
               $hasImpedimentOverlap = $impedimentRepository->hasOverlappingImpediments($availabilityId, $start, $end);
   
  -
               if ($hasImpedimentOverlap) {
                   $validationContext->setViolation(
                       'overlap',
                       'Schedule overlaps with an existing impediment'
                   );
  +
                   return;
               }
           } catch (Exception $exception) {
  
  ⨯ src/Validation/Rules/TimeSlotDateTimeRule.php                                                       single_quote, concat_space, not_operator_with_successor_space, ordered_imports  
  @@ -7,9 +7,9 @@
   use Exception;
   use Illuminate\Support\Carbon;
   use Roster\Contracts\Validation\ValidationContextInterface;
  -use Roster\Validation\Attributes\ValidationRule;
   use Roster\Enums\EntityType;
   use Roster\Enums\OperationType;
  +use Roster\Validation\Attributes\ValidationRule;
   
   #[ValidationRule(
       priority: 60,
  @@ -33,7 +33,7 @@
       private function validateCreate(ValidationContextInterface $validationContext): void
       {
           // Pour CREATE : les deux datetime doivent être fournies
  -        if (!$validationContext->has('start_datetime') || !$validationContext->has('end_datetime')) {
  +        if (! $validationContext->has('start_datetime') || ! $validationContext->has('end_datetime')) {
               return; // Validation des champs requis gérée par une autre règle
           }
   
  @@ -50,7 +50,7 @@
           $hasEnd = $validationContext->has('end_datetime');
   
           // Si aucune des deux datetime n'est modifiée, on ne valide pas
  -        if (!$hasStart && !$hasEnd) {
  +        if (! $hasStart && ! $hasEnd) {
               return;
           }
   
  @@ -88,7 +88,7 @@
                   );
               }
           } catch (Exception $exception) {
  -            $validationContext->setViolation('datetime_format', "Invalid datetime format: " . $exception->getMessage());
  +            $validationContext->setViolation('datetime_format', 'Invalid datetime format: '.$exception->getMessage());
           }
       }
   }
  
  ⨯ src/Validation/Validator.php function_declaration, no_multiline_whitespace_around_double_arrow, concat_space, not_operator_with_successor_space, blank_line_before_statement, orde  
  @@ -4,14 +4,14 @@
   
   namespace Roster\Validation;
   
  -use Throwable;
   use ReflectionClass;
  -use Roster\Validation\Attributes\ValidationRule;
   use Roster\Contracts\Validation\RuleInterface;
   use Roster\Contracts\Validation\ValidationContextInterface;
   use Roster\Contracts\Validation\ValidatorInterface;
   use Roster\Enums\EntityType;
   use Roster\Enums\OperationType;
  +use Roster\Validation\Attributes\ValidationRule;
  +use Throwable;
   
   class Validator implements ValidatorInterface
   {
  @@ -30,7 +30,7 @@
       public function __construct(?RuleScanner $ruleScanner = null)
       {
           $this->ruleScanner = $ruleScanner ?? new RuleScanner([
  -            __DIR__ . '/Rules',
  +            __DIR__.'/Rules',
               // Ajoutez d'autres répertoires si nécessaire
           ]);
   
  @@ -63,8 +63,7 @@
           // Tri par priorité (plus haut = exécuté en premier)
           usort(
               $applicableRules,
  -            fn(RuleInterface $a, RuleInterface $b): int =>
  -            $b->getPriority() <=> $a->getPriority()
  +            fn (RuleInterface $a, RuleInterface $b): int => $b->getPriority() <=> $a->getPriority()
           );
   
           foreach ($applicableRules as $applicableRule) {
  @@ -72,12 +71,12 @@
                   $applicableRule->validate($validationContext);
               } catch (Throwable $e) {
   
  -                $validationContext->setViolation('_system', sprintf('Validation rule %s failed: ', $applicableRule->getName()) . $e->getMessage());
  +                $validationContext->setViolation('_system', sprintf('Validation rule %s failed: ', $applicableRule->getName()).$e->getMessage());
               }
           }
   
           return new ValidationResult(
  -            !$validationContext->hasViolations(),
  +            ! $validationContext->hasViolations(),
               $validationContext->getViolations()
           );
       }
  @@ -94,18 +93,18 @@
               $attribute = $attributes[0]->newInstance();
   
               foreach ($attribute->entities as $entity) {
  -                if (!$entity instanceof EntityType) {
  +                if (! $entity instanceof EntityType) {
                       continue;
                   }
   
                   foreach ($attribute->operations as $operation) {
  -                    if (!$operation instanceof OperationType) {
  +                    if (! $operation instanceof OperationType) {
                           continue;
                       }
   
                       $key = $this->getCacheKey($operation, $entity);
   
  -                    if (!isset($this->rulesByEntityOperation[$key])) {
  +                    if (! isset($this->rulesByEntityOperation[$key])) {
                           $this->rulesByEntityOperation[$key] = [];
                       }
   
  @@ -119,7 +118,7 @@
                       if ($rule->supports($operation, $entity)) {
                           $key = $this->getCacheKey($operation, $entity);
   
  -                        if (!isset($this->rulesByEntityOperation[$key])) {
  +                        if (! isset($this->rulesByEntityOperation[$key])) {
                               $this->rulesByEntityOperation[$key] = [];
                           }
   
  @@ -133,6 +132,7 @@
       public function getRulesFor(OperationType $operationType, EntityType $entityType): array
       {
           $key = $this->getCacheKey($operationType, $entityType);
  +
           return $this->rulesByEntityOperation[$key] ?? [];
       }
   
  @@ -143,7 +143,7 @@
   
       private function getCacheKey(OperationType $operationType, EntityType $entityType): string
       {
  -        return $operationType->value . ':' . $entityType->value;
  +        return $operationType->value.':'.$entityType->value;
       }
   
       /**
  @@ -184,7 +184,8 @@
       public function getRulesSortedByPriority(): array
       {
           $sortedRules = $this->allRules;
  -        usort($sortedRules, fn($a, $b): int => $b->getPriority() <=> $a->getPriority());
  +        usort($sortedRules, fn ($a, $b): int => $b->getPriority() <=> $a->getPriority());
  +
           return $sortedRules;
       }
   
  
  ⨯ src/helpers.php                 function_declaration, increment_style, concat_space, not_operator_with_successor_space, blank_line_before_statement, ordered_imports, phpdoc_align  
  @@ -5,15 +5,15 @@
    *
    * Collection of helper functions for the Roster package.
    */
  +use Carbon\Month;
   use Carbon\WeekDay;
  -use Carbon\Month;
   use Illuminate\Support\Carbon;
   
  -if (!function_exists('roster_day_of_week')) {
  +if (! function_exists('roster_day_of_week')) {
       /**
        * Retourne le jour de la semaine d'une date.
        *
  -     * @param string|DateTimeInterface $date Exemple : '2038-07-01' ou new DateTime('2038-07-01')
  +     * @param  string|DateTimeInterface  $date  Exemple : '2038-07-01' ou new DateTime('2038-07-01')
        * @return string|null 'monday', 'tuesday', ... ou null si date invalide
        */
       function roster_day_of_week($date): ?string
  @@ -28,12 +28,12 @@
       }
   }
   
  -if (!function_exists('roster_days_in_period')) {
  +if (! function_exists('roster_days_in_period')) {
       /**
        * Retourne tous les jours d'une période.
        *
  -     * @param string|DateTimeInterface|Carbon $startDate Date de début
  -     * @param string|DateTimeInterface|Carbon $endDate Date de fin
  +     * @param  string|DateTimeInterface|Carbon  $startDate  Date de début
  +     * @param  string|DateTimeInterface|Carbon  $endDate  Date de fin
        * @return array<string> Liste des jours (ex: ['monday', 'tuesday'])
        */
       function roster_days_in_period(DateTimeInterface|WeekDay|Month|string|int|float|null $startDate, DateTimeInterface|WeekDay|Month|string|int|float|null $endDate): array
  @@ -58,12 +58,12 @@
       }
   }
   
  -if (!function_exists('roster_format_period_days_for_display')) {
  +if (! function_exists('roster_format_period_days_for_display')) {
       /**
        * Formate les jours d'une période pour l'affichage.
        * Détecte les séquences continues et les formate comme "X to Y".
        *
  -     * @param array<string> $days Liste des jours (doit être triée)
  +     * @param  array<string>  $days  Liste des jours (doit être triée)
        * @return string Chaîne formatée (ex: "Thursday to Sunday" ou "Monday, Wednesday and Friday")
        */
       function roster_format_period_days_for_display(array $days): string
  @@ -85,9 +85,9 @@
   
           // Vérifier si c'est une séquence continue
           $isContinuous = true;
  -        $dayIndices = array_map(fn($day): false|int => array_search($day, $dayOrder, true), $days);
  +        $dayIndices = array_map(fn ($day): false|int => array_search($day, $dayOrder, true), $days);
   
  -        for ($i = 0; $i < count($dayIndices) - 1; ++$i) {
  +        for ($i = 0; $i < count($dayIndices) - 1; $i++) {
               $current = $dayIndices[$i];
               $next = $dayIndices[$i + 1];
   
  @@ -94,7 +94,7 @@
               // Deux jours sont consécutifs si :
               // 1. next = current + 1 (cas normal)
               // 2. OU current = 6 (dimanche) et next = 0 (lundi) - traverse le weekend
  -            if ($next !== $current + 1 && !($current === 6 && $next === 0)) {
  +            if ($next !== $current + 1 && ! ($current === 6 && $next === 0)) {
                   $isContinuous = false;
                   break;
               }
  @@ -121,11 +121,11 @@
       }
   }
   
  -if (!function_exists('roster_format_days_for_display')) {
  +if (! function_exists('roster_format_days_for_display')) {
       /**
        * Formate une liste de jours pour l'affichage.
        *
  -     * @param array<string> $days Liste des jours
  +     * @param  array<string>  $days  Liste des jours
        * @return string Chaîne formatée (ex: "Monday, Tuesday and Thursday")
        */
       function roster_format_days_for_display(array $days): string
  @@ -141,30 +141,31 @@
           }
   
           if (count($capitalized) === 2) {
  -            return $capitalized[0] . ' and ' . $capitalized[1];
  +            return $capitalized[0].' and '.$capitalized[1];
           }
   
           $last = array_pop($capitalized);
  -        return implode(', ', $capitalized) . ' and ' . $last;
  +
  +        return implode(', ', $capitalized).' and '.$last;
       }
   }
   
  -if (!function_exists('roster_period_duration_in_days')) {
  +if (! function_exists('roster_period_duration_in_days')) {
       /**
        * Calcule la durée d'une période en jours.
        *
  -     * @param string|DateTimeInterface $startDate Date de début
  -     * @param string|DateTimeInterface $endDate Date de fin
  +     * @param  string|DateTimeInterface  $startDate  Date de début
  +     * @param  string|DateTimeInterface  $endDate  Date de fin
        * @return int|null Nombre de jours ou null si dates invalides
        */
       function roster_period_duration_in_days($startDate, $endDate): ?int
       {
           try {
  -            if (!$startDate instanceof DateTimeInterface) {
  +            if (! $startDate instanceof DateTimeInterface) {
                   $startDate = new DateTime($startDate);
               }
   
  -            if (!$endDate instanceof DateTimeInterface) {
  +            if (! $endDate instanceof DateTimeInterface) {
                   $endDate = new DateTime($endDate);
               }
   
  @@ -176,28 +177,29 @@
       }
   }
   
  -if (!function_exists('roster_is_day_in_period')) {
  +if (! function_exists('roster_is_day_in_period')) {
       /**
        * Vérifie si un jour de la semaine est dans une période.
        *
  -     * @param string $day Jour à vérifier (ex: 'monday')
  -     * @param string|DateTimeInterface $startDate Date de début
  -     * @param string|DateTimeInterface $endDate Date de fin
  +     * @param  string  $day  Jour à vérifier (ex: 'monday')
  +     * @param  string|DateTimeInterface  $startDate  Date de début
  +     * @param  string|DateTimeInterface  $endDate  Date de fin
        */
       function roster_is_day_in_period(string $day, DateTimeInterface|WeekDay|Month|string|int|float|null $startDate, DateTimeInterface|WeekDay|Month|string|int|float|null $endDate): bool
       {
           $daysInPeriod = roster_days_in_period($startDate, $endDate);
  +
           return in_array($day, $daysInPeriod, true);
       }
   }
   
  -if (!function_exists('roster_get_valid_days_in_period')) {
  +if (! function_exists('roster_get_valid_days_in_period')) {
       /**
        * Filtre une liste de jours pour ne garder que ceux dans la période.
        *
  -     * @param array<string> $days Liste des jours à filtrer
  -     * @param string|DateTimeInterface $startDate Date de début
  -     * @param string|DateTimeInterface $endDate Date de fin
  +     * @param  array<string>  $days  Liste des jours à filtrer
  +     * @param  string|DateTimeInterface  $startDate  Date de début
  +     * @param  string|DateTimeInterface  $endDate  Date de fin
        * @return array<string> Jours filtrés
        */
       function roster_get_valid_days_in_period(array $days, DateTimeInterface|WeekDay|Month|string|int|float|null $startDate, DateTimeInterface|WeekDay|Month|string|int|float|null $endDate): array
  @@ -215,12 +217,12 @@
       }
   }
   
  -if (!function_exists('roster_should_auto_adjust_days')) {
  +if (! function_exists('roster_should_auto_adjust_days')) {
       /**
        * Détermine si les jours doivent être ajustés automatiquement.
        *
  -     * @param string|DateTimeInterface|null $startDate Date de début
  -     * @param string|DateTimeInterface|null $endDate Date de fin
  +     * @param  string|DateTimeInterface|null  $startDate  Date de début
  +     * @param  string|DateTimeInterface|null  $endDate  Date de fin
        */
       function roster_should_auto_adjust_days($startDate, $endDate): bool
       {
  @@ -231,6 +233,7 @@
   
           try {
               $duration = roster_period_duration_in_days($startDate, $endDate);
  +
               return $duration !== null && $duration < 7;
           } catch (Exception $exception) {
               return false;
  
  ⨯ tests/Feature/Services/AvailabilityServiceDaysCoherenceTest.php                                                                                                    ordered_imports  
  @@ -4,11 +4,11 @@
   
   namespace Tests\Feature\Services;
   
  +use Illuminate\Foundation\Testing\RefreshDatabase;
   use Roster\Enums\DaysOfWeek;
  +use Roster\Models\Availability;
   use Roster\Services\AvailabilityService;
   use Roster\Validation\Exceptions\ValidationFailedException;
  -use Illuminate\Foundation\Testing\RefreshDatabase;
  -use Roster\Models\Availability;
   use Tests\Support\TestSchedulable;
   use Tests\TestCase;
   
  
  ⨯ tests/Support/TestSchedulable.php                                                                                                                      class_attributes_separation  
  @@ -10,6 +10,7 @@
   class TestSchedulable extends Model
   {
       use HasRoster;
  +
       protected $table = 'test_schedulables';
   
       public $timestamps = false;
  
  ⨯ tests/TestCase.php                                                                                                                                   concat_space, ordered_imports  
  @@ -4,9 +4,9 @@
   
   namespace Tests;
   
  +use Illuminate\Support\Facades\Config;
   use Orchestra\Testbench\TestCase as OrchestraTestCase;
   use Roster\RosterServiceProvider;
  -use Illuminate\Support\Facades\Config;
   
   abstract class TestCase extends OrchestraTestCase
   {
  @@ -15,10 +15,10 @@
           parent::setUp();
   
           // Charger les migrations du package
  -        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
  +        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
   
           // Charger les migrations de test
  -        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
  +        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
   
           // Utiliser le cache en mémoire pour les tests
           Config::set('cache.default', 'array');
  
  ⨯ tests/Unit/HelpersTest.php                                                                                                                             class_attributes_separation  
  @@ -9,7 +9,6 @@
   
   final class HelpersTest extends TestCase
   {
  -
       public function test_roster_day_of_week(): void
       {
           $this->assertSame('thursday', roster_day_of_week('2038-07-01'));
  @@ -82,7 +81,6 @@
           $this->assertSame([], roster_days_in_period('invalid', '2038-07-01'));
       }
   
  -
       public function test_roster_format_days_for_display(): void
       {
           $this->assertSame('Monday', roster_format_days_for_display(['monday']));
  @@ -92,7 +90,6 @@
           $this->assertSame('', roster_format_days_for_display([]));
       }
   
  -
       public function test_roster_period_duration_in_days(): void
       {
           $this->assertSame(1, roster_period_duration_in_days('2038-07-01', '2038-07-01'));
  @@ -102,7 +99,6 @@
           $this->assertNull(roster_period_duration_in_days('invalid', '2038-07-01'));
       }
   
  -
       public function test_roster_is_day_in_period(): void
       {
           $this->assertTrue(roster_is_day_in_period('thursday', '2038-07-01', '2038-07-04'));
  @@ -119,7 +115,6 @@
           $this->assertFalse(roster_is_day_in_period('friday', '2038-07-01', '2038-07-01'));
       }
   
  -
       public function test_roster_get_valid_days_in_period(): void
       {
           $days = ['monday', 'thursday', 'friday', 'sunday'];
  @@ -145,7 +140,6 @@
           $expected = ['thursday', 'friday', 'saturday', 'sunday'];
           $this->assertSame($expected, $validDays);
       }
  -
   
       public function test_roster_should_auto_adjust_days(): void
       {
  
  ⨯ tests/Unit/Models/ScheduleTest.php                                                                            class_attributes_separation, no_unused_imports, no_extra_blank_lines  
  @@ -4,25 +4,20 @@
   
   namespace Unit\Models;
   
  -use Illuminate\Support\Carbon;
  -use Rector\Carbon\NodeFactory\CarbonCallFactory;
  -use Roster\Models\Availability;
  -use Tests\Support\TestSchedulable;
   use Tests\TestCase;
   
   final class ScheduleTest extends TestCase
   {
  -
       protected function setUp(): void
       {
           parent::setUp();
       }
  +
       /**
        * Test basic data validation with valid input.
        */
       public function test_validate_basic_data_with_valid_data(): void
       {
  -
   
           // Arrange
   
  
  ⨯ tests/Unit/Services/AvailabilityServiceTest.php                                                                   class_attributes_separation, method_argument_space, single_quote  
  @@ -166,7 +166,6 @@
           $this->availabilityService->update($availabilityId, $updateData);
       }
   
  -
       public function test_can_delete_an_availability(): void
       {
           // Arrange - Créer une disponibilité
  @@ -206,7 +205,6 @@
           $this->availabilityService->delete($availabilityId);
       }
   
  -
       public function test_can_find_an_availability_by_id(): void
       {
           // Arrange - Créer une disponibilité
  @@ -485,7 +483,7 @@
   
           // Expect exception
           $this->expectException(ValidationFailedException::class);
  -        $this->expectExceptionMessageMatches("/cannot be changed/");
  +        $this->expectExceptionMessageMatches('/cannot be changed/');
   
           // Act
           $this->availabilityService->update($availability->id, $updateData);
  @@ -635,7 +633,6 @@
           ]);
       }
   
  -
       public function test_validate_invalid_day_value(): void
       {
           // Arrange - Jour invalide
  @@ -658,13 +655,13 @@
   
       public function test_validate_invalid_type(): void
       {
  -        config()->set('roster-validation.availability_types',  [
  +        config()->set('roster-validation.availability_types', [
               'consultation',
               'training',
               'coaching',
               'meeting',
               'support',
  -        ],);
  +        ], );
   
           // Arrange - Type invalide
           $availabilityData = [
  
  ⨯ tests/Unit/Services/ImpedimentServiceTest.php                                                                   class_attributes_separation, no_extra_blank_lines, ordered_imports  
  @@ -4,14 +4,14 @@
   
   namespace Tests\Feature\Services;
   
  -use Roster\Contracts\Validation\ValidatorInterface;
  +use Illuminate\Support\Carbon;
   use Illuminate\Support\Collection;
  -use Illuminate\Support\Carbon;
   use Illuminate\Support\Facades\App;
   use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
   use Roster\Contracts\Repository\ImpedimentRepositoryInterface;
   use Roster\Contracts\Repository\ScheduleRepositoryInterface;
   use Roster\Contracts\Services\SlotFinderInterface;
  +use Roster\Contracts\Validation\ValidatorInterface;
   use Roster\Enums\ScheduleStatus;
   use Roster\Models\Availability;
   use Roster\Models\Impediment;
  @@ -19,8 +19,8 @@
   use Roster\Services\ImpedimentService;
   use Roster\Services\ScheduleService;
   use Roster\Validation\Exceptions\ValidationFailedException;
  +use Tests\Support\TestSchedulable;
   use Tests\TestCase;
  -use Tests\Support\TestSchedulable;
   
   final class ImpedimentServiceTest extends TestCase
   {
  @@ -188,6 +188,7 @@
           $this->assertEquals(Carbon::parse('2038-01-04 13:00:00'), $impediment->start_datetime);
           $this->assertEquals(Carbon::parse('2038-01-04 15:00:00'), $impediment->end_datetime);
       }
  +
       public function test_update_impediment_throws_exception_when_not_found(): void
       {
           // Assert
  @@ -210,7 +211,6 @@
               'end_datetime' => '2038-01-04 12:00:00',
           ]);
   
  -
           // Act
           $result = $this->impedimentService->for($this->testSchedulable)->delete($impediment->id);
   
  @@ -687,7 +687,6 @@
               'end_datetime' => '2038-01-04 13:00:00',
               'status' => ScheduleStatus::BOOKED,
           ];
  -
   
           $this->scheduleService->for($this->testSchedulable)->create($this->testAvailability, $scheduleData);
   
  
  ⨯ tests/Unit/Services/ScheduleServiceTest.php                                                  class_attributes_separation, no_unused_imports, no_extra_blank_lines, ordered_imports  
  @@ -7,13 +7,6 @@
   use Illuminate\Support\Carbon;
   use Illuminate\Support\Collection;
   use Illuminate\Support\Facades\Config;
  -use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
  -use Roster\Contracts\Repository\ImpedimentRepositoryInterface;
  -use Roster\Contracts\Repository\ScheduleRepositoryInterface;
  -use Roster\Contracts\Validation\ValidatorInterface;
  -use Roster\DTOs\ScheduleData;
  -use Roster\Enums\EntityType;
  -use Roster\Enums\OperationType;
   use Roster\Enums\ScheduleStatus;
   use Roster\Models\Availability;
   use Roster\Models\Impediment;
  @@ -20,14 +13,17 @@
   use Roster\Models\Schedule;
   use Roster\Services\ScheduleService;
   use Roster\Validation\Exceptions\ValidationFailedException;
  +use Tests\Support\TestSchedulable;
   use Tests\TestCase;
  -use Tests\Support\TestSchedulable;
   
   final class ScheduleServiceTest extends TestCase
   {
       private ScheduleService $scheduleService;
  +
       private TestSchedulable $testSchedulable;
  +
       private Availability $testAvailability;
  +
       private array $baseScheduleData;
   
       protected function setUp(): void
  @@ -233,7 +229,6 @@
               'end_datetime' => '2038-01-04 11:00:00',
           ]);
   
  -
           $schedule2 = $this->scheduleService->create($this->testAvailability, [
               'title' => 'Schedule 2',
               'start_datetime' => '2038-01-04 12:00:00',
  @@ -240,7 +235,6 @@
               'end_datetime' => '2038-01-04 13:00:00',
           ]);
   
  -
           // Essayer de déplacer schedule1 pour qu'il chevauche schedule2
           $updateData = [
               'start_datetime' => '2038-01-04 12:30:00', // Chevauche avec schedule2
  @@ -247,12 +241,10 @@
               'end_datetime' => '2038-01-04 13:30:00',
           ];
   
  -
           // Expect
           $this->expectException(ValidationFailedException::class);
           $this->expectExceptionMessageMatches('/Schedule overlaps with an existing schedule/');
   
  -
           // Act
           $this->scheduleService->update($schedule1->id, $updateData);
       }
  @@ -505,8 +497,6 @@
               'validity_start' => '2038-01-01',
               'validity_end' => '2038-01-10',
           ]);
  -
  -
   
           // Act - Chercher un créneau le mardi
           $slot = $this->scheduleService->for($this->testSchedulable)->findNextSlot(
  
  ⨯ tests/Unit/Validation/Rules/AvailabilityDaysCoherenceRuleTest.php                                                                            new_with_parentheses, ordered_imports  
  @@ -4,10 +4,10 @@
   
   namespace Tests\Unit\Validation\Rules;
   
  -use Roster\Validation\Rules\AvailabilityDaysCoherenceRule;
   use Roster\Contracts\Validation\ValidationContextInterface;
   use Roster\Enums\EntityType;
   use Roster\Enums\OperationType;
  +use Roster\Validation\Rules\AvailabilityDaysCoherenceRule;
   use Tests\TestCase;
   
   final class AvailabilityDaysCoherenceRuleTest extends TestCase
  @@ -17,7 +17,7 @@
       protected function setUp(): void
       {
           parent::setUp();
  -        $this->availabilityDaysCoherenceRule = new AvailabilityDaysCoherenceRule();
  +        $this->availabilityDaysCoherenceRule = new AvailabilityDaysCoherenceRule;
       }
   
       public function test_passes_when_days_provided_and_within_period(): void
  
  ⨯ tests/Unit/Validation/Rules/AvailabilityRulesTest.php                                             class_attributes_separation, new_with_parentheses, single_quote, ordered_imports  
  @@ -7,8 +7,8 @@
   use Roster\Enums\EntityType;
   use Roster\Enums\OperationType;
   use Roster\Validation\Context\ValidationContext;
  +use Roster\Validation\Rules\AvailabilityOverlapRule;
   use Roster\Validation\Rules\RequiredFieldsRule;
  -use Roster\Validation\Rules\AvailabilityOverlapRule;
   use Tests\Support\TestSchedulable;
   use Tests\TestCase;
   
  @@ -24,12 +24,11 @@
       {
           parent::setUp();
   
  -        $this->requiredFieldsRule = new RequiredFieldsRule();
  -        $this->availabilityOverlapRule = new AvailabilityOverlapRule();
  +        $this->requiredFieldsRule = new RequiredFieldsRule;
  +        $this->availabilityOverlapRule = new AvailabilityOverlapRule;
           $this->testSchedulable = TestSchedulable::create();
       }
   
  -
       public function test_required_fields_rule_valid_for_availability_create(): void
       {
           $data = [
  @@ -173,7 +172,7 @@
       public function test_required_fields_for_schedule_create(): void
       {
           $data = [
  -            'title' => "Title de la schedule",
  +            'title' => 'Title de la schedule',
               'start_datetime' => '2038-07-01 10:00:00',
               'end_datetime' => '2038-07-01 11:00:00',
           ];
  
  ⨯ tests/Unit/Validation/Rules/DateRangeRulesTest.php                                                                                           new_with_parentheses, ordered_imports  
  @@ -4,7 +4,6 @@
   
   namespace Tests\Unit\Validation\Rules;
   
  -use stdClass;
   use Roster\Enums\EntityType;
   use Roster\Enums\OperationType;
   use Roster\Models\Availability;
  @@ -11,6 +10,7 @@
   use Roster\Validation\Context\ValidationContext;
   use Roster\Validation\Rules\AvailabilityDateRangeRule;
   use Roster\Validation\Rules\TimeSlotDateTimeRule;
  +use stdClass;
   use Tests\Support\TestSchedulable;
   use Tests\TestCase;
   
  @@ -26,8 +26,8 @@
       {
           parent::setUp();
   
  -        $this->availabilityDateRangeRule = new AvailabilityDateRangeRule();
  -        $this->timeSlotDateTimeRule = new TimeSlotDateTimeRule();
  +        $this->availabilityDateRangeRule = new AvailabilityDateRangeRule;
  +        $this->timeSlotDateTimeRule = new TimeSlotDateTimeRule;
           $this->testSchedulable = TestSchedulable::create();
       }
   
  @@ -282,7 +282,7 @@
       public function test_schedule_validate_update_partial(): void
       {
           // Simuler un schedule existant
  -        $schedule = new stdClass();
  +        $schedule = new stdClass;
           $schedule->start_datetime = '2038-07-01 10:00:00';
           $schedule->end_datetime = '2038-07-01 11:00:00';
   
  @@ -307,7 +307,7 @@
       public function test_schedule_validate_update_partial_fails(): void
       {
           // Simuler un schedule existant
  -        $schedule = new stdClass();
  +        $schedule = new stdClass;
           $schedule->start_datetime = '2038-07-01 10:00:00';
           $schedule->end_datetime = '2038-07-01 11:00:00';
   
  @@ -333,7 +333,7 @@
       public function test_schedule_validate_update_skip_when_no_datetimes_changed(): void
       {
           // Simuler un schedule existant
  -        $schedule = new stdClass();
  +        $schedule = new stdClass;
           $schedule->start_datetime = '2038-07-01 10:00:00';
           $schedule->end_datetime = '2038-07-01 11:00:00';
   
  
  ⨯ tests/bootstrap.php                                                   phpdoc_no_package, concat_space, phpdoc_trim, not_operator_with_successor_space, no_blank_lines_after_phpdoc  
  @@ -2,7 +2,7 @@
   
   declare(strict_types=1);
   
  -require __DIR__ . '/../vendor/autoload.php';
  +require __DIR__.'/../vendor/autoload.php';
   
   /**
    * Bootstrap file for testing environment.
  @@ -10,10 +10,7 @@
    * This file is responsible for:
    *  - Loading Composer autoload
    *  - Defining constants needed for tests
  - *
  - * @package Roster\Tests
    */
  -
  -if (!defined('TESTING')) {
  +if (! defined('TESTING')) {
       define('TESTING', true);
   }
  

