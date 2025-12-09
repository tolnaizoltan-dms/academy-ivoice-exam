<?php

declare(strict_types=1);

namespace App\Application\Actions;

use App\Domain\Approval\Aggregates\Approval;
use App\Domain\Approval\Contracts\ApprovalRepositoryInterface;
use App\Domain\Approval\Exceptions\InvalidApprovalException;
use App\Domain\Approval\ValueObjects\ApprovalId;

/**
 * Application Action: Reject an invoice.
 *
 * This is the Command Handler for the "Reject Invoice" use case.
 */
final class RejectInvoiceAction
{
    public function __construct(
        private ApprovalRepositoryInterface $approvalRepository,
    ) {}

    /**
     * Execute the reject invoice action.
     *
     * @throws InvalidApprovalException
     */
    public function execute(string $approvalId, string $reason): Approval
    {
        $approval = $this->approvalRepository->findById(
            ApprovalId::fromString($approvalId)
        );

        if (!$approval instanceof \App\Domain\Approval\Aggregates\Approval) {
            throw new InvalidApprovalException(
                sprintf('Approval with ID %s not found.', $approvalId)
            );
        }

        $approval->reject($reason);

        $this->approvalRepository->save($approval);

        // Dispatch domain events via Laravel Event system
        foreach ($approval->releaseEvents() as $event) {
            event($event);
        }

        return $approval;
    }
}
