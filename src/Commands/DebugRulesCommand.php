<?php

declare(strict_types=1);

namespace Roster\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Validation\RuleScanner;
use Roster\Validation\Validator;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use Symfony\Component\Console\Helper\Table;
use Throwable;

/**
 * Command for debugging validation rules in the Roster package.
 *
 * Displays detailed information about validation rules detected for entities,
 * their properties, and validation logic sources.
 */
class DebugRulesCommand extends Command
{
    /**
     * The command signature with available options.
     *
     * @var string
     */
    protected $signature = 'roster:debug-rules
                            {entity? : Entity class name or entity type (availability, schedule, impediment)}
                            {--operation= : Filter by operation type (create, update, delete)}
                            {--property= : Filter by specific property name}
                            {--show-methods : Display validation method details}
                            {--show-source : Display rule source code location}
                            {--details : Show all details including rule priorities and dependencies}';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Debug Roster validation rules for entities';

    /**
     * Execute the console command.
     *
     * @param RuleScanner $ruleScanner The rule scanner service
     * @param Validator $validator The validator instance
     * @return int Command exit code (SUCCESS or FAILURE)
     */
    public function handle(RuleScanner $ruleScanner, Validator $validator): int
    {
        try {
            $entityInput = $this->argument('entity');
            $operationFilter = $this->option('operation');
            $propertyFilter = $this->option('property');

            if ($entityInput) {
                $this->debugSpecificEntity(
                    entityInput: $entityInput,
                    operationFilter: $operationFilter,
                    propertyFilter: $propertyFilter,
                    ruleScanner: $ruleScanner,
                    validator: $validator
                );
            } else {
                $this->debugAllRules(
                    ruleScanner: $ruleScanner,
                    validator: $validator
                );
            }

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            if ($this->option('verbose')) {
                $this->error($exception->getTraceAsString());
            }

            return self::FAILURE;
        }
    }

    /**
     * Debug rules for a specific entity.
     *
     * @param string $entityInput Entity identifier input
     * @param string|null $operationFilter Operation type filter
     * @param string|null $propertyFilter Property name filter
     * @param RuleScanner $ruleScanner Rule scanner service
     * @param Validator $validator Validator instance
     */
    private function debugSpecificEntity(
        string $entityInput,
        ?string $operationFilter,
        ?string $propertyFilter,
        RuleScanner $ruleScanner,
        Validator $validator
    ): void {
        $entityType = $this->resolveEntityType($entityInput);

        $this->line("🔍 Debugging validation rules for: {$entityInput}");
        $this->line("📊 Entity Type: " . $entityType->value);
        $this->newLine();

        $rules = $this->getRulesForEntity(
            entityType: $entityType,
            operationFilter: $operationFilter,
            validator: $validator
        );

        if (empty($rules)) {
            $this->warn('No validation rules found for this entity/operation combination.');
            return;
        }

        $this->displayRulesTable(
            rules: $rules,
            propertyFilter: $propertyFilter,
            entityType: $entityType,
            operationFilter: $operationFilter
        );

        if ($this->option('show-methods')) {
            $this->displayValidationMethods($rules);
        }

        if ($this->option('show-source')) {
            $this->displayRuleSources($rules);
        }
    }

    /**
     * Debug all rules in the system.
     *
     * @param RuleScanner $ruleScanner Rule scanner service
     * @param Validator $validator Validator instance
     */
    private function debugAllRules(RuleScanner $ruleScanner, Validator $validator): void
    {
        $this->line('📋 All validation rules in Roster system');
        $this->newLine();

        $scannedRules = $ruleScanner->scan();
        $instantiatedRules = $ruleScanner->instantiateRules();

        $this->line("📊 Scanner detected: " . count($scannedRules) . " rules");
        $this->line("⚡ Instantiated: " . count($instantiatedRules) . " rules");
        $this->newLine();

        $this->displayEntitySummary($validator);

        if ($this->option('verbose')) {
            $this->displayScannedRulesTable($scannedRules);
        }
    }

    /**
     * Resolve entity type from input.
     *
     * @param string $input Entity identifier input
     * @return EntityType Resolved entity type
     */
    private function resolveEntityType(string $input): EntityType
    {
        try {
            return EntityType::from(strtolower($input));
        } catch (\ValueError) {
            $this->warn("Entity '{$input}' not found in EntityType enum. Using AVAILABILITY as default.");
            return EntityType::AVAILABILITY;
        }
    }

    /**
     * Get rules for a specific entity and optional operation.
     * NOTE: Only supports CREATE, UPDATE, DELETE operations (not RETRIEVE)
     *
     * @param EntityType $entityType Entity type to get rules for
     * @param string|null $operationFilter Operation type filter
     * @param Validator $validator Validator instance
     * @return array Collection of validation rules
     */
    private function getRulesForEntity(
        EntityType $entityType,
        ?string $operationFilter,
        Validator $validator
    ): array {
        $rules = [];

        // CORRECTION ICI : utiliser seulement CREATE, UPDATE, DELETE (pas RETRIEVE)
        $supportedOperations = [
            OperationType::CREATE,
            OperationType::UPDATE,
            OperationType::DELETE
        ];

        $operations = $operationFilter
            ? [$this->resolveOperationType($operationFilter)]
            : $supportedOperations;

        foreach ($operations as $operation) {
            if (!$operation instanceof OperationType) {
                continue;
            }

            // Skip RETRIEVE operation as validation rules don't apply to read operations
            if ($operation === OperationType::RETRIEVE) {
                continue;
            }

            $entityRules = $validator->getRulesFor($operation, $entityType);

            foreach ($entityRules as $rule) {
                $rules[] = [
                    'rule' => $rule,
                    'operation' => $operation,
                    'entity' => $entityType,
                ];
            }
        }

        return $rules;
    }

    /**
     * Resolve operation type from string.
     *
     * @param string $operation Operation string identifier
     * @return OperationType Resolved operation type
     */
    private function resolveOperationType(string $operation): OperationType
    {
        return OperationType::tryFrom(strtolower($operation)) ?? OperationType::CREATE;
    }

    /**
     * Display rules in a formatted table.
     *
     * @param array $rules Rules to display
     * @param string|null $propertyFilter Property name filter
     * @param EntityType $entityType Entity type
     * @param string|null $operationFilter Operation type filter
     */
    private function displayRulesTable(
        array $rules,
        ?string $propertyFilter,
        EntityType $entityType,
        ?string $operationFilter
    ): void {
        $this->line("📋 Rules for {$entityType->value}" .
            ($operationFilter ? " (Operation: {$operationFilter})" : ""));
        $this->newLine();

        $groupedRules = $this->groupRulesByClassName($rules);
        $sortedRules = $this->sortRulesByPriority($groupedRules);
        $filteredRules = $this->filterRulesByProperty($sortedRules, $propertyFilter);

        if (empty($filteredRules)) {
            $this->warn("No rules match the specified filters.");
            return;
        }

        $this->renderRulesTable($filteredRules);
    }

    /**
     * Group rules by their class name.
     *
     * @param array $rules Rules to group
     * @return array Grouped rules by class name
     */
    private function groupRulesByClassName(array $rules): array
    {
        $groupedRules = [];

        foreach ($rules as $ruleData) {
            $rule = $ruleData['rule'];
            $operation = $ruleData['operation'];
            $className = get_class($rule);

            if (!isset($groupedRules[$className])) {
                $groupedRules[$className] = [
                    'rule' => $rule,
                    'operations' => [],
                    'entity' => $ruleData['entity'],
                ];
            }

            $groupedRules[$className]['operations'][] = $operation->value;
        }

        return $groupedRules;
    }

    /**
     * Sort rules by priority in descending order.
     *
     * @param array $groupedRules Grouped rules to sort
     * @return array Sorted rules
     */
    private function sortRulesByPriority(array $groupedRules): array
    {
        uasort($groupedRules, function (array $firstRuleData, array $secondRuleData): int {
            return $secondRuleData['rule']->getPriority() <=> $firstRuleData['rule']->getPriority();
        });

        return $groupedRules;
    }

    /**
     * Filter rules by property name.
     *
     * @param array $groupedRules Grouped rules to filter
     * @param string|null $propertyFilter Property name filter
     * @return array Filtered rules
     */
    private function filterRulesByProperty(array $groupedRules, ?string $propertyFilter): array
    {
        if (!$propertyFilter) {
            return $groupedRules;
        }

        $filteredRules = [];

        foreach ($groupedRules as $className => $ruleData) {
            $properties = $this->extractRuleProperties($ruleData['rule']);

            if (in_array($propertyFilter, $properties)) {
                $filteredRules[$className] = $ruleData;
            }
        }

        return $filteredRules;
    }

    /**
     * Render rules table.
     *
     * @param array $groupedRules Grouped rules to display
     */
    private function renderRulesTable(array $groupedRules): void
    {
        $table = new Table($this->output);
        $table->setHeaders([
            '#',
            'Rule Name',
            'Priority',
            'Properties',
            'Operations',
            'Source',
        ]);

        $rows = $this->buildRulesTableRows($groupedRules);
        $table->setRows($rows);
        $table->render();
    }

    /**
     * Build table rows for rules display.
     *
     * @param array $groupedRules Grouped rules
     * @return array Table rows
     */
    private function buildRulesTableRows(array $groupedRules): array
    {
        $rows = [];
        $index = 1;

        foreach ($groupedRules as $ruleData) {
            $rule = $ruleData['rule'];
            $operations = $this->getUniqueSortedOperations($ruleData['operations']);

            $rows[] = [
                $index++,
                $rule->getName(),
                $rule->getPriority(),
                $this->formatRuleProperties($rule),
                implode(', ', $operations),
                $this->getRuleSource($rule),
            ];
        }

        return $rows;
    }

    /**
     * Get unique sorted operations from operation list.
     *
     * @param array $operations Operation values
     * @return array Unique sorted operations
     */
    private function getUniqueSortedOperations(array $operations): array
    {
        $uniqueOperations = array_unique($operations);
        sort($uniqueOperations);
        return $uniqueOperations;
    }

    /**
     * Format rule properties for display.
     *
     * @param object $rule Validation rule object
     * @return string Formatted properties string
     */
    private function formatRuleProperties(object $rule): string
    {
        $properties = $this->extractRuleProperties($rule);
        return !empty($properties) ? implode(', ', $properties) : '(class-level)';
    }

    /**
     * Extract properties validated by a rule.
     *
     * @param object $rule Validation rule object
     * @return array Extracted property names
     */
    private function extractRuleProperties(object $rule): array
    {
        if (method_exists($rule, 'getProperties')) {
            return $rule->getProperties();
        }

        try {
            $reflection = new ReflectionClass($rule);
            $properties = [];

            foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                if ($this->isValidationMethod($method->getName())) {
                    $properties = array_merge(
                        $properties,
                        $this->extractPropertiesFromMethod($method)
                    );
                }
            }

            return array_unique($properties);
        } catch (ReflectionException $exception) {
            $this->warn("Could not analyze properties for rule: " . get_class($rule));
            return [];
        }
    }

    /**
     * Check if a method name indicates a validation method.
     *
     * @param string $methodName Method name to check
     * @return bool True if method is a validation method
     */
    private function isValidationMethod(string $methodName): bool
    {
        return str_starts_with($methodName, 'validate') || str_starts_with($methodName, 'check');
    }

    /**
     * Extract properties from a method.
     *
     * @param ReflectionMethod $method Method to analyze
     * @return array Extracted property names
     */
    private function extractPropertiesFromMethod(ReflectionMethod $method): array
    {
        return array_unique(array_merge(
            $this->extractPropertiesFromDocComment($method),
            $this->extractPropertiesFromMethodBody($method)
        ));
    }

    /**
     * Extract properties from method doc comment.
     *
     * @param ReflectionMethod $method Method with doc comment
     * @return array Extracted property names
     */
    private function extractPropertiesFromDocComment(ReflectionMethod $method): array
    {
        $docComment = $method->getDocComment();
        if (!$docComment) {
            return [];
        }

        if (preg_match_all('/@param\s+\S+\s+\$(\w+)/', $docComment, $matches)) {
            return $matches[1] ?? [];
        }

        return [];
    }

    /**
     * Extract properties from method body.
     *
     * @param ReflectionMethod $method Method to analyze
     * @return array Extracted property names
     */
    private function extractPropertiesFromMethodBody(ReflectionMethod $method): array
    {
        try {
            $methodSource = file($method->getFileName());
            if ($methodSource === false) {
                return [];
            }

            $properties = [];
            $startLine = max(0, $method->getStartLine() - 1);
            $endLine = $method->getEndLine();

            for ($i = $startLine; $i < $endLine && $i < count($methodSource); $i++) {
                $line = $methodSource[$i];
                $properties = array_merge(
                    $properties,
                    $this->extractPropertiesFromLine($line)
                );
            }

            return $properties;
        } catch (\Exception) {
            return [];
        }
    }

    /**
     * Extract properties from a single line of code.
     *
     * @param string $line Line of source code
     * @return array Extracted property names
     */
    private function extractPropertiesFromLine(string $line): array
    {
        $properties = [];

        if (preg_match('/->(get|has)\(\s*[\'"]([^\'"]+)[\'"]\s*\)/', $line, $matches)) {
            $properties[] = $matches[2];
        }

        if (preg_match('/->setViolation\(\s*[\'"]([^\'"]+)[\'"]/', $line, $matches)) {
            $properties[] = $matches[1];
        }

        return $properties;
    }

    /**
     * Get rule source information.
     *
     * @param object $rule Validation rule object
     * @return string Source information
     */
    private function getRuleSource(object $rule): string
    {
        try {
            $reflection = new ReflectionClass($rule);

            if (!empty($reflection->getAttributes(\Roster\Validation\Attributes\ValidationRule::class))) {
                return 'Attribute';
            }

            $cacheFile = config('roster.cache.cache_file');

            // CORRECTION ICI : vérifier si $cacheFile n'est pas null
            if ($cacheFile && file_exists($cacheFile)) {
                $cachedRules = require $cacheFile;
                if (isset($cachedRules[get_class($rule)])) {
                    return 'Cache';
                }
            }

            return 'Scanner';
        } catch (ReflectionException) {
            return 'Unknown';
        }
    }

    /**
     * Display validation methods for rules.
     *
     * @param array $rules Rules to analyze
     */
    private function displayValidationMethods(array $rules): void
    {
        $this->newLine();
        $this->line('🔧 Validation Methods Analysis');
        $this->newLine();

        $groupedRules = $this->groupRulesByClassName($rules);
        $sortedRules = $this->sortRulesByPriority($groupedRules);

        foreach ($sortedRules as $ruleData) {
            $this->displayRuleMethodDetails(
                rule: $ruleData['rule'],
                operations: $this->getUniqueSortedOperations($ruleData['operations'])
            );
        }
    }

    /**
     * Display validation method details for a rule.
     *
     * @param object $rule Rule to analyze
     * @param array $operations Operations array
     */
    private function displayRuleMethodDetails(object $rule, array $operations): void
    {
        $this->line("Rule: " . $rule->getName() . " (Priority: {$rule->getPriority()})");
        $this->line("Operations: " . implode(', ', $operations));
        $this->line("Class: " . get_class($rule));

        try {
            $reflection = new ReflectionClass($rule);
            foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                if ($method->getName() === 'validate') {
                    $this->displayMethodInfo($method);
                }
            }
        } catch (ReflectionException $exception) {
            $this->warn("    Could not analyze methods for: " . get_class($rule));
        }

        $this->newLine();
    }

    /**
     * Display information about a single method.
     *
     * @param ReflectionMethod $method Method to display
     */
    private function displayMethodInfo(ReflectionMethod $method): void
    {
        $this->line("  📝 Method: {$method->getName()}()");
        $this->line("    📍 File: {$method->getFileName()}:{$method->getStartLine()}");

        $parameters = $this->extractMethodParameters($method);
        if (!empty($parameters)) {
            $this->line("    🔧 Params: " . implode(', ', $parameters));
        }
    }

    /**
     * Extract method parameters as formatted strings.
     *
     * @param ReflectionMethod $method Method to analyze
     * @return array Formatted parameter strings
     */
    private function extractMethodParameters(ReflectionMethod $method): array
    {
        $parameters = [];

        foreach ($method->getParameters() as $parameter) {
            $type = $parameter->getType() ? $parameter->getType()->getName() : 'mixed';
            $parameters[] = "{$type} \${$parameter->getName()}";
        }

        return $parameters;
    }

    /**
     * Display rule source code locations.
     *
     * @param array $rules Rules to display sources for
     */
    private function displayRuleSources(array $rules): void
    {
        $this->newLine();
        $this->line('📁 Rule Source Locations');
        $this->newLine();

        $groupedRules = $this->groupRulesByClassName($rules);
        $sortedRules = $this->sortRulesByPriority($groupedRules);
        $rows = $this->buildRuleSourcesTableRows($sortedRules);

        $table = new Table($this->output);
        $table->setHeaders(['#', 'Rule Name', 'Priority', 'Operations', 'Source File', 'Line']);
        $table->setRows($rows);
        $table->render();
    }

    /**
     * Build table rows for rule sources display.
     *
     * @param array $groupedRules Grouped rules
     * @return array Table rows
     */
    private function buildRuleSourcesTableRows(array $groupedRules): array
    {
        $rows = [];
        $index = 1;

        foreach ($groupedRules as $ruleData) {
            $rows[] = $this->createRuleSourceRow(
                rule: $ruleData['rule'],
                operations: $this->getUniqueSortedOperations($ruleData['operations']),
                index: $index++
            );
        }

        return $rows;
    }

    /**
     * Create a row for the rule sources table.
     *
     * @param object $rule Rule object
     * @param array $operations Operations array
     * @param int $index Row index
     * @return array Table row data
     */
    private function createRuleSourceRow(object $rule, array $operations, int $index): array
    {
        try {
            $reflection = new ReflectionClass($rule);
            return [
                $index,
                $rule->getName(),
                $rule->getPriority(),
                implode(', ', $operations),
                $reflection->getFileName(),
                $reflection->getStartLine(),
            ];
        } catch (ReflectionException) {
            return [
                $index,
                $rule->getName(),
                $rule->getPriority(),
                implode(', ', $operations),
                'Unknown',
                'N/A',
            ];
        }
    }

    /**
     * Display summary of rules by entity.
     *
     * @param Validator $validator Validator instance
     */
    private function displayEntitySummary(Validator $validator): void
    {
        $this->line('📊 Rules by Entity Type');
        $this->newLine();

        $summaryData = $this->collectEntitySummaryData($validator);

        $table = new Table($this->output);
        $table->setHeaders(['#', 'Entity Type', 'Create', 'Update', 'Delete', 'Total']);

        foreach ($summaryData as $index => $data) {
            $table->addRow([
                $index + 1,
                $data['entity']->value,
                $data['create'],
                $data['update'],
                $data['delete'],
                $data['total'],
            ]);
        }

        $table->render();
        $this->newLine();
    }

    /**
     * Collect summary data for all entity types.
     * NOTE: Only counts CREATE, UPDATE, DELETE operations (not RETRIEVE)
     *
     * @param Validator $validator Validator instance
     * @return array Summary data
     */
    private function collectEntitySummaryData(Validator $validator): array
    {
        $summaryData = [];

        foreach (EntityType::cases() as $entityType) {
            $createCount = count($validator->getRulesFor(OperationType::CREATE, $entityType));
            $updateCount = count($validator->getRulesFor(OperationType::UPDATE, $entityType));
            $deleteCount = count($validator->getRulesFor(OperationType::DELETE, $entityType));

            $summaryData[] = [
                'entity' => $entityType,
                'create' => $createCount,
                'update' => $updateCount,
                'delete' => $deleteCount,
                'total' => $createCount + $updateCount + $deleteCount,
            ];
        }

        usort($summaryData, function (array $firstData, array $secondData): int {
            return $secondData['total'] <=> $firstData['total'];
        });

        return $summaryData;
    }

    /**
     * Display all scanned rules.
     *
     * @param array $scannedRules Scanned rules data
     */
    private function displayScannedRulesTable(array $scannedRules): void
    {
        $this->newLine();
        $this->line('🔍 All Scanned Rules (from cache/scanner)');
        $this->newLine();

        $sortedRules = $this->sortScannedRulesByPriority($scannedRules);
        $rows = $this->buildScannedRulesTableRows($sortedRules);

        $table = new Table($this->output);
        $table->setHeaders(['#', 'Class', 'Priority', 'Entities', 'Operations', 'Cached']);
        $table->setRows($rows);
        $table->render();
    }

    /**
     * Sort scanned rules by priority in descending order.
     *
     * @param array $scannedRules Scanned rules to sort
     * @return array Sorted rules
     */
    private function sortScannedRulesByPriority(array $scannedRules): array
    {
        uasort($scannedRules, function (object $firstRuleData, object $secondRuleData): int {
            $firstPriority = $firstRuleData->priority ?? 0;
            $secondPriority = $secondRuleData->priority ?? 0;
            return $secondPriority <=> $firstPriority;
        });

        return $scannedRules;
    }

    /**
     * Build table rows for scanned rules display.
     *
     * @param array $sortedRules Sorted rules data
     * @return array Table rows
     */
    private function buildScannedRulesTableRows(array $sortedRules): array
    {
        $rows = [];
        $index = 1;

        foreach ($sortedRules as $className => $ruleData) {
            $rows[] = $this->createScannedRuleRow(
                className: $className,
                ruleData: $ruleData,
                index: $index++
            );
        }

        return $rows;
    }

    /**
     * Create a row for the scanned rules table.
     *
     * @param string $className Rule class name
     * @param object $ruleData Rule data object
     * @param int $index Row index
     * @return array Table row data
     */
    private function createScannedRuleRow(string $className, object $ruleData, int $index): array
    {
        $operations = $this->getUniqueOperationsFromScannedRule($ruleData);
        $entities = $this->getEntityValuesFromScannedRule($ruleData);

        $cacheFile = config('roster.cache.cache_file');

        return [
            $index,
            $className,
            $ruleData->priority ?? 'N/A',
            implode(', ', $entities),
            implode(', ', $operations),
            $cacheFile && file_exists($cacheFile) ? 'Yes' : 'No', // CORRECTION ICI
        ];
    }

    /**
     * Get unique operations from scanned rule data.
     *
     * @param object $ruleData Scanned rule data
     * @return array Unique sorted operations
     */
    private function getUniqueOperationsFromScannedRule(object $ruleData): array
    {
        $operations = array_unique(array_map(
            fn(object $operation): string => $operation->value,
            $ruleData->operations ?? []
        ));
        sort($operations);
        return $operations;
    }

    /**
     * Get entity values from scanned rule data.
     *
     * @param object $ruleData Scanned rule data
     * @return array Entity values
     */
    private function getEntityValuesFromScannedRule(object $ruleData): array
    {
        return array_map(
            fn(object $entity): string => $entity->value,
            $ruleData->entities ?? []
        );
    }
}
