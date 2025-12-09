<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

/**
 * Base exception for all domain-level exceptions.
 *
 * Domain exceptions represent business rule violations and invalid state transitions.
 * They should be caught and handled at the application layer boundary.
 */
abstract class DomainException extends Exception
{
    /**
     * Create a new domain exception instance.
     */
    public function __construct(
        string $message,
        int $code = 0,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
