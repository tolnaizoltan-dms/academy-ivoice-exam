<?php

declare(strict_types=1);

use App\Domain\Approval\Contracts\ApprovalRepositoryInterface;
use App\Domain\Approval\Events\InvoiceRejected;
use Illuminate\Support\Facades\Event;

use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

describe('Reject Invoice API', function () {
    it('can reject a pending invoice with reason', function () {
        // First, submit an invoice to create an approval
        $response = postJson('/api/v1/invoices', [
            'invoiceNumber' => 'INV-2025-0010',
            'amount' => 1500.50,
            'submitterId' => '550e8400-e29b-41d4-a716-446655440000',
            'supervisorId' => '550e8400-e29b-41d4-a716-446655440001',
        ]);

        $invoiceId = $response->json('invoiceId');

        // Get the approval ID
        $approvalRepository = app(ApprovalRepositoryInterface::class);
        $approval = $approvalRepository->findByInvoiceId($invoiceId);

        // Reject the invoice
        $rejectResponse = putJson("/api/v1/approvals/{$approval->getId()->value}/reject", [
            'reason' => 'Insufficient documentation provided.',
        ]);

        $rejectResponse->assertStatus(200)
            ->assertJsonStructure([
                'approvalId',
                'invoiceId',
                'status',
                'reason',
                'message',
            ])
            ->assertJson([
                'status' => 'rejected',
                'reason' => 'Insufficient documentation provided.',
                'message' => 'Invoice rejected successfully.',
            ]);
    });

    it('dispatches InvoiceRejected event', function () {
        Event::fake([InvoiceRejected::class]);

        // Submit invoice
        $response = postJson('/api/v1/invoices', [
            'invoiceNumber' => 'INV-2025-0011',
            'amount' => 2000.00,
            'submitterId' => '550e8400-e29b-41d4-a716-446655440000',
            'supervisorId' => '550e8400-e29b-41d4-a716-446655440001',
        ]);

        $invoiceId = $response->json('invoiceId');
        $approvalRepository = app(ApprovalRepositoryInterface::class);
        $approval = $approvalRepository->findByInvoiceId($invoiceId);

        // Reject
        putJson("/api/v1/approvals/{$approval->getId()->value}/reject", [
            'reason' => 'Budget exceeded',
        ]);

        Event::assertDispatched(InvoiceRejected::class, function ($event) {
            return $event->reason === 'Budget exceeded';
        });
    });

    it('validates reason is required', function () {
        $response = putJson('/api/v1/approvals/some-id/reject', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['reason']);
    });

    it('validates reason minimum length', function () {
        $response = putJson('/api/v1/approvals/some-id/reject', [
            'reason' => 'ab',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['reason']);
    });

    it('returns error for non-existent approval', function () {
        $response = putJson('/api/v1/approvals/non-existent-id/reject', [
            'reason' => 'Some valid reason here',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'Rejection failed',
            ]);
    });

    it('returns error when already rejected', function () {
        // Submit and reject
        $response = postJson('/api/v1/invoices', [
            'invoiceNumber' => 'INV-2025-0012',
            'amount' => 3000.00,
            'submitterId' => '550e8400-e29b-41d4-a716-446655440000',
            'supervisorId' => '550e8400-e29b-41d4-a716-446655440001',
        ]);

        $invoiceId = $response->json('invoiceId');
        $approvalRepository = app(ApprovalRepositoryInterface::class);
        $approval = $approvalRepository->findByInvoiceId($invoiceId);

        // First reject
        putJson("/api/v1/approvals/{$approval->getId()->value}/reject", [
            'reason' => 'First rejection',
        ]);

        // Try to reject again
        $secondResponse = putJson("/api/v1/approvals/{$approval->getId()->value}/reject", [
            'reason' => 'Second rejection',
        ]);

        $secondResponse->assertStatus(400)
            ->assertJson([
                'error' => 'Rejection failed',
                'message' => 'Cannot modify an already rejected invoice.',
            ]);
    });

    it('returns error when trying to reject an approved invoice', function () {
        // Submit and approve first
        $response = postJson('/api/v1/invoices', [
            'invoiceNumber' => 'INV-2025-0013',
            'amount' => 4000.00,
            'submitterId' => '550e8400-e29b-41d4-a716-446655440000',
            'supervisorId' => '550e8400-e29b-41d4-a716-446655440001',
        ]);

        $invoiceId = $response->json('invoiceId');
        $approvalRepository = app(ApprovalRepositoryInterface::class);
        $approval = $approvalRepository->findByInvoiceId($invoiceId);

        // Approve first
        putJson("/api/v1/approvals/{$approval->getId()->value}/approve");

        // Try to reject
        $rejectResponse = putJson("/api/v1/approvals/{$approval->getId()->value}/reject", [
            'reason' => 'Trying to reject approved',
        ]);

        $rejectResponse->assertStatus(400)
            ->assertJson([
                'error' => 'Rejection failed',
                'message' => 'Cannot modify an already approved invoice.',
            ]);
    });
});
