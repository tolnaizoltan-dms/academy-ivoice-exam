<?php

declare(strict_types=1);

use App\Application\Actions\SubmitInvoiceAction;
use App\Application\DTOs\SubmitInvoiceData;
use App\Domain\InvoiceReception\Aggregates\Invoice;
use App\Domain\InvoiceReception\Events\InvoiceSubmitted;
use App\Domain\InvoiceReception\Exceptions\InvalidInvoiceException;
use App\Infrastructure\Repositories\InMemoryInvoiceRepository;
use Illuminate\Support\Facades\Event;

describe('SubmitInvoiceAction', function (): void {
    beforeEach(function (): void {
        $this->repository = new InMemoryInvoiceRepository;
        $this->action = new SubmitInvoiceAction($this->repository);
    });

    it('submits a valid invoice', function (): void {
        Event::fake();

        $data = new SubmitInvoiceData(
            invoiceNumber: 'INV-2025-0001',
            amount: 1500.50,
            submitterId: 'user-123',
            supervisorId: 'supervisor-456',
        );

        $invoice = $this->action->execute($data);

        expect($invoice)->toBeInstanceOf(Invoice::class);
        expect($invoice->getNumber()->value)->toBe('INV-2025-0001');
        expect($invoice->getAmount()->getValue())->toBe(1500.50);
    });

    it('persists the invoice to repository', function (): void {
        Event::fake();

        $data = new SubmitInvoiceData(
            invoiceNumber: 'INV-2025-0001',
            amount: 1000.00,
            submitterId: 'user-123',
            supervisorId: 'supervisor-456',
        );

        $invoice = $this->action->execute($data);
        $found = $this->repository->findById($invoice->getId());

        expect($found)->not->toBeNull();
        expect($found->getId()->equals($invoice->getId()))->toBeTrue();
    });

    it('dispatches InvoiceSubmitted event', function (): void {
        Event::fake([InvoiceSubmitted::class]);

        $data = new SubmitInvoiceData(
            invoiceNumber: 'INV-2025-0001',
            amount: 1000.00,
            submitterId: 'user-123',
            supervisorId: 'supervisor-456',
        );

        $this->action->execute($data);

        Event::assertDispatched(InvoiceSubmitted::class, function ($event) {
            return $event->invoiceNumber === 'INV-2025-0001'
                && $event->amount === 1000.00
                && $event->submitterId === 'user-123'
                && $event->supervisorId === 'supervisor-456';
        });
    });

    it('throws exception for invalid invoice number', function (): void {
        Event::fake();

        $data = new SubmitInvoiceData(
            invoiceNumber: 'INVALID',
            amount: 1000.00,
            submitterId: 'user-123',
            supervisorId: 'supervisor-456',
        );

        $this->action->execute($data);
    })->throws(InvalidInvoiceException::class);

    it('throws exception for negative amount', function (): void {
        Event::fake();

        $data = new SubmitInvoiceData(
            invoiceNumber: 'INV-2025-0001',
            amount: -100.00,
            submitterId: 'user-123',
            supervisorId: 'supervisor-456',
        );

        $this->action->execute($data);
    })->throws(InvalidInvoiceException::class);

    it('throws exception for duplicate invoice number', function (): void {
        Event::fake();

        $data = new SubmitInvoiceData(
            invoiceNumber: 'INV-2025-0001',
            amount: 1000.00,
            submitterId: 'user-123',
            supervisorId: 'supervisor-456',
        );

        // First submission should succeed
        $this->action->execute($data);

        // Second submission with same invoice number should fail
        $this->action->execute($data);
    })->throws(InvalidInvoiceException::class, 'An invoice with number INV-2025-0001 already exists.');

    it('can create DTO from array', function (): void {
        $data = SubmitInvoiceData::fromArray([
            'invoiceNumber' => 'INV-2025-0001',
            'amount' => '1500.50',
            'submitterId' => 'user-123',
            'supervisorId' => 'supervisor-456',
        ]);

        expect($data->invoiceNumber)->toBe('INV-2025-0001');
        expect($data->amount)->toBe(1500.50);
        expect($data->submitterId)->toBe('user-123');
        expect($data->supervisorId)->toBe('supervisor-456');
    });
});
