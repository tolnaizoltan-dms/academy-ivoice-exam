<?php

declare(strict_types=1);

use App\Domain\InvoiceReception\Aggregates\Invoice;
use App\Domain\InvoiceReception\ValueObjects\Amount;
use App\Domain\InvoiceReception\ValueObjects\InvoiceId;
use App\Domain\InvoiceReception\ValueObjects\InvoiceNumber;
use App\Domain\InvoiceReception\ValueObjects\SubmitterId;
use App\Infrastructure\Persistence\Repositories\EloquentInvoiceRepository;

describe('EloquentInvoiceRepository', function (): void {
    beforeEach(function (): void {
        $this->repository = new EloquentInvoiceRepository();
    });

    it('can save and retrieve an invoice', function (): void {
        $invoice = Invoice::submit(
            id: InvoiceId::generate(),
            number: new InvoiceNumber('INV-2025-0001'),
            amount: new Amount(1000.00),
            submitterId: new SubmitterId('user-123'),
            supervisorId: 'supervisor-456',
        );
        $invoice->releaseEvents();

        $this->repository->save($invoice);
        $found = $this->repository->findById($invoice->getId());

        expect($found)->not->toBeNull();
        expect($found->getId()->equals($invoice->getId()))->toBeTrue();
        expect($found->getNumber()->value)->toBe('INV-2025-0001');
        expect($found->getAmount()->getValue())->toBe(1000.00);
        expect($found->getSubmitterId()->value)->toBe('user-123');
        expect($found->getSupervisorId())->toBe('supervisor-456');
    });

    it('returns null for non-existent invoice', function (): void {
        $found = $this->repository->findById(InvoiceId::generate());

        expect($found)->toBeNull();
    });

    it('generates unique identities', function (): void {
        $id1 = $this->repository->nextIdentity();
        $id2 = $this->repository->nextIdentity();

        expect($id1->equals($id2))->toBeFalse();
    });

    it('returns true when invoice with number exists', function (): void {
        $invoice = Invoice::submit(
            id: InvoiceId::generate(),
            number: new InvoiceNumber('INV-2025-0002'),
            amount: new Amount(1500.00),
            submitterId: new SubmitterId('user-456'),
            supervisorId: 'supervisor-789',
        );
        $invoice->releaseEvents();

        $this->repository->save($invoice);

        expect($this->repository->existsByNumber(new InvoiceNumber('INV-2025-0002')))->toBeTrue();
    });

    it('returns false when invoice with number does not exist', function (): void {
        expect($this->repository->existsByNumber(new InvoiceNumber('INV-2025-9999')))->toBeFalse();
    });

    it('updates existing invoice on save', function (): void {
        $invoice = Invoice::submit(
            id: InvoiceId::generate(),
            number: new InvoiceNumber('INV-2025-0003'),
            amount: new Amount(2000.00),
            submitterId: new SubmitterId('user-789'),
            supervisorId: 'supervisor-111',
        );
        $invoice->releaseEvents();

        $this->repository->save($invoice);

        // Save again (simulate update)
        $this->repository->save($invoice);

        $found = $this->repository->findById($invoice->getId());

        expect($found)->not->toBeNull();
        expect($found->getNumber()->value)->toBe('INV-2025-0003');
    });

    it('persists submitted at timestamp', function (): void {
        $invoice = Invoice::submit(
            id: InvoiceId::generate(),
            number: new InvoiceNumber('INV-2025-0004'),
            amount: new Amount(3000.00),
            submitterId: new SubmitterId('user-999'),
            supervisorId: 'supervisor-222',
        );
        $invoice->releaseEvents();

        $this->repository->save($invoice);
        $found = $this->repository->findById($invoice->getId());

        expect($found)->not->toBeNull();
        expect($found->getSubmittedAt())->not->toBeNull();
    });
});
