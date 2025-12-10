<?php

declare(strict_types=1);

namespace App\Domain\InvoiceReception\Exceptions;

use App\Exceptions\DomainException;

/**
 * Exception thrown when an invoice operation violates business rules.
 */
final class InvalidInvoiceException extends DomainException
{
    public static function invalidAmount(float $amount): self
    {
        return new self(
            sprintf('Invoice amount must be greater than zero. Given: %s', $amount)
        );
    }

    public static function invalidInvoiceNumber(string $number): self
    {
        return new self(
            sprintf('Invoice number must be in format INV-YYYY-XXXX. Given: %s', $number)
        );
    }

    public static function emptySubmitterId(): self
    {
        return new self('Submitter ID cannot be empty.');
    }

    public static function emptySupervisorId(): self
    {
        return new self('Supervisor ID cannot be empty.');
    }

    public static function duplicateInvoiceNumber(string $number): self
    {
        return new self(
            sprintf('An invoice with number %s already exists.', $number)
        );
    }
}
