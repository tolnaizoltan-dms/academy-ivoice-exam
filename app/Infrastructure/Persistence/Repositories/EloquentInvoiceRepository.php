<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\InvoiceReception\Aggregates\Invoice;
use App\Domain\InvoiceReception\Contracts\InvoiceRepositoryInterface;
use App\Domain\InvoiceReception\ValueObjects\Amount;
use App\Domain\InvoiceReception\ValueObjects\InvoiceId;
use App\Domain\InvoiceReception\ValueObjects\InvoiceNumber;
use App\Domain\InvoiceReception\ValueObjects\SubmitterId;
use App\Infrastructure\Persistence\Models\InvoiceModel;
use DateTimeImmutable;

/**
 * Eloquent implementation of Invoice Repository.
 *
 * This repository translates between the Domain Aggregate (Invoice)
 * and the Eloquent Model (InvoiceModel) for persistence.
 */
final class EloquentInvoiceRepository implements InvoiceRepositoryInterface
{
    public function save(Invoice $invoice): void
    {
        InvoiceModel::updateOrCreate(
            ['id' => $invoice->getId()->value],
            [
                'invoice_number' => $invoice->getNumber()->value,
                'amount' => $invoice->getAmount()->getValue(),
                'submitter_id' => $invoice->getSubmitterId()->value,
                'supervisor_id' => $invoice->getSupervisorId(),
                'submitted_at' => $invoice->getSubmittedAt(),
            ]
        );
    }

    public function findById(InvoiceId $id): ?Invoice
    {
        $model = InvoiceModel::find($id->value);

        if ($model === null) {
            return null;
        }

        return $this->toDomain($model);
    }

    public function nextIdentity(): InvoiceId
    {
        return InvoiceId::generate();
    }

    /**
     * Map Eloquent Model to Domain Aggregate.
     */
    private function toDomain(InvoiceModel $model): Invoice
    {
        return Invoice::reconstitute(
            id: InvoiceId::fromString($model->id),
            number: InvoiceNumber::fromString($model->invoice_number),
            amount: Amount::fromFloat((float) $model->amount),
            submitterId: SubmitterId::fromString($model->submitter_id),
            supervisorId: $model->supervisor_id,
            submittedAt: new DateTimeImmutable($model->submitted_at->toDateTimeString()),
        );
    }
}
