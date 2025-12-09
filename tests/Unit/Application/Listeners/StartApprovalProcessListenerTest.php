<?php

declare(strict_types=1);

use App\Application\Actions\StartApprovalProcessAction;
use App\Application\Listeners\StartApprovalProcessListener;
use App\Domain\Approval\Events\ApprovalProcessStarted;
use App\Domain\Approval\ValueObjects\ApprovalStatus;
use App\Domain\InvoiceReception\Events\InvoiceSubmitted;
use App\Infrastructure\Repositories\InMemoryApprovalRepository;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

describe('StartApprovalProcessListener (Policy)', function () {
    beforeEach(function () {
        $this->approvalRepository = new InMemoryApprovalRepository;
        $this->action = new StartApprovalProcessAction($this->approvalRepository);
        $this->listener = new StartApprovalProcessListener($this->action);
    });

    it('starts approval process when invoice is submitted', function () {
        Event::fake([ApprovalProcessStarted::class]);

        $event = new InvoiceSubmitted(
            invoiceId: 'invoice-123',
            invoiceNumber: 'INV-2025-0001',
            amount: 1500.50,
            submitterId: 'user-123',
            supervisorId: 'supervisor-456',
            occurredAt: new DateTimeImmutable,
        );

        $this->listener->handle($event);

        // Verify approval was created
        $approval = $this->approvalRepository->findByInvoiceId('invoice-123');
        expect($approval)->not->toBeNull();
        expect($approval->getInvoiceId())->toBe('invoice-123');
        expect($approval->getApproverId()->value)->toBe('supervisor-456');
        expect($approval->getStatus())->toBe(ApprovalStatus::PENDING);
    });

    it('dispatches ApprovalProcessStarted event', function () {
        Event::fake([ApprovalProcessStarted::class]);

        $event = new InvoiceSubmitted(
            invoiceId: 'invoice-123',
            invoiceNumber: 'INV-2025-0001',
            amount: 1500.50,
            submitterId: 'user-123',
            supervisorId: 'supervisor-456',
            occurredAt: new DateTimeImmutable,
        );

        $this->listener->handle($event);

        Event::assertDispatched(ApprovalProcessStarted::class, function ($dispatched) {
            return $dispatched->invoiceId === 'invoice-123'
                && $dispatched->approverId === 'supervisor-456';
        });
    });

    it('logs policy execution', function () {
        Event::fake([ApprovalProcessStarted::class]);
        Log::shouldReceive('info')
            ->once()
            ->with('Policy triggered: Starting approval process for submitted invoice', \Mockery::type('array'));

        Log::shouldReceive('info')
            ->once()
            ->with('Approval process started successfully', \Mockery::type('array'));

        $event = new InvoiceSubmitted(
            invoiceId: 'invoice-123',
            invoiceNumber: 'INV-2025-0001',
            amount: 1500.50,
            submitterId: 'user-123',
            supervisorId: 'supervisor-456',
            occurredAt: new DateTimeImmutable,
        );

        $this->listener->handle($event);
    });

    it('uses supervisor as approver', function () {
        Event::fake([ApprovalProcessStarted::class]);

        $event = new InvoiceSubmitted(
            invoiceId: 'invoice-999',
            invoiceNumber: 'INV-2025-0002',
            amount: 2000.00,
            submitterId: 'employee-1',
            supervisorId: 'manager-1',
            occurredAt: new DateTimeImmutable,
        );

        $this->listener->handle($event);

        $approval = $this->approvalRepository->findByInvoiceId('invoice-999');
        expect($approval->getApproverId()->value)->toBe('manager-1');
    });
});
