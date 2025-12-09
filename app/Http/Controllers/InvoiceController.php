<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Actions\SubmitInvoiceAction;
use App\Application\DTOs\SubmitInvoiceData;
use App\Domain\InvoiceReception\Exceptions\InvalidInvoiceException;
use App\Http\Requests\SubmitInvoiceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * API Controller for Invoice operations.
 */
class InvoiceController extends Controller
{
    public function __construct(
        private SubmitInvoiceAction $submitInvoiceAction,
    ) {}

    /**
     * Submit a new invoice.
     *
     * POST /api/v1/invoices
     */
    public function store(SubmitInvoiceRequest $request): JsonResponse
    {
        try {
            $data = SubmitInvoiceData::fromArray($request->validated());

            $invoice = $this->submitInvoiceAction->execute($data);

            Log::info('Invoice submitted successfully via API', [
                'invoice_id' => $invoice->getId()->value,
                'invoice_number' => $invoice->getNumber()->value,
            ]);

            return response()->json([
                'invoiceId' => $invoice->getId()->value,
                'invoiceNumber' => $invoice->getNumber()->value,
                'amount' => $invoice->getAmount()->getValue(),
                'status' => 'submitted',
                'message' => 'Invoice submitted successfully. Approval process has been started.',
            ], 201);

        } catch (InvalidInvoiceException $e) {
            Log::warning('Invoice submission failed: Invalid invoice data', [
                'error' => $e->getMessage(),
                'request_data' => $request->validated(),
            ]);

            return response()->json([
                'error' => 'Invalid invoice data',
                'message' => $e->getMessage(),
            ], 400);

        } catch (\Exception $e) {
            Log::error('Invoice submission failed: Unexpected error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Internal server error',
                'message' => 'An unexpected error occurred while submitting the invoice.',
            ], 500);
        }
    }
}
