<?php

declare(strict_types=1);

namespace Roster\Validation\Context;

use RuntimeException;
use Roster\Facades\Availability;
use Roster\Facades\Schedule;
use Roster\Facades\Impediment;
use Exception;
use Roster\Services\AvailabilityService;
use Illuminate\Database\Eloquent\Model;
use Roster\Contracts\EntityServiceInterface;
use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;

class ValidationContext implements ValidationContextInterface
{
    private OperationType $operationType;

    private EntityType $entityType;

    /**
     * Raw data (may contain null values)
     *
     * @var array<string, mixed>
     */
    private array $data;

    private ?Model $model;

    private mixed $currentEntity;

    /**
     * @var array<string, string|array<int, string>>
     */
    private array $violations = [];

    /**
     * @var array<string, mixed>
     */
    private array $flags = [];

    public function __construct(
        OperationType $operationType,
        EntityType $entityType,
        array $data,
        ?Model $model = null,
        mixed $currentEntity = null
    ) {
        $this->operationType  = $operationType;
        $this->entityType     = $entityType;
        $this->data           = $data;
        $this->model          = $model;
        $this->currentEntity  = $currentEntity;
    }

    /* -----------------------------------------------------------------
     | Context metadata
     | -----------------------------------------------------------------
     */

    public function getOperation(): OperationType
    {
        return $this->operationType;
    }

    public function getEntityType(): EntityType
    {
        return $this->entityType;
    }

    public function getSchedulable(): ?Model
    {
        return $this->model;
    }

    /**
     * Get the current service instance configured with the appropriate context.
     *
     * Returns an instance of the service facade with:
     * - For Availability: schedulable already set via for()
     * - For Schedule and Impediment: schedulable set via for() and owner already set via owner()
     *
     * @return EntityServiceInterface The service instance with context configured
     */
    public function getCurrentService(): EntityServiceInterface
    {
        // Récupérer le schedulable du contexte
        $schedulable = $this->getSchedulable();

        if (!$schedulable instanceof Model) {
            throw new RuntimeException('Cannot get service: schedulable is not set in validation context');
        }

        // Détecter le type d'entité et utiliser la facade appropriée
        return match ($this->getEntityType()) {
            EntityType::AVAILABILITY =>
            // Pour Availability: seulement le schedulable
            Availability::for($schedulable),

            EntityType::SCHEDULE =>
            // Pour Schedule: schedulable et owner (s'il existe dans le contexte)
            $this->setupScheduleService($schedulable),

            EntityType::IMPEDIMENT =>
            // Pour Impediment: schedulable et owner (s'il existe dans le contexte)
            $this->setupImpedimentService($schedulable),
        };
    }

    /**
     * Configure le service Schedule avec le contexte approprié.
     */
    private function setupScheduleService(Model $model): EntityServiceInterface
    {
        // Récupérer l'owner depuis les données ou l'entité courante
        $owner = $this->resolveOwner();

        if (!$owner instanceof Model) {
            throw new RuntimeException('Cannot get Schedule service: owner is required but not available in validation context');
        }

        return Schedule::for($model)->owner($owner);
    }

    /**
     * Configure le service Impediment avec le contexte approprié.
     */
    private function setupImpedimentService(Model $model): EntityServiceInterface
    {
        // Récupérer l'owner depuis les données ou l'entité courante
        $owner = $this->resolveOwner();

        if (!$owner instanceof Model) {
            throw new RuntimeException('Cannot get Impediment service: owner is required but not available in validation context');
        }

        return Impediment::for($model)->owner($owner);
    }

    /**
     * Résout l'owner depuis le contexte de validation.
     */
    private function resolveOwner(): ?Model
    {
        // 1. Chercher dans les données brutes
        if (isset($this->data['availability_id']) && $this->model instanceof Model) {
            // Si on a un ID de disponibilité, chercher le modèle
            try {
                return \Roster\Models\Availability::find($this->data['availability_id']);
            } catch (Exception $e) {
                // Continuer avec d'autres méthodes si non trouvé
            }
        }

        // 2. Chercher dans l'entité courante
        if ($this->currentEntity instanceof Model) {
            // Si c'est un Schedule ou Impediment, il a une relation availability
            if (method_exists($this->currentEntity, 'availability')) {
                return $this->currentEntity->availability;
            }

            // Si c'est une Availability, c'est déjà l'owner
            if ($this->currentEntity instanceof \Roster\Models\Availability) {
                return $this->currentEntity;
            }
        }

        // 3. Chercher dans les flags
        if ($this->hasFlag('availability')) {
            $owner = $this->getFlag('availability');
            if ($owner instanceof Model) {
                return $owner;
            }
        }

        return null;
    }

    /**
     * Get an Availability service instance configured with the schedulable context.
     *
     * Returns an instance of Availability service facade with the schedulable already set via for().
     *
     * @return AvailabilityService The Availability service instance
     */
    public function getAvailabilityService(): AvailabilityService
    {
        $schedulable = $this->getSchedulable();

        if (!$schedulable instanceof Model) {
            throw new RuntimeException('Cannot get Availability service: schedulable is not set in validation context');
        }

        return Availability::for($schedulable);
    }

    public function getCurrentEntity(): mixed
    {
        return $this->currentEntity;
    }

    /* -----------------------------------------------------------------
     | Safe data access (null = absent)
     | -----------------------------------------------------------------
     */

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->data[$key] ?? null;

        return $value !== null ? $value : $default;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data)
            && $this->data[$key] !== null;
    }

    public function safeData(): array
    {
        return array_filter(
            $this->data,
            static fn($value): bool => $value !== null
        );
    }

    /* -----------------------------------------------------------------
     | Raw data access (includes nulls)
     | -----------------------------------------------------------------
     */

    public function rawGet(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function rawHas(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return array<string, mixed>
     */
    public function rawData(): array
    {
        return $this->data;
    }

    /* -----------------------------------------------------------------
     | Mutation
     | -----------------------------------------------------------------
     */

    public function set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    /* -----------------------------------------------------------------
     | Violations
     | -----------------------------------------------------------------
     */

    public function setViolation(string $field, string $message): void
    {
        $this->violations[$field] = $message;
    }

    public function addViolation(string $field, string $message): void
    {
        if (!isset($this->violations[$field])) {
            $this->violations[$field] = [];
        }

        if (is_string($this->violations[$field])) {
            $this->violations[$field] = [$this->violations[$field]];
        }

        $this->violations[$field][] = $message;
    }

    /**
     * @return array<string, string|array<int, string>>
     */
    public function getViolations(): array
    {
        return $this->violations;
    }

    public function hasViolations(): bool
    {
        return $this->violations !== [];
    }

    /* -----------------------------------------------------------------
     | Flags
     | -----------------------------------------------------------------
     */

    public function setFlag(string $flag, mixed $value = true): void
    {
        $this->flags[$flag] = $value;
    }

    public function hasFlag(string $flag): bool
    {
        return isset($this->flags[$flag]) && $this->flags[$flag];
    }

    public function getFlag(string $flag, mixed $default = false): mixed
    {
        return $this->flags[$flag] ?? $default;
    }
}
