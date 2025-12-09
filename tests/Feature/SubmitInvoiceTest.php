<?php

declare(strict_types=1);

use App\Domain\InvoiceReception\Events\InvoiceSubmitted;
use Illuminate\Support\Facades\Event;

use function Pest\Laravel\postJson;

describe('Submit Invoice API', function (): void {
    it('can submit a valid invoice', function (): void {
        Event::fake();

        $response = postJson('/api/v1/invoices', [
            'invoiceNumber' => 'INV-2025-0001',
            'amount' => 1500.50,
            'submitterId' => '550e8400-e29b-41d4-a716-446655440000',
            'supervisorId' => '550e8400-e29b-41d4-a716-446655440001',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'invoiceId',
                'invoiceNumber',
                'amount',
                'status',
                'message',
            ])
            ->assertJson([
                'invoiceNumber' => 'INV-2025-0001',
                'amount' => 1500.50,
                'status' => 'submitted',
            ]);
    });

    it('dispatches InvoiceSubmitted event', function (): void {
        Event::fake([InvoiceSubmitted::class]);

        postJson('/api/v1/invoices', [
            'invoiceNumber' => 'INV-2025-0001',
            'amount' => 1000.00,
            'submitterId' => '550e8400-e29b-41d4-a716-446655440000',
            'supervisorId' => '550e8400-e29b-41d4-a716-446655440001',
        ]);

        Event::assertDispatched(InvoiceSubmitted::class, function ($event) {
            return $event->invoiceNumber === 'INV-2025-0001'
                && $event->amount === 1000.00
                && $event->submitterId === '550e8400-e29b-41d4-a716-446655440000'
                && $event->supervisorId === '550e8400-e29b-41d4-a716-446655440001';
        });
    });

    it('validates required fields', function (): void {
        $response = postJson('/api/v1/invoices', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['invoiceNumber', 'amount', 'submitterId', 'supervisorId']);
    });

    it('validates invoice number format', function (): void {
        $response = postJson('/api/v1/invoices', [
            'invoiceNumber' => 'INVALID-FORMAT',
            'amount' => 1000.00,
            'submitterId' => '550e8400-e29b-41d4-a716-446655440000',
            'supervisorId' => '550e8400-e29b-41d4-a716-446655440001',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['invoiceNumber']);
    });

    it('validates amount is positive', function (): void {
        $response = postJson('/api/v1/invoices', [
            'invoiceNumber' => 'INV-2025-0001',
            'amount' => 0,
            'submitterId' => '550e8400-e29b-41d4-a716-446655440000',
            'supervisorId' => '550e8400-e29b-41d4-a716-446655440001',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    });

    it('validates submitter id is uuid', function (): void {
        $response = postJson('/api/v1/invoices', [
            'invoiceNumber' => 'INV-2025-0001',
            'amount' => 1000.00,
            'submitterId' => 'not-a-uuid',
            'supervisorId' => '550e8400-e29b-41d4-a716-446655440001',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['submitterId']);
    });

    it('validates supervisor id is uuid', function (): void {
        $response = postJson('/api/v1/invoices', [
            'invoiceNumber' => 'INV-2025-0001',
            'amount' => 1000.00,
            'submitterId' => '550e8400-e29b-41d4-a716-446655440000',
            'supervisorId' => 'not-a-uuid',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['supervisorId']);
    });
});
