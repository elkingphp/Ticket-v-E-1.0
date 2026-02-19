<?php

namespace Modules\Settings\Domain\Interfaces;

use Modules\Core\Domain\Interfaces\BaseRepositoryInterface;

interface SettingRepositoryInterface extends BaseRepositoryInterface
{
    public function getByKey(string $key, $default = null);
    public function setByKey(string $key, $value);
    public function getByGroup(string $group);
}