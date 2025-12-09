<?php

declare(strict_types=1);

use App\Domain\InvoiceReception\Exceptions\InvalidInvoiceException;
use App\Domain\InvoiceReception\ValueObjects\InvoiceNumber;

describe('InvoiceNumber Value Object', function () {
    it('creates valid invoice number', function () {
        $number = new InvoiceNumber('INV-2025-0001');

        expect($number->value)->toBe('INV-2025-0001');
        expect((string) $number)->toBe('INV-2025-0001');
    });

    it('creates from string', function () {
        $number = InvoiceNumber::fromString('INV-2024-9999');

        expect($number->value)->toBe('INV-2024-9999');
    });

    it('throws exception for invalid format - missing prefix', function () {
        new InvoiceNumber('2025-0001');
    })->throws(InvalidInvoiceException::class, 'Invoice number must be in format INV-YYYY-XXXX');

    it('throws exception for invalid format - wrong prefix', function () {
        new InvoiceNumber('ABC-2025-0001');
    })->throws(InvalidInvoiceException::class);

    it('throws exception for invalid format - short year', function () {
        new InvoiceNumber('INV-25-0001');
    })->throws(InvalidInvoiceException::class);

    it('throws exception for invalid format - short number', function () {
        new InvoiceNumber('INV-2025-001');
    })->throws(InvalidInvoiceException::class);

    it('throws exception for invalid format - letters in number', function () {
        new InvoiceNumber('INV-2025-ABCD');
    })->throws(InvalidInvoiceException::class);

    it('compares equal numbers correctly', function () {
        $number1 = new InvoiceNumber('INV-2025-0001');
        $number2 = new InvoiceNumber('INV-2025-0001');
        $number3 = new InvoiceNumber('INV-2025-0002');

        expect($number1->equals($number2))->toBeTrue();
        expect($number1->equals($number3))->toBeFalse();
    });
});
