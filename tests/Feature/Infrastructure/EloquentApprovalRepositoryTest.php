<?php

declare(strict_types=1);

use App\Domain\Approval\Aggregates\Approval;
use App\Domain\Approval\ValueObjects\ApprovalId;
use App\Domain\Approval\ValueObjects\ApproverId;
use App\Infrastructure\Persistence\Repositories\EloquentApprovalRepository;

describe('EloquentApprovalRepository', function (): void {
    beforeEach(function (): void {
        $this->repository = new EloquentApprovalRepository();
    });

    it('can save and retrieve an approval', function (): void {
        $approval = Approval::start(
            id: ApprovalId::generate(),
            invoiceId: 'invoice-123',
            approverId: new ApproverId('supervisor-456'),
        );

        $this->repository->save($approval);
        $found = $this->repository->findById($approval->getId());

        expect($found)->not->toBeNull();
        expect($found->getId()->equals($approval->getId()))->toBeTrue();
        expect($found->getInvoiceId())->toBe('invoice-123');
    });

    it('returns null for non-existent approval', function (): void {
        $found = $this->repository->findById(ApprovalId::generate());

        expect($found)->toBeNull();
    });

    it('can find approval by invoice id', function (): void {
        $approval = Approval::start(
            id: ApprovalId::generate(),
            invoiceId: 'invoice-456',
            approverId: new ApproverId('supervisor-789'),
        );

        $this->repository->save($approval);
        $found = $this->repository->findByInvoiceId('invoice-456');

        expect($found)->not->toBeNull();
        expect($found->getInvoiceId())->toBe('invoice-456');
    });

    it('returns null when finding by non-existent invoice id', function (): void {
        $found = $this->repository->findByInvoiceId('non-existent-invoice-id');

        expect($found)->toBeNull();
    });

    it('generates unique identities', function (): void {
        $id1 = $this->repository->nextIdentity();
        $id2 = $this->repository->nextIdentity();

        expect($id1->equals($id2))->toBeFalse();
    });

    it('updates existing approval on save', function (): void {
        $approval = Approval::start(
            id: ApprovalId::generate(),
            invoiceId: 'invoice-789',
            approverId: new ApproverId('supervisor-123'),
        );

        $this->repository->save($approval);

        // Approve the invoice
        $approval->approve();
        $approval->releaseEvents();
        $this->repository->save($approval);

        $found = $this->repository->findById($approval->getId());

        expect($found)->not->toBeNull();
        expect($found->getStatus()->value)->toBe('approved');
        expect($found->getCompletedAt())->not->toBeNull();
    });

    it('persists rejection reason', function (): void {
        $approval = Approval::start(
            id: ApprovalId::generate(),
            invoiceId: 'invoice-999',
            approverId: new ApproverId('supervisor-111'),
        );

        $this->repository->save($approval);

        // Reject the invoice
        $approval->reject('Insufficient documentation');
        $approval->releaseEvents();
        $this->repository->save($approval);

        $found = $this->repository->findById($approval->getId());

        expect($found)->not->toBeNull();
        expect($found->getStatus()->value)->toBe('rejected');
        expect($found->getRejectionReason())->toBe('Insufficient documentation');
    });
});
