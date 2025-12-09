<?php

declare(strict_types=1);

namespace App\Application\Actions;

use App\Application\DTOs\StartApprovalData;
use App\Domain\Approval\Aggregates\Approval;
use App\Domain\Approval\Contracts\ApprovalRepositoryInterface;
use App\Domain\Approval\ValueObjects\ApproverId;

/**
 * Application Action: Start an approval process.
 *
 * This is the Command Handler for the "Start Approval Process" use case.
 * It is typically triggered by the Policy (Event Listener) after an invoice is submitted.
 */
final class StartApprovalProcessAction
{
    public function __construct(
        private ApprovalRepositoryInterface $approvalRepository,
    ) {}

    /**
     * Execute the start approval process action.
     *
     * @throws \App\Domain\Approval\Exceptions\InvalidApprovalException
     */
    public function execute(StartApprovalData $data): Approval
    {
        // Create Value Objects from raw data (validates invariants)
        $approverId = new ApproverId($data->approverId);

        // Create Approval aggregate via factory method
        $approval = Approval::start(
            id: $this->approvalRepository->nextIdentity(),
            invoiceId: $data->invoiceId,
            approverId: $approverId,
        );

        // Persist the approval
        $this->approvalRepository->save($approval);

        // Dispatch domain events via Laravel Event system
        foreach ($approval->releaseEvents() as $event) {
            event($event);
        }

        return $approval;
    }
}
