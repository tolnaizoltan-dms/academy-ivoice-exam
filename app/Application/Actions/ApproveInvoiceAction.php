<?php

declare(strict_types=1);

namespace App\Application\Actions;

use App\Domain\Approval\Aggregates\Approval;
use App\Domain\Approval\Contracts\ApprovalRepositoryInterface;
use App\Domain\Approval\Exceptions\InvalidApprovalException;
use App\Domain\Approval\ValueObjects\ApprovalId;

/**
 * Application Action: Approve an invoice.
 *
 * This is the Command Handler for the "Approve Invoice" use case.
 */
final class ApproveInvoiceAction
{
    public function __construct(
        private ApprovalRepositoryInterface $approvalRepository,
    ) {}

    /**
     * Execute the approve invoice action.
     *
     * @throws InvalidApprovalException
     */
    public function execute(string $approvalId): Approval
    {
        $approval = $this->approvalRepository->findById(
            ApprovalId::fromString($approvalId)
        );

        if (!$approval instanceof \App\Domain\Approval\Aggregates\Approval) {
            throw new InvalidApprovalException(
                sprintf('Approval with ID %s not found.', $approvalId)
            );
        }

        $approval->approve();

        $this->approvalRepository->save($approval);

        // Dispatch domain events via Laravel Event system
        foreach ($approval->releaseEvents() as $event) {
            event($event);
        }

        return $approval;
    }
}
