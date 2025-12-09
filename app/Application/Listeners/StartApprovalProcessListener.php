<?php

declare(strict_types=1);

namespace App\Application\Listeners;

use App\Application\Actions\StartApprovalProcessAction;
use App\Application\DTOs\StartApprovalData;
use App\Domain\InvoiceReception\Events\InvoiceSubmitted;
use Illuminate\Support\Facades\Log;

/**
 * Policy: Start Approval Process when Invoice is Submitted.
 *
 * This listener implements the policy that connects the Invoice Reception
 * bounded context with the Approval bounded context.
 *
 * When an invoice is submitted (InvoiceSubmitted event), this policy
 * automatically starts the approval process by dispatching the
 * StartApprovalProcessCommand to the Approval bounded context.
 */
final class StartApprovalProcessListener
{
    public function __construct(
        private StartApprovalProcessAction $startApprovalProcessAction,
    ) {}

    /**
     * Handle the InvoiceSubmitted event.
     */
    public function handle(InvoiceSubmitted $event): void
    {
        Log::info('Policy triggered: Starting approval process for submitted invoice', [
            'invoice_id' => $event->invoiceId,
            'invoice_number' => $event->invoiceNumber,
            'amount' => $event->amount,
            'submitter_id' => $event->submitterId,
            'supervisor_id' => $event->supervisorId,
            'occurred_at' => $event->occurredAt->format('Y-m-d H:i:s'),
        ]);

        $data = new StartApprovalData(
            invoiceId: $event->invoiceId,
            approverId: $event->supervisorId,
        );

        $approval = $this->startApprovalProcessAction->execute($data);

        Log::info('Approval process started successfully', [
            'approval_id' => $approval->getId()->value,
            'invoice_id' => $event->invoiceId,
            'approver_id' => $event->supervisorId,
            'status' => $approval->getStatus()->value,
        ]);
    }
}
