<?php

declare(strict_types=1);

use App\Domain\Approval\Aggregates\Approval;
use App\Domain\Approval\Events\ApprovalProcessStarted;
use App\Domain\Approval\Events\InvoiceApproved;
use App\Domain\Approval\Events\InvoiceRejected;
use App\Domain\Approval\Exceptions\InvalidApprovalException;
use App\Domain\Approval\ValueObjects\ApprovalId;
use App\Domain\Approval\ValueObjects\ApprovalStatus;
use App\Domain\Approval\ValueObjects\ApproverId;

describe('Approval Aggregate', function (): void {
    it('can start an approval process', function (): void {
        $approval = Approval::start(
            id: ApprovalId::generate(),
            invoiceId: 'invoice-123',
            approverId: new ApproverId('supervisor-456'),
        );

        expect($approval)->toBeInstanceOf(Approval::class);
        expect($approval->getInvoiceId())->toBe('invoice-123');
        expect($approval->getApproverId()->value)->toBe('supervisor-456');
        expect($approval->getStatus())->toBe(ApprovalStatus::PENDING);
        expect($approval->getStartedAt())->toBeInstanceOf(DateTimeImmutable::class);
        expect($approval->getCompletedAt())->toBeNull();
    });

    it('raises ApprovalProcessStarted event when started', function (): void {
        $approval = Approval::start(
            id: ApprovalId::generate(),
            invoiceId: 'invoice-123',
            approverId: new ApproverId('supervisor-456'),
        );

        $events = $approval->releaseEvents();

        expect($events)->toHaveCount(1);
        expect($events[0])->toBeInstanceOf(ApprovalProcessStarted::class);
        expect($events[0]->invoiceId)->toBe('invoice-123');
        expect($events[0]->approverId)->toBe('supervisor-456');
    });

    it('can approve a pending approval', function (): void {
        $approval = Approval::start(
            id: ApprovalId::generate(),
            invoiceId: 'invoice-123',
            approverId: new ApproverId('supervisor-456'),
        );
        $approval->releaseEvents(); // Clear the start event

        $approval->approve();

        expect($approval->getStatus())->toBe(ApprovalStatus::APPROVED);
        expect($approval->getCompletedAt())->toBeInstanceOf(DateTimeImmutable::class);
    });

    it('raises InvoiceApproved event when approved', function (): void {
        $approval = Approval::start(
            id: ApprovalId::generate(),
            invoiceId: 'invoice-123',
            approverId: new ApproverId('supervisor-456'),
        );
        $approval->releaseEvents();

        $approval->approve();
        $events = $approval->releaseEvents();

        expect($events)->toHaveCount(1);
        expect($events[0])->toBeInstanceOf(InvoiceApproved::class);
        expect($events[0]->invoiceId)->toBe('invoice-123');
    });

    it('can reject a pending approval with reason', function (): void {
        $approval = Approval::start(
            id: ApprovalId::generate(),
            invoiceId: 'invoice-123',
            approverId: new ApproverId('supervisor-456'),
        );
        $approval->releaseEvents();

        $approval->reject('Insufficient documentation');

        expect($approval->getStatus())->toBe(ApprovalStatus::REJECTED);
        expect($approval->getRejectionReason())->toBe('Insufficient documentation');
        expect($approval->getCompletedAt())->toBeInstanceOf(DateTimeImmutable::class);
    });

    it('raises InvoiceRejected event when rejected', function (): void {
        $approval = Approval::start(
            id: ApprovalId::generate(),
            invoiceId: 'invoice-123',
            approverId: new ApproverId('supervisor-456'),
        );
        $approval->releaseEvents();

        $approval->reject('Budget exceeded');
        $events = $approval->releaseEvents();

        expect($events)->toHaveCount(1);
        expect($events[0])->toBeInstanceOf(InvoiceRejected::class);
        expect($events[0]->invoiceId)->toBe('invoice-123');
        expect($events[0]->reason)->toBe('Budget exceeded');
    });

    it('cannot approve an already approved approval', function (): void {
        $approval = Approval::start(
            id: ApprovalId::generate(),
            invoiceId: 'invoice-123',
            approverId: new ApproverId('supervisor-456'),
        );
        $approval->approve();

        $approval->approve();
    })->throws(InvalidApprovalException::class, 'Cannot modify an already approved invoice');

    it('cannot reject an already approved approval', function (): void {
        $approval = Approval::start(
            id: ApprovalId::generate(),
            invoiceId: 'invoice-123',
            approverId: new ApproverId('supervisor-456'),
        );
        $approval->approve();

        $approval->reject('Too late');
    })->throws(InvalidApprovalException::class, 'Cannot modify an already approved invoice');

    it('cannot approve an already rejected approval', function (): void {
        $approval = Approval::start(
            id: ApprovalId::generate(),
            invoiceId: 'invoice-123',
            approverId: new ApproverId('supervisor-456'),
        );
        $approval->reject('Invalid');

        $approval->approve();
    })->throws(InvalidApprovalException::class, 'Cannot modify an already rejected invoice');

    it('cannot reject an already rejected approval', function (): void {
        $approval = Approval::start(
            id: ApprovalId::generate(),
            invoiceId: 'invoice-123',
            approverId: new ApproverId('supervisor-456'),
        );
        $approval->reject('First rejection');

        $approval->reject('Second rejection');
    })->throws(InvalidApprovalException::class, 'Cannot modify an already rejected invoice');

    it('throws exception for empty invoice id', function (): void {
        Approval::start(
            id: ApprovalId::generate(),
            invoiceId: '',
            approverId: new ApproverId('supervisor-456'),
        );
    })->throws(InvalidApprovalException::class, 'Invoice ID cannot be empty');

    it('preserves approval id', function (): void {
        $id = ApprovalId::generate();

        $approval = Approval::start(
            id: $id,
            invoiceId: 'invoice-123',
            approverId: new ApproverId('supervisor-456'),
        );

        expect($approval->getId()->equals($id))->toBeTrue();
    });
});
