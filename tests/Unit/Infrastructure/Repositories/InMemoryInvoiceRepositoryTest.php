<?php

declare(strict_types=1);

use App\Domain\InvoiceReception\Aggregates\Invoice;
use App\Domain\InvoiceReception\ValueObjects\Amount;
use App\Domain\InvoiceReception\ValueObjects\InvoiceId;
use App\Domain\InvoiceReception\ValueObjects\InvoiceNumber;
use App\Domain\InvoiceReception\ValueObjects\SubmitterId;
use App\Infrastructure\Repositories\InMemoryInvoiceRepository;

describe('InMemoryInvoiceRepository', function (): void {
    beforeEach(function (): void {
        $this->repository = new InMemoryInvoiceRepository;
    });

    it('can save and retrieve an invoice', function (): void {
        $invoice = Invoice::submit(
            id: InvoiceId::generate(),
            number: new InvoiceNumber('INV-2025-0001'),
            amount: new Amount(1000.00),
            submitterId: new SubmitterId('user-123'),
            supervisorId: 'supervisor-456',
        );

        $this->repository->save($invoice);
        $found = $this->repository->findById($invoice->getId());

        expect($found)->not->toBeNull();
        expect($found->getId()->equals($invoice->getId()))->toBeTrue();
        expect($found->getNumber()->value)->toBe('INV-2025-0001');
    });

    it('returns null for non-existent invoice', function (): void {
        $found = $this->repository->findById(new InvoiceId('non-existent'));

        expect($found)->toBeNull();
    });

    it('generates unique identities', function (): void {
        $id1 = $this->repository->nextIdentity();
        $id2 = $this->repository->nextIdentity();

        expect($id1->equals($id2))->toBeFalse();
    });

    it('can retrieve all invoices', function (): void {
        $invoice1 = Invoice::submit(
            id: InvoiceId::generate(),
            number: new InvoiceNumber('INV-2025-0001'),
            amount: new Amount(1000.00),
            submitterId: new SubmitterId('user-123'),
            supervisorId: 'supervisor-456',
        );

        $invoice2 = Invoice::submit(
            id: InvoiceId::generate(),
            number: new InvoiceNumber('INV-2025-0002'),
            amount: new Amount(2000.00),
            submitterId: new SubmitterId('user-123'),
            supervisorId: 'supervisor-456',
        );

        $this->repository->save($invoice1);
        $this->repository->save($invoice2);

        expect($this->repository->all())->toHaveCount(2);
    });

    it('can clear all invoices', function (): void {
        $invoice = Invoice::submit(
            id: InvoiceId::generate(),
            number: new InvoiceNumber('INV-2025-0001'),
            amount: new Amount(1000.00),
            submitterId: new SubmitterId('user-123'),
            supervisorId: 'supervisor-456',
        );

        $this->repository->save($invoice);
        $this->repository->clear();

        expect($this->repository->all())->toHaveCount(0);
    });

    it('returns true when invoice with number exists', function (): void {
        $invoice = Invoice::submit(
            id: InvoiceId::generate(),
            number: new InvoiceNumber('INV-2025-0001'),
            amount: new Amount(1000.00),
            submitterId: new SubmitterId('user-123'),
            supervisorId: 'supervisor-456',
        );

        $this->repository->save($invoice);

        expect($this->repository->existsByNumber(new InvoiceNumber('INV-2025-0001')))->toBeTrue();
    });

    it('returns false when invoice with number does not exist', function (): void {
        expect($this->repository->existsByNumber(new InvoiceNumber('INV-2025-0001')))->toBeFalse();
    });
});
