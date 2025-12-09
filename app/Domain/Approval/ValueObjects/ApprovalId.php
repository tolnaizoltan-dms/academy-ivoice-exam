<?php

declare(strict_types=1);

namespace App\Domain\Approval\ValueObjects;

use Illuminate\Support\Str;

/**
 * Value Object representing a unique Approval identifier.
 */
final readonly class ApprovalId
{
    public function __construct(
        public string $value
    ) {
        if (empty($value)) {
            throw new \InvalidArgumentException('Approval ID cannot be empty.');
        }
    }

    /**
     * Generate a new unique Approval ID.
     */
    public static function generate(): self
    {
        return new self(Str::uuid()->toString());
    }

    /**
     * Create an Approval ID from an existing string value.
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
