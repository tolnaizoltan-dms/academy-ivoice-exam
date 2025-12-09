<?php

declare(strict_types=1);

namespace App\Domain\InvoiceReception\Aggregates;

use App\Domain\InvoiceReception\Events\InvoiceSubmitted;
use App\Domain\InvoiceReception\Exceptions\InvalidInvoiceException;
use App\Domain\InvoiceReception\ValueObjects\Amount;
use App\Domain\InvoiceReception\ValueObjects\InvoiceId;
use App\Domain\InvoiceReception\ValueObjects\InvoiceNumber;
use App\Domain\InvoiceReception\ValueObjects\SubmitterId;
use App\Domain\Shared\AggregateRoot;
use DateTimeImmutable;

/**
 * Invoice Aggregate Root
 *
 * Responsible for:
 * - Creating valid invoices from raw data
 * - Enforcing business rules (invariants)
 * - Recording domain events
 */
final class Invoice extends AggregateRoot
{
    private function __construct(
        private readonly InvoiceId $id,
        private readonly InvoiceNumber $number,
        private readonly Amount $amount,
        private readonly SubmitterId $submitterId,
        private readonly string $supervisorId,
        private readonly DateTimeImmutable $submittedAt,
    ) {}

    /**
     * Factory method: Submit a new invoice.
     *
     * @throws InvalidInvoiceException
     */
    public static function submit(
        InvoiceId $id,
        InvoiceNumber $number,
        Amount $amount,
        SubmitterId $submitterId,
        string $supervisorId,
    ): self {
        if ($supervisorId === '' || $supervisorId === '0') {
            throw InvalidInvoiceException::emptySupervisorId();
        }

        $submittedAt = new DateTimeImmutable;

        $invoice = new self(
            id: $id,
            number: $number,
            amount: $amount,
            submitterId: $submitterId,
            supervisorId: $supervisorId,
            submittedAt: $submittedAt,
        );

        $invoice->recordEvent(new InvoiceSubmitted(
            invoiceId: $id->value,
            invoiceNumber: $number->value,
            amount: $amount->getValue(),
            submitterId: $submitterId->value,
            supervisorId: $supervisorId,
            occurredAt: $submittedAt,
        ));

        return $invoice;
    }

    public function getId(): InvoiceId
    {
        return $this->id;
    }

    public function getNumber(): InvoiceNumber
    {
        return $this->number;
    }

    public function getAmount(): Amount
    {
        return $this->amount;
    }

    public function getSubmitterId(): SubmitterId
    {
        return $this->submitterId;
    }

    public function getSupervisorId(): string
    {
        return $this->supervisorId;
    }

    public function getSubmittedAt(): DateTimeImmutable
    {
        return $this->submittedAt;
    }

    /**
     * Reconstitute an Invoice from persistence.
     *
     * This method is used by the repository to rebuild the aggregate
     * from stored data. It does NOT record any domain events.
     */
    public static function reconstitute(
        InvoiceId $id,
        InvoiceNumber $number,
        Amount $amount,
        SubmitterId $submitterId,
        string $supervisorId,
        DateTimeImmutable $submittedAt,
    ): self {
        return new self(
            id: $id,
            number: $number,
            amount: $amount,
            submitterId: $submitterId,
            supervisorId: $supervisorId,
            submittedAt: $submittedAt,
        );
    }
}
