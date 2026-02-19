<?php

namespace Modules\Core\Domain\Interfaces;

interface ModuleHealthContract
{
    /**
     * Check the health of the module.
     * 
     * @return array [
     *   'status' => 'healthy'|'degraded'|'critical',
     *   'impact_score' => int (0-100),
     *   'blocking' => bool,
     *   'details' => array
     * ]
     */
    public function checks(): array;

    /**
     * Determine if the health status is critical and should block operations.
     */
    public function critical(): bool;
}