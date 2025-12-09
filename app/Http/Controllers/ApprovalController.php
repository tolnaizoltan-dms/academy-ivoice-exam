<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Actions\ApproveInvoiceAction;
use App\Application\Actions\RejectInvoiceAction;
use App\Domain\Approval\Exceptions\InvalidApprovalException;
use App\Http\Requests\RejectInvoiceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * API Controller for Approval operations.
 */
class ApprovalController extends Controller
{
    public function __construct(
        private ApproveInvoiceAction $approveInvoiceAction,
        private RejectInvoiceAction $rejectInvoiceAction,
    ) {}

    /**
     * Approve an invoice.
     *
     * PUT /api/v1/approvals/{id}/approve
     */
    public function approve(string $id): JsonResponse
    {
        try {
            $approval = $this->approveInvoiceAction->execute($id);

            Log::info('Invoice approved successfully via API', [
                'approval_id' => $approval->getId()->value,
                'invoice_id' => $approval->getInvoiceId(),
                'status' => $approval->getStatus()->value,
            ]);

            return response()->json([
                'approvalId' => $approval->getId()->value,
                'invoiceId' => $approval->getInvoiceId(),
                'status' => $approval->getStatus()->value,
                'message' => 'Invoice approved successfully.',
            ], 200);

        } catch (InvalidApprovalException $e) {
            Log::warning('Invoice approval failed', [
                'approval_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Approval failed',
                'message' => $e->getMessage(),
            ], 400);

        } catch (\Exception $e) {
            Log::error('Invoice approval failed: Unexpected error', [
                'approval_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Internal server error',
                'message' => 'An unexpected error occurred while approving the invoice.',
            ], 500);
        }
    }

    /**
     * Reject an invoice.
     *
     * PUT /api/v1/approvals/{id}/reject
     */
    public function reject(RejectInvoiceRequest $request, string $id): JsonResponse
    {
        try {
            $reason = $request->validated()['reason'];
            $approval = $this->rejectInvoiceAction->execute($id, $reason);

            Log::info('Invoice rejected successfully via API', [
                'approval_id' => $approval->getId()->value,
                'invoice_id' => $approval->getInvoiceId(),
                'status' => $approval->getStatus()->value,
                'reason' => $reason,
            ]);

            return response()->json([
                'approvalId' => $approval->getId()->value,
                'invoiceId' => $approval->getInvoiceId(),
                'status' => $approval->getStatus()->value,
                'reason' => $approval->getRejectionReason(),
                'message' => 'Invoice rejected successfully.',
            ], 200);

        } catch (InvalidApprovalException $e) {
            Log::warning('Invoice rejection failed', [
                'approval_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Rejection failed',
                'message' => $e->getMessage(),
            ], 400);

        } catch (\Exception $e) {
            Log::error('Invoice rejection failed: Unexpected error', [
                'approval_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Internal server error',
                'message' => 'An unexpected error occurred while rejecting the invoice.',
            ], 500);
        }
    }
}
