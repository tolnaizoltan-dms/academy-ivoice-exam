<?php

declare(strict_types=1);

namespace App\Domain\Approval\Contracts;

use App\Domain\Approval\Aggregates\Approval;
use App\Domain\Approval\ValueObjects\ApprovalId;

/**
 * Repository contract for Approval aggregate persistence.
 */
interface ApprovalRepositoryInterface
{
    /**
     * Persist an approval to the repository.
     */
    public function save(Approval $approval): void;

    /**
     * Find an approval by its ID.
     */
    public function findById(ApprovalId $id): ?Approval;

    /**
     * Find an approval by the associated invoice ID.
     */
    public function findByInvoiceId(string $invoiceId): ?Approval;

    /**
     * Generate a new unique identity for an approval.
     */
    public function nextIdentity(): ApprovalId;
}
