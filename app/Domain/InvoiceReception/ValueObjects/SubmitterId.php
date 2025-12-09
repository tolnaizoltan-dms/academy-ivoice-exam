<?php

declare(strict_types=1);

namespace App\Domain\InvoiceReception\ValueObjects;

use App\Domain\InvoiceReception\Exceptions\InvalidInvoiceException;

/**
 * Value Object representing the ID of the person who submitted the invoice.
 */
final readonly class SubmitterId
{
    public function __construct(
        public string $value
    ) {
        if (empty($value)) {
            throw InvalidInvoiceException::emptySubmitterId();
        }
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
