<?php

declare(strict_types=1);

use App\Domain\InvoiceReception\Exceptions\InvalidInvoiceException;
use App\Domain\InvoiceReception\ValueObjects\SubmitterId;

describe('SubmitterId Value Object', function () {
    it('creates valid submitter id', function () {
        $id = new SubmitterId('550e8400-e29b-41d4-a716-446655440000');

        expect($id->value)->toBe('550e8400-e29b-41d4-a716-446655440000');
        expect((string) $id)->toBe('550e8400-e29b-41d4-a716-446655440000');
    });

    it('creates from string', function () {
        $id = SubmitterId::fromString('user-123');

        expect($id->value)->toBe('user-123');
    });

    it('throws exception for empty id', function () {
        new SubmitterId('');
    })->throws(InvalidInvoiceException::class, 'Submitter ID cannot be empty');

    it('compares equal ids correctly', function () {
        $id1 = new SubmitterId('user-1');
        $id2 = new SubmitterId('user-1');
        $id3 = new SubmitterId('user-2');

        expect($id1->equals($id2))->toBeTrue();
        expect($id1->equals($id3))->toBeFalse();
    });
});
