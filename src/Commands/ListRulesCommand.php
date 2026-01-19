<?php

declare(strict_types=1);

namespace Roster\Commands;

use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Validation\RuleScanner;
use Roster\Validation\Validator;
use Roster\Contracts\Validation\RuleInterface;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use ValueError;
use Throwable;

/**
 * Command for listing validation rules in a simplified format.
 *
 * Displays rule names and descriptions in a clean, readable format.
 */
class ListRulesCommand extends Command
{
    /**
     * The command signature with available options.
     *
     * @var string
     */
    protected $signature = 'roster:list-rules
                            {entity? : Entity class name or entity type (availability, schedule, impediment)}
                            {--operation= : Filter by operation type (create, update, delete)}
                            {--simple : Display in simple list format (no table)}
                            {--count : Show only the count of rules}
                            {--details : Show additional details like priority and supported operations}';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'List Roster validation rules with names and descriptions';

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

            if ($this->option('count')) {
                return $this->displayRulesCount(
                    entityInput: $entityInput,
                    operationFilter: $operationFilter,
                    validator: $validator
                );
            }

            if ($entityInput) {
                $this->listRulesForEntity(
                    entityInput: $entityInput,
                    operationFilter: $operationFilter,
                    validator: $validator
                );
            } else {
                $this->listAllRules($ruleScanner, $validator);
            }

            return self::SUCCESS;
        } catch (Throwable $throwable) {
            $this->error($throwable->getMessage());

            if ($this->option('details')) {
                $this->error($throwable->getTraceAsString());
            }

            return self::FAILURE;
        }
    }

    /**
     * Display only the count of rules.
     *
     * @param string|null $entityInput Entity identifier input
     * @param string|null $operationFilter Operation type filter
     * @param Validator $validator Validator instance
     * @return int Command exit code
     */
    private function displayRulesCount(
        ?string $entityInput,
        ?string $operationFilter,
        Validator $validator
    ): int {
        if ($entityInput) {
            $entityType = $this->resolveEntityType($entityInput);
            $count = $this->countRulesForEntity($entityType, $operationFilter, $validator);

            $message = sprintf(
                "Found %d validation rule(s) for entity '%s'",
                $count,
                $entityType->value
            );

            if ($operationFilter) {
                $message .= sprintf(" with operation '%s'", $operationFilter);
            }
        } else {
            $count = $this->countAllRules($validator);
            $message = sprintf("Found %d validation rule(s) in total", $count);
        }

        $this->info($message);
        return self::SUCCESS;
    }

    /**
     * List rules for a specific entity.
     *
     * @param string $entityInput Entity identifier input
     * @param string|null $operationFilter Operation type filter
     * @param Validator $validator Validator instance
     */
    private function listRulesForEntity(
        string $entityInput,
        ?string $operationFilter,
        Validator $validator
    ): void {
        $entityType = $this->resolveEntityType($entityInput);

        $this->line('📋 Listing validation rules for: ' . $entityInput);
        $this->line("📊 Entity Type: " . $entityType->value);

        if ($operationFilter) {
            $this->line("🔄 Operation: " . $operationFilter);
        }

        $this->newLine();

        $rules = $this->getRulesForEntity($entityType, $operationFilter, $validator);

        if (empty($rules)) {
            $this->warn('No validation rules found for this entity/operation combination.');
            return;
        }

        if ($this->option('simple')) {
            $this->displaySimpleList($rules);
        } else {
            $this->displayRulesTable($rules);
        }
    }

    /**
     * List all rules in the system.
     *
     * @param RuleScanner $ruleScanner Rule scanner service
     * @param Validator $validator Validator instance
     */
    private function listAllRules(RuleScanner $ruleScanner, Validator $validator): void
    {
        $this->line('📋 All validation rules in Roster system');
        $this->newLine();

        $scannedRules = $ruleScanner->scan();
        $this->line("📊 Scanner detected: " . count($scannedRules) . " rules");
        $this->newLine();

        $allRules = $this->getAllRules($validator);

        if (empty($allRules)) {
            $this->warn('No validation rules found in the system.');
            return;
        }

        if ($this->option('simple')) {
            $this->displaySimpleList($allRules);
        } else {
            $this->displayRulesTable($allRules);
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
        } catch (ValueError) {
            $this->warn(sprintf("Entity '%s' not found in EntityType enum. Using AVAILABILITY as default.", $input));
            return EntityType::AVAILABILITY;
        }
    }

    /**
     * Get rules for a specific entity and optional operation.
     *
     * @param EntityType $entityType Entity type to get rules for
     * @param string|null $operationFilter Operation type filter
     * @param Validator $validator Validator instance
     * @return array Collection of validation rules with context
     */
    private function getRulesForEntity(
        EntityType $entityType,
        ?string $operationFilter,
        Validator $validator
    ): array {
        $rules = [];

        // Only support CREATE, UPDATE, DELETE operations (not RETRIEVE)
        $supportedOperations = [
            OperationType::CREATE,
            OperationType::UPDATE,
            OperationType::DELETE
        ];

        $operations = $operationFilter
            ? [$this->resolveOperationType($operationFilter)]
            : $supportedOperations;

        foreach ($operations as $operation) {
            // Skip RETRIEVE operation
            if ($operation === OperationType::RETRIEVE) {
                continue;
            }

            $entityRules = $validator->getRulesFor($operation, $entityType);

            foreach ($entityRules as $rule) {
                if ($rule instanceof RuleInterface) {
                    $rules[] = [
                        'rule' => $rule,
                        'operation' => $operation,
                        'entity' => $entityType,
                    ];
                }
            }
        }

        return $rules; // Retourner toutes les règles sans déduplication
    }

    /**
     * Get all rules from all entities.
     *
     * @param Validator $validator Validator instance
     * @return array All rules with context
     */
    private function getAllRules(Validator $validator): array
    {
        $allRules = [];

        foreach (EntityType::cases() as $entityType) {
            foreach ([OperationType::CREATE, OperationType::UPDATE, OperationType::DELETE] as $operation) {
                $entityRules = $validator->getRulesFor($operation, $entityType);

                foreach ($entityRules as $rule) {
                    if ($rule instanceof RuleInterface) {
                        $allRules[] = [
                            'rule' => $rule,
                            'operation' => $operation,
                            'entity' => $entityType,
                        ];
                    }
                }
            }
        }

        return $allRules; // Retourner toutes les règles sans déduplication
    }

    /**
     * Remove duplicate rules from the collection.
     * Note: Cette méthode n'est plus utilisée car on veut voir les règles par opération.
     *
     * @param array $rules Rules with context
     * @return array Deduplicated rules
     */
    private function deduplicateRules(array $rules): array
    {
        // Garder toutes les règles pour montrer les différentes opérations
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
     * Display rules in a simple list format.
     *
     * @param array $rules Rules to display
     */
    private function displaySimpleList(array $rules): void
    {
        foreach ($rules as $index => $ruleData) {
            /** @var RuleInterface $rule */
            $rule = $ruleData['rule'];

            $this->line(sprintf("%d. %s", $index + 1, $rule->getName()));
            $this->line("   📝 " . $this->truncateDescription($rule->getDescription()));

            if ($this->option('details')) {
                $this->displayVerboseDetails($ruleData);
            }

            if ($index < count($rules) - 1) {
                $this->line('');
            }
        }
    }

    /**
     * Display rules in a formatted table with proper multiline descriptions.
     *
     * @param array $rules Rules to display
     */
    private function displayRulesTable(array $rules): void
    {
        $table = new Table($this->output);

        $headers = ['#', 'Rule Name', 'Description', 'Operation', 'Entity'];
        if ($this->option('details')) {
            $headers = array_merge($headers, ['Priority', 'Class']);
        }

        $table->setHeaders($headers);

        $rows = [];
        foreach ($rules as $index => $ruleData) {
            /** @var RuleInterface $rule */
            $rule = $ruleData['rule'];

            // Wordwrap pour que les lignes ne dépassent pas 100 caractères
            $description = wordwrap($rule->getDescription(), 100, "\n", true);

            $row = [
                $index + 1,
                $rule->getName(),
                $description,
                $ruleData['operation']->value,
                $ruleData['entity']->value,
            ];

            if ($this->option('details')) {
                $row = array_merge($row, [
                    $rule->getPriority(),
                    get_class($rule),
                ]);
            }

            $rows[] = $row;
        }

        $table->setRows($rows);
        $table->render();
    }

    /**
     * Truncate description for display.
     *
     * @param string $description Full description
     * @return string Truncated description
     */
    private function truncateDescription(string $description): string
    {
        $maxLength = 300;

        if (strlen($description) <= $maxLength) {
            return $description;
        }

        return substr($description, 0, $maxLength - 3) . '...';
    }

    /**
     * Display details details for a rule.
     *
     * @param array $ruleData Rule data
     */
    private function displayVerboseDetails(array $ruleData): void
    {
        /** @var RuleInterface $rule */
        $rule = $ruleData['rule'];

        $this->line("   ⚡ Priority: " . $rule->getPriority());
        $this->line("   🔄 Operation: " . $ruleData['operation']->value);
        $this->line("   📊 Entity: " . $ruleData['entity']->value);
        $this->line("   🏷️  Class: " . get_class($rule));
    }

    /**
     * Count rules for a specific entity.
     *
     * @param EntityType $entityType Entity type
     * @param string|null $operationFilter Operation filter
     * @param Validator $validator Validator instance
     * @return int Number of rules
     */
    private function countRulesForEntity(
        EntityType $entityType,
        ?string $operationFilter,
        Validator $validator
    ): int {
        $rules = $this->getRulesForEntity($entityType, $operationFilter, $validator);
        return count($rules);
    }

    /**
     * Count all rules in the system.
     *
     * @param Validator $validator Validator instance
     * @return int Total number of rules
     */
    private function countAllRules(Validator $validator): int
    {
        $rules = $this->getAllRules($validator);
        return count($rules);
    }
}
