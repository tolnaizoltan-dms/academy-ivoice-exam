<?php

declare(strict_types=1);

namespace App\Domain\Approval\Exceptions;

use App\Exceptions\DomainException;

/**
 * Exception thrown when an approval operation violates business rules.
 */
final class InvalidApprovalException extends DomainException
{
    public static function alreadyApproved(): self
    {
        return new self('Cannot modify an already approved invoice.');
    }

    public static function alreadyRejected(): self
    {
        return new self('Cannot modify an already rejected invoice.');
    }

    public static function invalidStatusTransition(string $from, string $to): self
    {
        return new self(
            sprintf('Cannot transition from %s to %s.', $from, $to)
        );
    }

    public static function emptyApproverId(): self
    {
        return new self('Approver ID cannot be empty.');
    }

    public static function emptyInvoiceId(): self
    {
        return new self('Invoice ID cannot be empty.');
    }
}
