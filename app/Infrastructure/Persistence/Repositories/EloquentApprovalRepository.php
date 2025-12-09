<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Approval\Aggregates\Approval;
use App\Domain\Approval\Contracts\ApprovalRepositoryInterface;
use App\Domain\Approval\ValueObjects\ApprovalId;
use App\Domain\Approval\ValueObjects\ApprovalStatus;
use App\Domain\Approval\ValueObjects\ApproverId;
use App\Infrastructure\Persistence\Models\ApprovalModel;
use DateTimeImmutable;

/**
 * Eloquent implementation of Approval Repository.
 *
 * This repository translates between the Domain Aggregate (Approval)
 * and the Eloquent Model (ApprovalModel) for persistence.
 */
final class EloquentApprovalRepository implements ApprovalRepositoryInterface
{
    public function save(Approval $approval): void
    {
        ApprovalModel::updateOrCreate(
            ['id' => $approval->getId()->value],
            [
                'invoice_id' => $approval->getInvoiceId(),
                'approver_id' => $approval->getApproverId()->value,
                'status' => $approval->getStatus()->value,
                'rejection_reason' => $approval->getRejectionReason(),
                'started_at' => $approval->getStartedAt(),
                'completed_at' => $approval->getCompletedAt(),
            ]
        );
    }

    public function findById(ApprovalId $id): ?Approval
    {
        $model = ApprovalModel::find($id->value);

        if ($model === null) {
            return null;
        }

        return $this->toDomain($model);
    }

    public function findByInvoiceId(string $invoiceId): ?Approval
    {
        $model = ApprovalModel::where('invoice_id', $invoiceId)->first();

        if ($model === null) {
            return null;
        }

        return $this->toDomain($model);
    }

    public function nextIdentity(): ApprovalId
    {
        return ApprovalId::generate();
    }

    /**
     * Map Eloquent Model to Domain Aggregate.
     */
    private function toDomain(ApprovalModel $model): Approval
    {
        return Approval::reconstitute(
            id: ApprovalId::fromString($model->id),
            invoiceId: $model->invoice_id,
            approverId: ApproverId::fromString($model->approver_id),
            status: ApprovalStatus::from($model->status),
            startedAt: new DateTimeImmutable($model->started_at->toDateTimeString()),
            completedAt: $model->completed_at
                ? new DateTimeImmutable($model->completed_at->toDateTimeString())
                : null,
            rejectionReason: $model->rejection_reason,
        );
    }
}
