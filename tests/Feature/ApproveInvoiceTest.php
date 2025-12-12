<?php

declare(strict_types=1);

use App\Domain\Approval\Contracts\ApprovalRepositoryInterface;
use App\Domain\Approval\Events\InvoiceApproved;
use Illuminate\Support\Facades\Event;

use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

describe('Approve Invoice API', function (): void {
    it('can approve a pending invoice', function (): void {
        // First, submit an invoice to create an approval
        $response = postJson('/api/v1/invoices', [
            'invoiceNumber' => 'INV-2025-0001',
            'amount' => 1500.50,
            'submitterId' => '550e8400-e29b-41d4-a716-446655440000',
            'supervisorId' => '550e8400-e29b-41d4-a716-446655440001',
        ]);

        $invoiceId = $response->json('invoiceId');

        // Get the approval ID
        $approvalRepository = app(ApprovalRepositoryInterface::class);
        $approval = $approvalRepository->findByInvoiceId($invoiceId);

        // Approve the invoice
        $approveResponse = putJson("/api/v1/approvals/{$approval->getId()->value}/approve");

        $approveResponse->assertStatus(200)
            ->assertJsonStructure([
                'approvalId',
                'invoiceId',
                'status',
                'message',
            ])
            ->assertJson([
                'status' => 'approved',
                'message' => 'Invoice approved successfully.',
            ]);
    });

    it('dispatches InvoiceApproved event', function (): void {
        Event::fake([InvoiceApproved::class]);

        // Submit invoice
        $response = postJson('/api/v1/invoices', [
            'invoiceNumber' => 'INV-2025-0002',
            'amount' => 2000.00,
            'submitterId' => '550e8400-e29b-41d4-a716-446655440000',
            'supervisorId' => '550e8400-e29b-41d4-a716-446655440001',
        ]);

        $invoiceId = $response->json('invoiceId');
        $approvalRepository = app(ApprovalRepositoryInterface::class);
        $approval = $approvalRepository->findByInvoiceId($invoiceId);

        // Approve
        putJson("/api/v1/approvals/{$approval->getId()->value}/approve");

        Event::assertDispatched(InvoiceApproved::class);
    });

    it('returns error for non-existent approval', function (): void {
        $response = putJson('/api/v1/approvals/non-existent-id/approve');

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'Approval failed',
            ]);
    });

    it('returns error when already approved', function (): void {
        // Submit and approve
        $response = postJson('/api/v1/invoices', [
            'invoiceNumber' => 'INV-2025-0003',
            'amount' => 3000.00,
            'submitterId' => '550e8400-e29b-41d4-a716-446655440000',
            'supervisorId' => '550e8400-e29b-41d4-a716-446655440001',
        ]);

        $invoiceId = $response->json('invoiceId');
        $approvalRepository = app(ApprovalRepositoryInterface::class);
        $approval = $approvalRepository->findByInvoiceId($invoiceId);

        // First approve
        putJson("/api/v1/approvals/{$approval->getId()->value}/approve");

        // Try to approve again
        $secondResponse = putJson("/api/v1/approvals/{$approval->getId()->value}/approve");

        $secondResponse->assertStatus(400)
            ->assertJson([
                'error' => 'Approval failed',
                'message' => 'Cannot modify an already approved invoice.',
            ]);
    });

    it('returns error when trying to approve a rejected invoice', function (): void {
        // Submit invoice
        $response = postJson('/api/v1/invoices', [
            'invoiceNumber' => 'INV-2025-0004',
            'amount' => 4000.00,
            'submitterId' => '550e8400-e29b-41d4-a716-446655440000',
            'supervisorId' => '550e8400-e29b-41d4-a716-446655440001',
        ]);

        $invoiceId = $response->json('invoiceId');
        $approvalRepository = app(ApprovalRepositoryInterface::class);
        $approval = $approvalRepository->findByInvoiceId($invoiceId);

        // First reject
        putJson("/api/v1/approvals/{$approval->getId()->value}/reject", [
            'reason' => 'Budget exceeded',
        ]);

        // Try to approve rejected invoice
        $approveResponse = putJson("/api/v1/approvals/{$approval->getId()->value}/approve");

        $approveResponse->assertStatus(400)
            ->assertJson([
                'error' => 'Approval failed',
                'message' => 'Cannot modify an already rejected invoice.',
            ]);
    });
});
