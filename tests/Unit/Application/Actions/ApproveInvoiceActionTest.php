<?php

declare(strict_types=1);

use App\Application\Actions\ApproveInvoiceAction;
use App\Domain\Approval\Aggregates\Approval;
use App\Domain\Approval\Events\InvoiceApproved;
use App\Domain\Approval\Exceptions\InvalidApprovalException;
use App\Domain\Approval\ValueObjects\ApprovalId;
use App\Domain\Approval\ValueObjects\ApprovalStatus;
use App\Domain\Approval\ValueObjects\ApproverId;
use App\Infrastructure\Repositories\InMemoryApprovalRepository;
use Illuminate\Support\Facades\Event;

describe('ApproveInvoiceAction', function (): void {
    beforeEach(function (): void {
        $this->repository = new InMemoryApprovalRepository;
        $this->action = new ApproveInvoiceAction($this->repository);
    });

    it('approves a pending approval', function (): void {
        Event::fake();

        // Create a pending approval
        $approval = Approval::start(
            id: ApprovalId::generate(),
            invoiceId: 'invoice-123',
            approverId: new ApproverId('supervisor-456'),
        );
        $approval->releaseEvents(); // Clear start event
        $this->repository->save($approval);

        // Execute approve action
        $result = $this->action->execute($approval->getId()->value);

        expect($result->getStatus())->toBe(ApprovalStatus::APPROVED);
        expect($result->getCompletedAt())->not->toBeNull();
    });

    it('dispatches InvoiceApproved event', function (): void {
        Event::fake([InvoiceApproved::class]);

        $approval = Approval::start(
            id: ApprovalId::generate(),
            invoiceId: 'invoice-123',
            approverId: new ApproverId('supervisor-456'),
        );
        $approval->releaseEvents();
        $this->repository->save($approval);

        $this->action->execute($approval->getId()->value);

        Event::assertDispatched(InvoiceApproved::class, function ($event) {
            return $event->invoiceId === 'invoice-123';
        });
    });

    it('throws exception for non-existent approval', function (): void {
        Event::fake();

        $this->action->execute('non-existent-id');
    })->throws(InvalidApprovalException::class, 'Approval with ID non-existent-id not found');

    it('throws exception when already approved', function (): void {
        Event::fake();

        $approval = Approval::start(
            id: ApprovalId::generate(),
            invoiceId: 'invoice-123',
            approverId: new ApproverId('supervisor-456'),
        );
        $approval->approve();
        $approval->releaseEvents();
        $this->repository->save($approval);

        $this->action->execute($approval->getId()->value);
    })->throws(InvalidApprovalException::class, 'Cannot modify an already approved invoice');

    it('throws exception when already rejected', function (): void {
        Event::fake();

        $approval = Approval::start(
            id: ApprovalId::generate(),
            invoiceId: 'invoice-123',
            approverId: new ApproverId('supervisor-456'),
        );
        $approval->reject('Some reason');
        $approval->releaseEvents();
        $this->repository->save($approval);

        $this->action->execute($approval->getId()->value);
    })->throws(InvalidApprovalException::class, 'Cannot modify an already rejected invoice');
});
