<?php

declare(strict_types=1);

namespace Roster\Validation;

use ReflectionClass;
use Throwable;
use Roster\Contracts\Validation\RuleInterface;
use Roster\Validation\Attributes\ValidationRule;
use Symfony\Component\Finder\Finder;

class RuleScanner
{
    /**
     * @var mixed[]
     */
    private array $ruleDirectories;

    private bool $withCache;

    private ?array $cachedRules = null;

    public function __construct(array $ruleDirectories = [], bool $withCache = false)
    {
        $this->ruleDirectories = $ruleDirectories;
        $this->withCache = $withCache;
    }

    public function scan(): array
    {
        if ($this->withCache) {
            return cache()->rememberForever('roster_validation_rules', fn(): array => $this->doScan());
        }

        return $this->doScan();
    }

    private function doScan(): array
    {
        // Si déjà scanné intra-requête
        if ($this->cachedRules !== null) {
            return $this->cachedRules;
        }

        $rules = [];

        foreach ($this->ruleDirectories as $ruleDirectory) {
            if (!is_dir($ruleDirectory)) {
                continue;
            }

            $finder = new Finder();
            $finder->files()->in($ruleDirectory)->name('*Rule.php');

            foreach ($finder as $file) {
                $className = $this->getClassNameFromFile($file->getPathname());

                if ($className && class_exists($className)) {
                    $reflection = new ReflectionClass($className);

                    if ($reflection->implementsInterface(RuleInterface::class)) {
                        try {
                            $ruleInstance = app()->make($className);
                            $attribute = $ruleInstance->getValidationRuleAttribute();
                            if ($attribute) {
                                $rules[$className] = $attribute;
                            }
                        } catch (Throwable $e) {
                            $attributes = $reflection->getAttributes(ValidationRule::class);
                            if ($attributes !== []) {
                                $rules[$className] = $attributes[0]->newInstance();
                            }
                        }
                    }
                }
            }
        }

        uasort($rules, fn($a, $b): int => $b->priority <=> $a->priority);

        $this->cachedRules = $rules;
        return $rules;
    }

    public function instantiateRules(): array
    {
        $rules = [];
        foreach (array_keys($this->scan()) as $className) {
            try {
                $rules[] = app()->make($className);
            } catch (Throwable $e) {
                $rules[] = new $className();
            }
        }

        return $rules;
    }

    private function getClassNameFromFile(string $filePath): ?string
    {
        $content = file_get_contents($filePath);
        if (preg_match('/namespace\s+([^;]+);/', $content, $namespaceMatches)) {
            $namespace = $namespaceMatches[1];
            if (preg_match('/class\s+(\w+)/', $content, $classMatches)) {
                return $namespace . '\\' . $classMatches[1];
            }
        }

        return null;
    }
}
