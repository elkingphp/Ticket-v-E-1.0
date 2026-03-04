<?php

namespace Modules\Educational\Application\Exceptions;

use Exception;

class SchedulingConflictException extends Exception
{
    /**
     * Create a new exception instance.
     */
    public function __construct(string $message = "A scheduling conflict occurred.")
    {
        parent::__construct($message);
    }
}
