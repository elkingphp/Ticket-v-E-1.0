<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Domain\Models\Module;

class ModuleFactory extends Factory
{
    protected $model = Module::class;

    public function definition(): array
    {
        return [
            'id' => fake()->uuid(),
            'slug' => fake()->unique()->slug(),
            'name' => fake()->word(),
            'version' => '1.0.0',
            'status' => 'registered',
            'is_core' => false,
            'priority' => 0,
            'state_version' => 1,
        ];
    }
}