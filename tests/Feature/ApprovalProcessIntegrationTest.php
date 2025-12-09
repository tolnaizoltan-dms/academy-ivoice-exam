<?php

declare(strict_types=1);

use App\Domain\Approval\Contracts\ApprovalRepositoryInterface;
use App\Domain\Approval\Events\ApprovalProcessStarted;
use App\Domain\Approval\ValueObjects\ApprovalStatus;
use App\Domain\InvoiceReception\Contracts\InvoiceRepositoryInterface;
use App\Domain\InvoiceReception\Events\InvoiceSubmitted;
use App\Domain\InvoiceReception\ValueObjects\InvoiceId;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

use function Pest\Laravel\postJson;

describe('Approval Process Integration Test (Vertical Slice)', function () {
    /**
     * This test verifies the complete vertical slice:
     *
     * 1. API receives POST /api/v1/invoices
     * 2. InvoiceController creates Invoice aggregate via SubmitInvoiceAction
     * 3. Invoice aggregate raises InvoiceSubmitted event
     * 4. Policy (StartApprovalProcessListener) catches the event
     * 5. Policy triggers StartApprovalProcessAction
     * 6. Approval aggregate is created with PENDING status
     * 7. Approval aggregate raises ApprovalProcessStarted event
     * 8. All events are logged for traceability
     */
    it('completes the full approval process flow', function () {
        // Arrange: Get fresh repository instances
        $invoiceRepository = app(InvoiceRepositoryInterface::class);
        $approvalRepository = app(ApprovalRepositoryInterface::class);

        // Act: Submit invoice via API (without event faking to test real flow)
        $response = postJson('/api/v1/invoices', [
            'invoiceNumber' => 'INV-2025-0001',
            'amount' => 15000.50,
            'submitterId' => '550e8400-e29b-41d4-a716-446655440000',
            'supervisorId' => '550e8400-e29b-41d4-a716-446655440001',
        ]);

        // Assert HTTP Response
        $response->assertStatus(201);
        $invoiceId = $response->json('invoiceId');

        // Assert Invoice was created and persisted
        $invoice = $invoiceRepository->findById(InvoiceId::fromString($invoiceId));
        expect($invoice)->not->toBeNull();
        expect($invoice->getNumber()->value)->toBe('INV-2025-0001');
        expect($invoice->getAmount()->getValue())->toBe(15000.50);

        // Assert Approval was created via Policy (automatic)
        $approval = $approvalRepository->findByInvoiceId($invoiceId);
        expect($approval)->not->toBeNull();
        expect($approval->getInvoiceId())->toBe($invoiceId);
        expect($approval->getApproverId()->value)->toBe('550e8400-e29b-41d4-a716-446655440001');
        expect($approval->getStatus())->toBe(ApprovalStatus::PENDING);
    });

    it('creates pending approval with supervisor as approver', function () {
        $approvalRepository = app(ApprovalRepositoryInterface::class);

        $response = postJson('/api/v1/invoices', [
            'invoiceNumber' => 'INV-2025-0002',
            'amount' => 5000.00,
            'submitterId' => 'aaaaaaaa-1111-1111-1111-111111111111',
            'supervisorId' => 'bbbbbbbb-2222-2222-2222-222222222222',
        ]);

        $invoiceId = $response->json('invoiceId');
        $approval = $approvalRepository->findByInvoiceId($invoiceId);

        // Supervisor becomes the approver
        expect($approval->getApproverId()->value)->toBe('bbbbbbbb-2222-2222-2222-222222222222');
        expect($approval->getStatus())->toBe(ApprovalStatus::PENDING);
    });

    it('logs the policy execution', function () {
        // Arrange: Mock the Log facade
        Log::shouldReceive('info')
            ->once()
            ->with('Policy triggered: Starting approval process for submitted invoice', \Mockery::type('array'));

        Log::shouldReceive('info')
            ->once()
            ->with('Approval process started successfully', \Mockery::type('array'));

        Log::shouldReceive('info')
            ->once()
            ->with('Invoice submitted successfully via API', \Mockery::type('array'));

        // Act
        postJson('/api/v1/invoices', [
            'invoiceNumber' => 'INV-2025-0003',
            'amount' => 3000.00,
            'submitterId' => '550e8400-e29b-41d4-a716-446655440000',
            'supervisorId' => '550e8400-e29b-41d4-a716-446655440001',
        ]);
    });

    it('triggers both domain events', function () {
        // We'll track events using a custom approach
        $eventsDispatched = [];

        Event::listen(InvoiceSubmitted::class, function ($event) use (&$eventsDispatched) {
            $eventsDispatched['InvoiceSubmitted'] = [
                'invoiceId' => $event->invoiceId,
                'invoiceNumber' => $event->invoiceNumber,
            ];
        });

        Event::listen(ApprovalProcessStarted::class, function ($event) use (&$eventsDispatched) {
            $eventsDispatched['ApprovalProcessStarted'] = [
                'invoiceId' => $event->invoiceId,
                'approvalId' => $event->approvalId,
            ];
        });

        postJson('/api/v1/invoices', [
            'invoiceNumber' => 'INV-2025-0004',
            'amount' => 8000.00,
            'submitterId' => '550e8400-e29b-41d4-a716-446655440000',
            'supervisorId' => '550e8400-e29b-41d4-a716-446655440001',
        ]);

        // Verify both events were dispatched
        expect($eventsDispatched)->toHaveKey('InvoiceSubmitted');
        expect($eventsDispatched)->toHaveKey('ApprovalProcessStarted');

        // Both events should reference the same invoice
        expect($eventsDispatched['InvoiceSubmitted']['invoiceId'])
            ->toBe($eventsDispatched['ApprovalProcessStarted']['invoiceId']);
    });

    it('maintains data integrity across bounded contexts', function () {
        $invoiceRepository = app(InvoiceRepositoryInterface::class);
        $approvalRepository = app(ApprovalRepositoryInterface::class);

        $response = postJson('/api/v1/invoices', [
            'invoiceNumber' => 'INV-2025-0005',
            'amount' => 12500.75,
            'submitterId' => 'cccccccc-3333-3333-3333-333333333333',
            'supervisorId' => 'dddddddd-4444-4444-4444-444444444444',
        ]);

        $invoiceId = $response->json('invoiceId');

        // Invoice Context
        $invoice = $invoiceRepository->findById(InvoiceId::fromString($invoiceId));
        expect($invoice->getId()->value)->toBe($invoiceId);

        // Approval Context references the same invoiceId
        $approval = $approvalRepository->findByInvoiceId($invoiceId);
        expect($approval->getInvoiceId())->toBe($invoiceId);

        // Cross-context data consistency
        expect($approval->getApproverId()->value)->toBe($invoice->getSupervisorId());
    });
});
