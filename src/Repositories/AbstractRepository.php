<?php

declare(strict_types=1);

namespace Roster\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

abstract class AbstractRepository
{
    /**
     * Crée un nouvel enregistrement.
     *
     * @param array<string, mixed> $data
     */
    abstract public function create(array $data): Model;

    /**
     * Met à jour un enregistrement existant.
     *
     * @param array<string, mixed> $data
     */
    abstract public function update(int $id, array $data): bool;

    /**
     * Supprime un enregistrement.
     */
    abstract public function delete(int $id): bool;

    /**
     * Récupère un enregistrement par son identifiant.
     */
    abstract public function findById(int $id): ?Model;

    /**
     * Récupère tous les enregistrements.
     *
     * @return Collection<int, Model>
     */
    abstract public function getAll(): Collection;
}
