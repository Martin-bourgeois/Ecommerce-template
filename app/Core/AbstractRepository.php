<?php

declare(strict_types=1);

namespace App\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class AbstractRepository implements RepositoryInterface
{
    protected Model $model;

    public function __construct()
    {
        $this->setModel();
    }

    abstract protected function setModel(): void;

    public function find(int|string $id): mixed
    {
        return $this->model->find($id);
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(int|string $id, array $data): bool
    {
        $model = $this->find($id);

        if (!$model) {
            return false;
        }

        return $model->update($data);
    }

    public function delete(int|string $id): bool
    {
        $model = $this->find($id);

        if (!$model) {
            return false;
        }

        return $model->delete();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->paginate($perPage);
    }
}
