<?php

declare(strict_types=1);

namespace Roster\Validation;

use Illuminate\Support\Facades\Log;
use Roster\Contracts\Validation\RuleInterface;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Validation\Attributes\ValidationRule;
use Roster\Validation\Cache\RuleCacheGenerator;
use ReflectionClass;
use RuntimeException;
use Symfony\Component\Finder\Finder;
use Throwable;

/**
 * Scans directories for validation rules and manages rule caching.
 *
 * Discovers validation rule classes, extracts their metadata via attributes,
 * and provides caching mechanisms for improved performance in production.
 */
class RuleScanner
{
    /**
     * @var string[] Directories to scan for validation rule classes
     */
    private array $directories;

    /**
     * @var array<string, ValidationRule>|null In-memory cache of scanned rules
     */
    private ?array $cachedRules = null;

    /**
     * @var bool Whether to use file-based caching
     */
    private bool $useFileCache;

    /**
     * @var string|null Path to the cache file
     */
    private ?string $cacheFile;

    /**
     * Constructor.
     *
     * @param string[] $directories Directories to scan for validation rules
     * @param bool $useFileCache Whether to enable file-based caching
     */
    public function __construct(array $directories = [], bool $useFileCache = true)
    {
        $this->directories = $directories;
        $this->useFileCache = $useFileCache;
        $this->cacheFile = config('roster.cache.cache_file');
    }

    /**
     * Scans directories for validation rules and returns their metadata.
     *
     * @return array<string, ValidationRule> Array of class names to ValidationRule objects
     */
    public function scan(): array
    {
        if ($this->useFileCache && $this->shouldUseCache()) {
            return $this->loadFromCache();
        }

        return $this->performScan();
    }

    /**
     * Instantiates all discovered validation rules.
     *
     * @return RuleInterface[] Array of instantiated rule objects
     */
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

    /**
     * Determines whether to use the cached version based on environment and freshness.
     *
     * @return bool True if cache should be used, false otherwise
     */
    private function shouldUseCache(): bool
    {
        if (!$this->cacheFile || !file_exists($this->cacheFile)) {
            return false;
        }

        // Always use cache in production for performance
        if (app()->isProduction()) {
            return true;
        }

        // In development, check if cache is fresh
        $ruleCacheGenerator = new RuleCacheGenerator($this);
        return $ruleCacheGenerator->isCacheFresh();
    }

    /**
     * Loads validation rules from the cache file.
     *
     * @return array<string, ValidationRule> Array of class names to ValidationRule objects
     *
     * @throws RuntimeException If cache file structure is invalid
     */
    private function loadFromCache(): array
    {
        try {
            $rules = require $this->cacheFile;

            if (!is_array($rules)) {
                throw new RuntimeException('Invalid cache file structure');
            }

            $result = [];
            foreach ($rules as $className => $data) {
                $result[$className] = new ValidationRule(
                    priority: $data['priority'],
                    entities: array_map(
                        fn($entity) => EntityType::from($entity),
                        $data['entities']
                    ),
                    operations: array_map(
                        fn($operation) => OperationType::from($operation),
                        $data['operations']
                    )
                );
            }

            return $result;
        } catch (Throwable $throwable) {
            Log::warning('Roster rule cache corrupted, regenerating', [
                'file' => $this->cacheFile,
                'error' => $throwable->getMessage()
            ]);

            return $this->regenerateCache();
        }
    }

    /**
     * Regenerates the cache file from fresh scanning.
     *
     * @return array<string, ValidationRule> Freshly scanned rules
     */
    private function regenerateCache(): array
    {
        $rules = $this->performScan();
        $ruleCacheGenerator = new RuleCacheGenerator($this);
        $ruleCacheGenerator->generate();

        return $rules;
    }

    /**
     * Performs the actual directory scanning for validation rules.
     *
     * @return array<string, ValidationRule> Discovered rules sorted by priority
     */
    private function performScan(): array
    {
        if ($this->cachedRules !== null) {
            return $this->cachedRules;
        }

        $rules = [];

        foreach ($this->directories as $directory) {
            if (!is_dir($directory)) {
                continue;
            }

            $finder = new Finder();
            $finder->files()->in($directory)->name('*Rule.php');

            foreach ($finder as $file) {
                $className = $this->extractClassNameFromFile($file->getPathname());

                if ($className && class_exists($className)) {
                    $reflection = new ReflectionClass($className);

                    if ($reflection->implementsInterface(RuleInterface::class)) {
                        $validationRule = $this->extractValidationRule($className, $reflection);

                        if ($validationRule instanceof ValidationRule) {
                            $rules[$className] = $validationRule;
                        }
                    }
                }
            }
        }

        uasort($rules, fn($a, $b): int => $b->priority <=> $a->priority);

        $this->cachedRules = $rules;
        return $rules;
    }

    /**
     * Extracts the ValidationRule attribute from a rule class.
     *
     * @param string $className The fully qualified class name
     * @param ReflectionClass $reflectionClass Reflection of the class
     *
     * @return ValidationRule|null The validation rule attribute, or null if not found
     */
    private function extractValidationRule(string $className, ReflectionClass $reflectionClass): ?ValidationRule
    {
        try {
            $ruleInstance = app()->make($className);
            $attribute = $ruleInstance->getValidationRuleAttribute();

            if ($attribute) {
                return $attribute;
            }
        } catch (Throwable $throwable) {
            $attributes = $reflectionClass->getAttributes(ValidationRule::class);

            if ($attributes !== []) {
                return $attributes[0]->newInstance();
            }
        }

        return null;
    }

    /**
     * Extracts the fully qualified class name from a PHP file.
     *
     * @param string $filePath Path to the PHP file
     *
     * @return string|null Fully qualified class name, or null if not found
     */
    private function extractClassNameFromFile(string $filePath): ?string
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
