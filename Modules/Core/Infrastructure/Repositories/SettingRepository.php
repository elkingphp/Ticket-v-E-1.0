<?php

namespace Modules\Core\Infrastructure\Repositories;

use Modules\Core\Domain\Interfaces\SettingRepositoryInterface;
use Modules\Core\Domain\Models\SystemSetting;
use Illuminate\Support\Collection;

class SettingRepository extends BaseRepository implements SettingRepositoryInterface
{
    public function __construct(SystemSetting $model)
    {
        parent::__construct($model);
    }

    public function getAllSettings(): Collection
    {
        return $this->model->all();
    }

    public function getByName(string $name): ?SystemSetting
    {
        return $this->model->where('name', $name)->first();
    }
}