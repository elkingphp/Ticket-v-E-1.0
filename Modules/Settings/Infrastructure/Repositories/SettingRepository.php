<?php

namespace Modules\Settings\Infrastructure\Repositories;

use Modules\Core\Infrastructure\Repositories\BaseRepository;
use Modules\Settings\Domain\Interfaces\SettingRepositoryInterface;
use Modules\Settings\Domain\Models\Setting;

class SettingRepository extends BaseRepository implements SettingRepositoryInterface
{
    public function __construct(Setting $model)
    {
        parent::__construct($model);
    }

    public function getByKey(string $key, $default = null)
    {
        return \Illuminate\Support\Facades\Cache::rememberForever("setting_{$key}", function () use ($key, $default) {
            $setting = $this->model->where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    public function setByKey(string $key, $value)
    {
        $setting = $this->model->updateOrCreate(
        ['key' => $key],
        ['value' => $value]
        );
        \Illuminate\Support\Facades\Cache::forget("setting_{$key}");
        \Illuminate\Support\Facades\Cache::forget("settings_all");
        return $setting;
    }

    public function all(array $columns = ['*'], array $relations = []): \Illuminate\Database\Eloquent\Collection
    {
        return \Illuminate\Support\Facades\Cache::rememberForever("settings_all", function () use ($columns, $relations) {
            return $this->model->with($relations)->orderBy('sort_order', 'asc')->get($columns);
        });
    }

    public function getByGroup(string $group)
    {
        return $this->model->where('group', $group)->orderBy('sort_order', 'asc')->get()->pluck('value', 'key')->toArray();
    }
}