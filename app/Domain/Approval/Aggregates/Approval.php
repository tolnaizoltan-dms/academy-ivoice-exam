<?php

declare(strict_types=1);

namespace App\Domain\Approval\Aggregates;

use App\Domain\Approval\Events\ApprovalProcessStarted;
use App\Domain\Approval\Events\InvoiceApproved;
use App\Domain\Approval\Events\InvoiceRejected;
use App\Domain\Approval\Exceptions\InvalidApprovalException;
use App\Domain\Approval\ValueObjects\ApprovalId;
use App\Domain\Approval\ValueObjects\ApprovalStatus;
use App\Domain\Approval\ValueObjects\ApproverId;
use App\Domain\Shared\AggregateRoot;
use DateTimeImmutable;

/**
 * Approval Aggregate Root
 *
 * Responsible for:
 * - Managing the approval lifecycle of an invoice
 * - Enforcing status transition rules
 * - Recording domain events
 */
final class Approval extends AggregateRoot
{
    private function __construct(
        private readonly ApprovalId $id,
        private readonly string $invoiceId,
        private readonly ApproverId $approverId,
        private ApprovalStatus $status,
        private readonly DateTimeImmutable $startedAt,
        private ?DateTimeImmutable $completedAt = null,
        private ?string $rejectionReason = null,
    ) {}

    /**
     * Factory method: Start a new approval process for an invoice.
     *
     * @throws InvalidApprovalException
     */
    public static function start(
        ApprovalId $id,
        string $invoiceId,
        ApproverId $approverId,
    ): self {
        if (empty($invoiceId)) {
            throw InvalidApprovalException::emptyInvoiceId();
        }

        $startedAt = new DateTimeImmutable;

        $approval = new self(
            id: $id,
            invoiceId: $invoiceId,
            approverId: $approverId,
            status: ApprovalStatus::PENDING,
            startedAt: $startedAt,
        );

        $approval->recordEvent(new ApprovalProcessStarted(
            approvalId: $id->value,
            invoiceId: $invoiceId,
            approverId: $approverId->value,
            occurredAt: $startedAt,
        ));

        return $approval;
    }

    /**
     * Approve the invoice.
     *
     * @throws InvalidApprovalException
     */
    public function approve(): void
    {
        $this->ensurePending();

        $this->status = ApprovalStatus::APPROVED;
        $this->completedAt = new DateTimeImmutable;

        $this->recordEvent(new InvoiceApproved(
            approvalId: $this->id->value,
            invoiceId: $this->invoiceId,
            approverId: $this->approverId->value,
            occurredAt: $this->completedAt,
        ));
    }

    /**
     * Reject the invoice with a reason.
     *
     * @throws InvalidApprovalException
     */
    public function reject(string $reason): void
    {
        $this->ensurePending();

        $this->status = ApprovalStatus::REJECTED;
        $this->rejectionReason = $reason;
        $this->completedAt = new DateTimeImmutable;

        $this->recordEvent(new InvoiceRejected(
            approvalId: $this->id->value,
            invoiceId: $this->invoiceId,
            approverId: $this->approverId->value,
            reason: $reason,
            occurredAt: $this->completedAt,
        ));
    }

    /**
     * Ensure the approval is in pending state before modification.
     *
     * @throws InvalidApprovalException
     */
    private function ensurePending(): void
    {
        match ($this->status) {
            ApprovalStatus::PENDING => null,
            ApprovalStatus::APPROVED => throw InvalidApprovalException::alreadyApproved(),
            ApprovalStatus::REJECTED => throw InvalidApprovalException::alreadyRejected(),
        };
    }

    public function getId(): ApprovalId
    {
        return $this->id;
    }

    public function getInvoiceId(): string
    {
        return $this->invoiceId;
    }

    public function getApproverId(): ApproverId
    {
        return $this->approverId;
    }

    public function getStatus(): ApprovalStatus
    {
        return $this->status;
    }

    public function getStartedAt(): DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function getCompletedAt(): ?DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function getRejectionReason(): ?string
    {
        return $this->rejectionReason;
    }

    /**
     * Reconstitute an Approval from persistence.
     *
     * This method is used by the repository to rebuild the aggregate
     * from stored data. It does NOT record any domain events.
     */
    public static function reconstitute(
        ApprovalId $id,
        string $invoiceId,
        ApproverId $approverId,
        ApprovalStatus $status,
        DateTimeImmutable $startedAt,
        ?DateTimeImmutable $completedAt = null,
        ?string $rejectionReason = null,
    ): self {
        return new self(
            id: $id,
            invoiceId: $invoiceId,
            approverId: $approverId,
            status: $status,
            startedAt: $startedAt,
            completedAt: $completedAt,
            rejectionReason: $rejectionReason,
        );
    }
}
