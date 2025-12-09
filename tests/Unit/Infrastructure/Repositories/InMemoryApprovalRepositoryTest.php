<?php

declare(strict_types=1);

use App\Domain\Approval\Aggregates\Approval;
use App\Domain\Approval\ValueObjects\ApprovalId;
use App\Domain\Approval\ValueObjects\ApproverId;
use App\Infrastructure\Repositories\InMemoryApprovalRepository;

describe('InMemoryApprovalRepository', function (): void {
    beforeEach(function (): void {
        $this->repository = new InMemoryApprovalRepository;
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
        $found = $this->repository->findById(new ApprovalId('non-existent'));

        expect($found)->toBeNull();
    });

    it('can find approval by invoice id', function (): void {
        $approval = Approval::start(
            id: ApprovalId::generate(),
            invoiceId: 'invoice-123',
            approverId: new ApproverId('supervisor-456'),
        );

        $this->repository->save($approval);
        $found = $this->repository->findByInvoiceId('invoice-123');

        expect($found)->not->toBeNull();
        expect($found->getInvoiceId())->toBe('invoice-123');
    });

    it('returns null when finding by non-existent invoice id', function (): void {
        $found = $this->repository->findByInvoiceId('non-existent');

        expect($found)->toBeNull();
    });

    it('generates unique identities', function (): void {
        $id1 = $this->repository->nextIdentity();
        $id2 = $this->repository->nextIdentity();

        expect($id1->equals($id2))->toBeFalse();
    });

    it('can retrieve all approvals', function (): void {
        $approval1 = Approval::start(
            id: ApprovalId::generate(),
            invoiceId: 'invoice-1',
            approverId: new ApproverId('supervisor-456'),
        );

        $approval2 = Approval::start(
            id: ApprovalId::generate(),
            invoiceId: 'invoice-2',
            approverId: new ApproverId('supervisor-456'),
        );

        $this->repository->save($approval1);
        $this->repository->save($approval2);

        expect($this->repository->all())->toHaveCount(2);
    });

    it('can clear all approvals', function (): void {
        $approval = Approval::start(
            id: ApprovalId::generate(),
            invoiceId: 'invoice-123',
            approverId: new ApproverId('supervisor-456'),
        );

        $this->repository->save($approval);
        $this->repository->clear();

        expect($this->repository->all())->toHaveCount(0);
    });
});
