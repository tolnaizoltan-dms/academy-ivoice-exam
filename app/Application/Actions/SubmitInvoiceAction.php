<?php

declare(strict_types=1);

namespace App\Application\Actions;

use App\Application\DTOs\SubmitInvoiceData;
use App\Domain\InvoiceReception\Aggregates\Invoice;
use App\Domain\InvoiceReception\Contracts\InvoiceRepositoryInterface;
use App\Domain\InvoiceReception\Exceptions\InvalidInvoiceException;
use App\Domain\InvoiceReception\ValueObjects\Amount;
use App\Domain\InvoiceReception\ValueObjects\InvoiceNumber;
use App\Domain\InvoiceReception\ValueObjects\SubmitterId;

/**
 * Application Action: Submit an invoice.
 *
 * This is the Command Handler for the "Submit Invoice" use case.
 * It orchestrates the domain logic and handles persistence.
 */
final class SubmitInvoiceAction
{
    public function __construct(
        private InvoiceRepositoryInterface $invoiceRepository,
    ) {}

    /**
     * Execute the submit invoice action.
     *
     * @throws \App\Domain\InvoiceReception\Exceptions\InvalidInvoiceException
     */
    public function execute(SubmitInvoiceData $data): Invoice
    {
        // Create Value Objects from raw data (validates invariants)
        $invoiceNumber = new InvoiceNumber($data->invoiceNumber);
        $amount = new Amount($data->amount);
        $submitterId = new SubmitterId($data->submitterId);

        // Check for duplicate invoice number
        if ($this->invoiceRepository->existsByNumber($invoiceNumber)) {
            throw InvalidInvoiceException::duplicateInvoiceNumber($invoiceNumber->value);
        }

        // Create Invoice aggregate via factory method
        $invoice = Invoice::submit(
            id: $this->invoiceRepository->nextIdentity(),
            number: $invoiceNumber,
            amount: $amount,
            submitterId: $submitterId,
            supervisorId: $data->supervisorId,
        );

        // Persist the invoice
        $this->invoiceRepository->save($invoice);

        // Dispatch domain events via Laravel Event system
        foreach ($invoice->releaseEvents() as $event) {
            event($event);
        }

        return $invoice;
    }
}
