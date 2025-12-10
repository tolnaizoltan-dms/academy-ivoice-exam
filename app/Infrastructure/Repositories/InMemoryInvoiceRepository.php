<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Domain\InvoiceReception\Aggregates\Invoice;
use App\Domain\InvoiceReception\Contracts\InvoiceRepositoryInterface;
use App\Domain\InvoiceReception\ValueObjects\InvoiceId;
use App\Domain\InvoiceReception\ValueObjects\InvoiceNumber;

/**
 * In-memory implementation of Invoice Repository.
 *
 * Useful for testing and demonstration purposes.
 * Can be replaced with Eloquent implementation for persistence.
 */
final class InMemoryInvoiceRepository implements InvoiceRepositoryInterface
{
    /** @var array<string, Invoice> */
    private array $invoices = [];

    public function save(Invoice $invoice): void
    {
        $this->invoices[$invoice->getId()->value] = $invoice;
    }

    public function findById(InvoiceId $id): ?Invoice
    {
        return $this->invoices[$id->value] ?? null;
    }

    public function existsByNumber(InvoiceNumber $number): bool
    {
        foreach ($this->invoices as $invoice) {
            if ($invoice->getNumber()->equals($number)) {
                return true;
            }
        }

        return false;
    }

    public function nextIdentity(): InvoiceId
    {
        return InvoiceId::generate();
    }

    /**
     * Get all stored invoices (for testing/debugging).
     *
     * @return array<string, Invoice>
     */
    public function all(): array
    {
        return $this->invoices;
    }

    /**
     * Clear all stored invoices (for testing).
     */
    public function clear(): void
    {
        $this->invoices = [];
    }
}
