<?php

declare(strict_types=1);

namespace App\Domain\InvoiceReception\Contracts;

use App\Domain\InvoiceReception\Aggregates\Invoice;
use App\Domain\InvoiceReception\ValueObjects\InvoiceId;

/**
 * Repository contract for Invoice aggregate persistence.
 */
interface InvoiceRepositoryInterface
{
    /**
     * Persist an invoice to the repository.
     */
    public function save(Invoice $invoice): void;

    /**
     * Find an invoice by its ID.
     */
    public function findById(InvoiceId $id): ?Invoice;

    /**
     * Generate a new unique identity for an invoice.
     */
    public function nextIdentity(): InvoiceId;
}
