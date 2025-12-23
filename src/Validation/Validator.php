<?php

declare(strict_types=1);

namespace Roster\Validation;

use Throwable;
use ReflectionClass;
use Roster\Validation\Attributes\ValidationRule;
use Roster\Contracts\Validation\RuleInterface;
use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Contracts\Validation\ValidatorInterface;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;

class Validator implements ValidatorInterface
{
    /**
     * @var array<string, array<int, RuleInterface>>
     */
    private array $rulesByEntityOperation = [];

    /**
     * @var array<int, RuleInterface>
     */
    private array $allRules = [];

    private RuleScanner $ruleScanner;

    public function __construct(?RuleScanner $ruleScanner = null)
    {
        $this->ruleScanner = $ruleScanner ?? new RuleScanner([
            __DIR__ . '/Rules',
            // Ajoutez d'autres répertoires si nécessaire
        ]);

        $this->discoverAndRegisterRules();
    }

    private function discoverAndRegisterRules(): void
    {
        $rules = $this->ruleScanner->instantiateRules();

        foreach ($rules as $rule) {
            $this->addRule($rule);
        }
    }

    public function validate(
        ValidationContextInterface $validationContext,
        array $additionalRules = []
    ): ValidationResult {
        $operationType = $validationContext->getOperation();
        $entityType = $validationContext->getEntityType();

        // Récupérer les règles applicables
        $applicableRules = $this->getRulesFor($operationType, $entityType);

        if ($additionalRules !== []) {
            $applicableRules = array_merge($applicableRules, $additionalRules);
        }

        // Tri par priorité (plus haut = exécuté en premier)
        usort(
            $applicableRules,
            fn(RuleInterface $a, RuleInterface $b): int =>
            $b->getPriority() <=> $a->getPriority()
        );

        foreach ($applicableRules as $applicableRule) {
            try {
                $applicableRule->validate($validationContext);
            } catch (Throwable $e) {

                $validationContext->setViolation('_system', sprintf('Validation rule %s failed: ', $applicableRule->getName()) . $e->getMessage());
            }
        }

        return new ValidationResult(
            !$validationContext->hasViolations(),
            $validationContext->getViolations()
        );
    }

    public function addRule(RuleInterface $rule): void
    {
        $this->allRules[] = $rule;

        // Indexer la règle pour un accès rapide
        $reflectionClass = new ReflectionClass($rule);
        $attributes = $reflectionClass->getAttributes(ValidationRule::class);

        if ($attributes !== []) {
            $attribute = $attributes[0]->newInstance();

            foreach ($attribute->entities as $entity) {
                if (!$entity instanceof EntityType) {
                    continue;
                }

                foreach ($attribute->operations as $operation) {
                    if (!$operation instanceof OperationType) {
                        continue;
                    }

                    $key = $this->getCacheKey($operation, $entity);

                    if (!isset($this->rulesByEntityOperation[$key])) {
                        $this->rulesByEntityOperation[$key] = [];
                    }

                    $this->rulesByEntityOperation[$key][] = $rule;
                }
            }
        } else {
            // Fallback: utiliser la méthode supports() si pas d'attribut
            foreach (EntityType::cases() as $entity) {
                foreach (OperationType::cases() as $operation) {
                    if ($rule->supports($operation, $entity)) {
                        $key = $this->getCacheKey($operation, $entity);

                        if (!isset($this->rulesByEntityOperation[$key])) {
                            $this->rulesByEntityOperation[$key] = [];
                        }

                        $this->rulesByEntityOperation[$key][] = $rule;
                    }
                }
            }
        }
    }

    public function getRulesFor(OperationType $operationType, EntityType $entityType): array
    {
        $key = $this->getCacheKey($operationType, $entityType);
        return $this->rulesByEntityOperation[$key] ?? [];
    }

    public function hasRulesFor(OperationType $operationType, EntityType $entityType): bool
    {
        return $this->getRulesFor($operationType, $entityType) !== [];
    }

    private function getCacheKey(OperationType $operationType, EntityType $entityType): string
    {
        return $operationType->value . ':' . $entityType->value;
    }

    /**
     * Obtenir toutes les règles enregistrées (pour le débogage)
     *
     * @return array<int, RuleInterface>
     */
    public function getAllRules(): array
    {
        return $this->allRules;
    }

    /**
     * Vérifier si une règle spécifique est enregistrée
     */
    public function hasRule(string $ruleClass): bool
    {
        foreach ($this->allRules as $allRule) {
            if (get_class($allRule) === $ruleClass) {
                return true;
            }
        }

        return false;
    }

    /**
     * Obtenir le nombre total de règles enregistrées
     */
    public function getRuleCount(): int
    {
        return count($this->allRules);
    }

    /**
     * Obtenir les règles triées par priorité
     */
    public function getRulesSortedByPriority(): array
    {
        $sortedRules = $this->allRules;
        usort($sortedRules, fn($a, $b): int => $b->getPriority() <=> $a->getPriority());
        return $sortedRules;
    }

    /**
     * Réinitialiser toutes les règles (utile pour les tests)
     */
    public function reset(): void
    {
        $this->rulesByEntityOperation = [];
        $this->allRules = [];
        $this->discoverAndRegisterRules();
    }
}
