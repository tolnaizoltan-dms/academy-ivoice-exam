<?php

declare(strict_types=1);

use App\Domain\InvoiceReception\Aggregates\Invoice;
use App\Domain\InvoiceReception\Events\InvoiceSubmitted;
use App\Domain\InvoiceReception\Exceptions\InvalidInvoiceException;
use App\Domain\InvoiceReception\ValueObjects\Amount;
use App\Domain\InvoiceReception\ValueObjects\InvoiceId;
use App\Domain\InvoiceReception\ValueObjects\InvoiceNumber;
use App\Domain\InvoiceReception\ValueObjects\SubmitterId;

describe('Invoice Aggregate', function (): void {
    it('can submit a valid invoice', function (): void {
        $invoice = Invoice::submit(
            id: InvoiceId::generate(),
            number: new InvoiceNumber('INV-2025-0001'),
            amount: new Amount(1500.50),
            submitterId: new SubmitterId('user-123'),
            supervisorId: 'supervisor-456',
        );

        expect($invoice)->toBeInstanceOf(Invoice::class);
        expect($invoice->getNumber()->value)->toBe('INV-2025-0001');
        expect($invoice->getAmount()->getValue())->toBe(1500.50);
        expect($invoice->getSubmitterId()->value)->toBe('user-123');
        expect($invoice->getSupervisorId())->toBe('supervisor-456');
        expect($invoice->getSubmittedAt())->toBeInstanceOf(DateTimeImmutable::class);
    });

    it('raises InvoiceSubmitted event when submitted', function (): void {
        $invoice = Invoice::submit(
            id: InvoiceId::generate(),
            number: new InvoiceNumber('INV-2025-0001'),
            amount: new Amount(1000.00),
            submitterId: new SubmitterId('user-123'),
            supervisorId: 'supervisor-456',
        );

        $events = $invoice->releaseEvents();

        expect($events)->toHaveCount(1);
        expect($events[0])->toBeInstanceOf(InvoiceSubmitted::class);
        expect($events[0]->invoiceNumber)->toBe('INV-2025-0001');
        expect($events[0]->amount)->toBe(1000.00);
        expect($events[0]->submitterId)->toBe('user-123');
        expect($events[0]->supervisorId)->toBe('supervisor-456');
    });

    it('clears events after release', function (): void {
        $invoice = Invoice::submit(
            id: InvoiceId::generate(),
            number: new InvoiceNumber('INV-2025-0001'),
            amount: new Amount(1000.00),
            submitterId: new SubmitterId('user-123'),
            supervisorId: 'supervisor-456',
        );

        $events1 = $invoice->releaseEvents();
        $events2 = $invoice->releaseEvents();

        expect($events1)->toHaveCount(1);
        expect($events2)->toHaveCount(0);
    });

    it('throws exception for empty supervisor id', function (): void {
        Invoice::submit(
            id: InvoiceId::generate(),
            number: new InvoiceNumber('INV-2025-0001'),
            amount: new Amount(1000.00),
            submitterId: new SubmitterId('user-123'),
            supervisorId: '',
        );
    })->throws(InvalidInvoiceException::class, 'Supervisor ID cannot be empty');

    it('preserves invoice id', function (): void {
        $id = InvoiceId::generate();

        $invoice = Invoice::submit(
            id: $id,
            number: new InvoiceNumber('INV-2025-0001'),
            amount: new Amount(1000.00),
            submitterId: new SubmitterId('user-123'),
            supervisorId: 'supervisor-456',
        );

        expect($invoice->getId()->equals($id))->toBeTrue();
    });
});
