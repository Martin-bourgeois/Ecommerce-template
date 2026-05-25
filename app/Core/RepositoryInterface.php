<?php

declare(strict_types=1);

namespace App\Core;

interface RepositoryInterface
{
    public function find(int|string $id): mixed;

    public function all(): mixed;

    public function create(array $data): mixed;

    public function update(int|string $id, array $data): mixed;

    public function delete(int|string $id): bool;

    public function paginate(int $perPage = 15): mixed;
}
