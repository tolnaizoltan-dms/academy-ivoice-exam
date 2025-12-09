<?php

declare(strict_types=1);

use App\Domain\InvoiceReception\ValueObjects\InvoiceId;

describe('InvoiceId Value Object', function (): void {
    it('creates valid invoice id', function (): void {
        $id = new InvoiceId('550e8400-e29b-41d4-a716-446655440000');

        expect($id->value)->toBe('550e8400-e29b-41d4-a716-446655440000');
        expect((string) $id)->toBe('550e8400-e29b-41d4-a716-446655440000');
    });

    it('generates unique ids', function (): void {
        $id1 = InvoiceId::generate();
        $id2 = InvoiceId::generate();

        expect($id1->value)->not->toBe($id2->value);
        expect($id1->equals($id2))->toBeFalse();
    });

    it('creates from string', function (): void {
        $id = InvoiceId::fromString('my-custom-id');

        expect($id->value)->toBe('my-custom-id');
    });

    it('throws exception for empty id', function (): void {
        new InvoiceId('');
    })->throws(InvalidArgumentException::class, 'Invoice ID cannot be empty');

    it('compares equal ids correctly', function (): void {
        $id1 = new InvoiceId('same-id');
        $id2 = new InvoiceId('same-id');
        $id3 = new InvoiceId('different-id');

        expect($id1->equals($id2))->toBeTrue();
        expect($id1->equals($id3))->toBeFalse();
    });
});
