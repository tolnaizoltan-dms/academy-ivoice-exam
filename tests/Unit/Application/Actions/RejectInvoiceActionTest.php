<?php

declare(strict_types=1);

use App\Application\Actions\RejectInvoiceAction;
use App\Domain\Approval\Aggregates\Approval;
use App\Domain\Approval\Events\InvoiceRejected;
use App\Domain\Approval\Exceptions\InvalidApprovalException;
use App\Domain\Approval\ValueObjects\ApprovalId;
use App\Domain\Approval\ValueObjects\ApprovalStatus;
use App\Domain\Approval\ValueObjects\ApproverId;
use App\Infrastructure\Repositories\InMemoryApprovalRepository;
use Illuminate\Support\Facades\Event;

describe('RejectInvoiceAction', function () {
    beforeEach(function () {
        $this->repository = new InMemoryApprovalRepository;
        $this->action = new RejectInvoiceAction($this->repository);
    });

    it('rejects a pending approval with reason', function () {
        Event::fake();

        // Create a pending approval
        $approval = Approval::start(
            id: ApprovalId::generate(),
            invoiceId: 'invoice-123',
            approverId: new ApproverId('supervisor-456'),
        );
        $approval->releaseEvents(); // Clear start event
        $this->repository->save($approval);

        // Execute reject action
        $result = $this->action->execute($approval->getId()->value, 'Insufficient documentation');

        expect($result->getStatus())->toBe(ApprovalStatus::REJECTED);
        expect($result->getRejectionReason())->toBe('Insufficient documentation');
        expect($result->getCompletedAt())->not->toBeNull();
    });

    it('dispatches InvoiceRejected event', function () {
        Event::fake([InvoiceRejected::class]);

        $approval = Approval::start(
            id: ApprovalId::generate(),
            invoiceId: 'invoice-123',
            approverId: new ApproverId('supervisor-456'),
        );
        $approval->releaseEvents();
        $this->repository->save($approval);

        $this->action->execute($approval->getId()->value, 'Budget exceeded');

        Event::assertDispatched(InvoiceRejected::class, function ($event) {
            return $event->invoiceId === 'invoice-123'
                && $event->reason === 'Budget exceeded';
        });
    });

    it('throws exception for non-existent approval', function () {
        Event::fake();

        $this->action->execute('non-existent-id', 'Some reason');
    })->throws(InvalidApprovalException::class, 'Approval with ID non-existent-id not found');

    it('throws exception when already approved', function () {
        Event::fake();

        $approval = Approval::start(
            id: ApprovalId::generate(),
            invoiceId: 'invoice-123',
            approverId: new ApproverId('supervisor-456'),
        );
        $approval->approve();
        $approval->releaseEvents();
        $this->repository->save($approval);

        $this->action->execute($approval->getId()->value, 'Too late');
    })->throws(InvalidApprovalException::class, 'Cannot modify an already approved invoice');

    it('throws exception when already rejected', function () {
        Event::fake();

        $approval = Approval::start(
            id: ApprovalId::generate(),
            invoiceId: 'invoice-123',
            approverId: new ApproverId('supervisor-456'),
        );
        $approval->reject('First reason');
        $approval->releaseEvents();
        $this->repository->save($approval);

        $this->action->execute($approval->getId()->value, 'Second reason');
    })->throws(InvalidApprovalException::class, 'Cannot modify an already rejected invoice');
});
