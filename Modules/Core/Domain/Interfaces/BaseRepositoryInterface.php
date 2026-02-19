<?php

namespace Modules\Core\Domain\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface BaseRepositoryInterface
{
    public function all(array $columns = ['*'], array $relations = []): Collection;

    public function find(int|string $id, array $columns = ['*'], array $relations = []): ?Model;

    public function create(array $details): Model;

    public function update(int|string $id, array $newDetails): bool;

    public function delete(int|string $id): bool;

    public function updateOrCreate(array $attributes, array $values): Model;
}