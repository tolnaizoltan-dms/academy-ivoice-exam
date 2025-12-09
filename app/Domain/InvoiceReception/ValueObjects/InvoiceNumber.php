<?php

declare(strict_types=1);

namespace App\Domain\InvoiceReception\ValueObjects;

use App\Domain\InvoiceReception\Exceptions\InvalidInvoiceException;

/**
 * Value Object representing an Invoice Number.
 *
 * Format: INV-YYYY-XXXX (e.g., INV-2025-0001)
 */
final readonly class InvoiceNumber
{
    private const PATTERN = '/^INV-\d{4}-\d{4}$/';

    public function __construct(
        public string $value
    ) {
        if (! preg_match(self::PATTERN, $value)) {
            throw InvalidInvoiceException::invalidInvoiceNumber($value);
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
