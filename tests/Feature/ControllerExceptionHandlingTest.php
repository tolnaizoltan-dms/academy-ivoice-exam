<?php

declare(strict_types=1);

use App\Domain\Approval\Contracts\ApprovalRepositoryInterface;
use App\Domain\InvoiceReception\Contracts\InvoiceRepositoryInterface;

use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

describe('Controller Exception Handling', function (): void {

    describe('InvoiceController', function (): void {

        it('returns 500 when unexpected exception occurs during submission', function (): void {
            $mock = Mockery::mock(InvoiceRepositoryInterface::class);
            $mock->shouldReceive('existsByNumber')->andThrow(new RuntimeException('Database connection failed'));

            app()->instance(InvoiceRepositoryInterface::class, $mock);

            $response = postJson('/api/v1/invoices', [
                'invoiceNumber' => 'INV-2025-0001',
                'amount' => 1000.00,
                'submitterId' => '550e8400-e29b-41d4-a716-446655440000',
                'supervisorId' => '550e8400-e29b-41d4-a716-446655440001',
            ]);

            $response->assertStatus(500)
                ->assertJson([
                    'error' => 'Internal server error',
                    'message' => 'An unexpected error occurred while submitting the invoice.',
                ]);
        });
    });

    describe('ApprovalController', function (): void {

        it('returns 500 when unexpected exception occurs during approval', function (): void {
            $mock = Mockery::mock(ApprovalRepositoryInterface::class);
            $mock->shouldReceive('findById')->andThrow(new RuntimeException('Database connection failed'));

            app()->instance(ApprovalRepositoryInterface::class, $mock);

            $response = putJson('/api/v1/approvals/some-approval-id/approve');

            $response->assertStatus(500)
                ->assertJson([
                    'error' => 'Internal server error',
                    'message' => 'An unexpected error occurred while approving the invoice.',
                ]);
        });

        it('returns 500 when unexpected exception occurs during rejection', function (): void {
            $mock = Mockery::mock(ApprovalRepositoryInterface::class);
            $mock->shouldReceive('findById')->andThrow(new RuntimeException('Database connection failed'));

            app()->instance(ApprovalRepositoryInterface::class, $mock);

            $response = putJson('/api/v1/approvals/some-approval-id/reject', [
                'reason' => 'Some valid reason',
            ]);

            $response->assertStatus(500)
                ->assertJson([
                    'error' => 'Internal server error',
                    'message' => 'An unexpected error occurred while rejecting the invoice.',
                ]);
        });
    });
});

