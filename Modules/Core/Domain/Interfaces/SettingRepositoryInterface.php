<?php

namespace Modules\Core\Domain\Interfaces;

use Modules\Core\Domain\Models\SystemSetting;
use Illuminate\Support\Collection;

interface SettingRepositoryInterface extends BaseRepositoryInterface
{
    public function getAllSettings(): Collection;
    public function getByName(string $name): ?SystemSetting;
}