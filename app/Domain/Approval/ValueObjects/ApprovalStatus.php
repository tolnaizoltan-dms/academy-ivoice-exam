<?php

declare(strict_types=1);

namespace App\Domain\Approval\ValueObjects;

/**
 * Enum representing the possible states of an approval process.
 */
enum ApprovalStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    /**
     * Check if this status allows modification (approve/reject).
     */
    public function isPending(): bool
    {
        return $this === self::PENDING;
    }

    /**
     * Check if the approval process is complete (approved or rejected).
     */
    public function isComplete(): bool
    {
        return $this === self::APPROVED || $this === self::REJECTED;
    }
}
