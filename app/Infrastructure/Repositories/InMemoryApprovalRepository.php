<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Domain\Approval\Aggregates\Approval;
use App\Domain\Approval\Contracts\ApprovalRepositoryInterface;
use App\Domain\Approval\ValueObjects\ApprovalId;

/**
 * In-memory implementation of Approval Repository.
 *
 * Useful for testing and demonstration purposes.
 * Can be replaced with Eloquent implementation for persistence.
 */
final class InMemoryApprovalRepository implements ApprovalRepositoryInterface
{
    /** @var array<string, Approval> */
    private array $approvals = [];

    public function save(Approval $approval): void
    {
        $this->approvals[$approval->getId()->value] = $approval;
    }

    public function findById(ApprovalId $id): ?Approval
    {
        return $this->approvals[$id->value] ?? null;
    }

    public function findByInvoiceId(string $invoiceId): ?Approval
    {
        foreach ($this->approvals as $approval) {
            if ($approval->getInvoiceId() === $invoiceId) {
                return $approval;
            }
        }

        return null;
    }

    public function nextIdentity(): ApprovalId
    {
        return ApprovalId::generate();
    }

    /**
     * Get all stored approvals (for testing/debugging).
     *
     * @return array<string, Approval>
     */
    public function all(): array
    {
        return $this->approvals;
    }

    /**
     * Clear all stored approvals (for testing).
     */
    public function clear(): void
    {
        $this->approvals = [];
    }
}
