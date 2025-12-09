<?php

declare(strict_types=1);

namespace App\Domain\InvoiceReception\ValueObjects;

use Illuminate\Support\Str;

/**
 * Value Object representing a unique Invoice identifier.
 */
final readonly class InvoiceId
{
    public function __construct(
        public string $value
    ) {
        if ($value === '' || $value === '0') {
            throw new \InvalidArgumentException('Invoice ID cannot be empty.');
        }
    }

    /**
     * Generate a new unique Invoice ID.
     */
    public static function generate(): self
    {
        return new self(Str::uuid()->toString());
    }

    /**
     * Create an Invoice ID from an existing string value.
     */
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
