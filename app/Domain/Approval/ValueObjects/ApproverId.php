<?php

declare(strict_types=1);

namespace App\Domain\Approval\ValueObjects;

use App\Domain\Approval\Exceptions\InvalidApprovalException;

/**
 * Value Object representing the ID of the approver (supervisor).
 */
final readonly class ApproverId
{
    public function __construct(
        public string $value
    ) {
        if ($value === '' || $value === '0') {
            throw InvalidApprovalException::emptyApproverId();
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
