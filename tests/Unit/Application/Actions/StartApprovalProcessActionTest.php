<?php

declare(strict_types=1);

use App\Application\Actions\StartApprovalProcessAction;
use App\Application\DTOs\StartApprovalData;
use App\Domain\Approval\Aggregates\Approval;
use App\Domain\Approval\Events\ApprovalProcessStarted;
use App\Domain\Approval\Exceptions\InvalidApprovalException;
use App\Domain\Approval\ValueObjects\ApprovalStatus;
use App\Infrastructure\Repositories\InMemoryApprovalRepository;
use Illuminate\Support\Facades\Event;

describe('StartApprovalProcessAction', function () {
    beforeEach(function () {
        $this->repository = new InMemoryApprovalRepository;
        $this->action = new StartApprovalProcessAction($this->repository);
    });

    it('starts an approval process', function () {
        Event::fake();

        $data = new StartApprovalData(
            invoiceId: 'invoice-123',
            approverId: 'supervisor-456',
        );

        $approval = $this->action->execute($data);

        expect($approval)->toBeInstanceOf(Approval::class);
        expect($approval->getInvoiceId())->toBe('invoice-123');
        expect($approval->getApproverId()->value)->toBe('supervisor-456');
        expect($approval->getStatus())->toBe(ApprovalStatus::PENDING);
    });

    it('persists the approval to repository', function () {
        Event::fake();

        $data = new StartApprovalData(
            invoiceId: 'invoice-123',
            approverId: 'supervisor-456',
        );

        $approval = $this->action->execute($data);
        $found = $this->repository->findById($approval->getId());

        expect($found)->not->toBeNull();
        expect($found->getId()->equals($approval->getId()))->toBeTrue();
    });

    it('can find approval by invoice id', function () {
        Event::fake();

        $data = new StartApprovalData(
            invoiceId: 'invoice-123',
            approverId: 'supervisor-456',
        );

        $this->action->execute($data);
        $found = $this->repository->findByInvoiceId('invoice-123');

        expect($found)->not->toBeNull();
        expect($found->getInvoiceId())->toBe('invoice-123');
    });

    it('dispatches ApprovalProcessStarted event', function () {
        Event::fake([ApprovalProcessStarted::class]);

        $data = new StartApprovalData(
            invoiceId: 'invoice-123',
            approverId: 'supervisor-456',
        );

        $this->action->execute($data);

        Event::assertDispatched(ApprovalProcessStarted::class, function ($event) {
            return $event->invoiceId === 'invoice-123'
                && $event->approverId === 'supervisor-456';
        });
    });

    it('throws exception for empty approver id', function () {
        Event::fake();

        $data = new StartApprovalData(
            invoiceId: 'invoice-123',
            approverId: '',
        );

        $this->action->execute($data);
    })->throws(InvalidApprovalException::class);

    it('throws exception for empty invoice id', function () {
        Event::fake();

        $data = new StartApprovalData(
            invoiceId: '',
            approverId: 'supervisor-456',
        );

        $this->action->execute($data);
    })->throws(InvalidApprovalException::class);
});
