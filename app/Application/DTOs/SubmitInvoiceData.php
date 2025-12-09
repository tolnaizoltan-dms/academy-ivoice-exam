<?php

declare(strict_types=1);

namespace App\Application\DTOs;

/**
 * Data Transfer Object for submitting an invoice.
 */
final readonly class SubmitInvoiceData
{
    public function __construct(
        public string $invoiceNumber,
        public float $amount,
        public string $submitterId,
        public string $supervisorId,
    ) {}

    /**
     * Create from request array.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            invoiceNumber: $data['invoiceNumber'],
            amount: (float) $data['amount'],
            submitterId: $data['submitterId'],
            supervisorId: $data['supervisorId'],
        );
    }
}
