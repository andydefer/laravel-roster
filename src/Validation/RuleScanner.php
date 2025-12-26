<?php

declare(strict_types=1);

namespace Roster\Validation;

use RuntimeException;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Illuminate\Support\Facades\Log;
use ReflectionClass;
use Throwable;
use Roster\Contracts\Validation\RuleInterface;
use Roster\Validation\Attributes\ValidationRule;
use Roster\Validation\Cache\RuleCacheGenerator;
use Symfony\Component\Finder\Finder;

class RuleScanner
{
    /**
     * @var mixed[]
     */
    private array $ruleDirectories;

    private ?array $cachedRules = null;

    private bool $useCacheFile;

    private ?string $cacheFile;

    public function __construct(
        array $ruleDirectories = [],
        bool $useCacheFile = true
    ) {
        $this->ruleDirectories = $ruleDirectories;
        $this->useCacheFile = $useCacheFile;
        $this->cacheFile = config('roster.cache.cache_file');
    }

    public function scan(): array
    {
        if ($this->useCacheFile && $this->shouldUseCache()) {
            return $this->loadFromCache();
        }

        return $this->doScan();
    }

    private function shouldUseCache(): bool
    {
        if (!$this->cacheFile || !file_exists($this->cacheFile)) {
            return false;
        }

        // En production, toujours utiliser le cache
        if (app()->isProduction()) {
            return true;
        }

        // En développement, vérifier si le cache est frais
        $ruleCacheGenerator = new RuleCacheGenerator($this);
        return $ruleCacheGenerator->isCacheFresh();
    }

    private function loadFromCache(): array
    {
        try {
            $rules = require $this->cacheFile;

            // Valider la structure du cache
            if (!is_array($rules)) {
                throw new RuntimeException('Invalid cache file structure');
            }

            // Convertir les données en objets ValidationRule
            $result = [];
            foreach ($rules as $className => $data) {
                $result[$className] = new ValidationRule(
                    priority: $data['priority'],
                    entities: array_map(
                        fn($e) => EntityType::from($e),
                        $data['entities']
                    ),
                    operations: array_map(
                        fn($o) => OperationType::from($o),
                        $data['operations']
                    )
                );
            }

            return $result;
        } catch (Throwable $throwable) {
            // Si le cache est corrompu, régénérer
            Log::warning('Roster rule cache corrupted, regenerating', [
                'file' => $this->cacheFile,
                'error' => $throwable->getMessage()
            ]);

            return $this->regenerateCache();
        }
    }

    private function regenerateCache(): array
    {
        $rules = $this->doScan();
        $ruleCacheGenerator = new RuleCacheGenerator($this);
        $ruleCacheGenerator->generate();

        return $rules;
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
