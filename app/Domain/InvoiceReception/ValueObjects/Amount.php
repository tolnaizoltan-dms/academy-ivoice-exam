<?php

declare(strict_types=1);

namespace App\Domain\InvoiceReception\ValueObjects;

use App\Domain\InvoiceReception\Exceptions\InvalidInvoiceException;

/**
 * Value Object representing an invoice amount.
 *
 * Must be a positive value greater than zero.
 */
final readonly class Amount
{
    public function __construct(
        private float $value
    ) {
        if ($value <= 0) {
            throw InvalidInvoiceException::invalidAmount($value);
        }
    }

    public static function fromFloat(float $value): self
    {
        return new self($value);
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return abs($this->value - $other->value) < 0.0001;
    }

    public function __toString(): string
    {
        return number_format($this->value, 2, '.', '');
    }
}
