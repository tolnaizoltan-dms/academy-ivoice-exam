<?php

declare(strict_types=1);

use App\Domain\InvoiceReception\Exceptions\InvalidInvoiceException;
use App\Domain\InvoiceReception\ValueObjects\InvoiceNumber;

describe('InvoiceNumber Value Object', function (): void {
    it('creates valid invoice number', function (): void {
        $number = new InvoiceNumber('INV-2025-0001');

        expect($number->value)->toBe('INV-2025-0001');
        expect((string) $number)->toBe('INV-2025-0001');
    });

    it('creates from string', function (): void {
        $number = InvoiceNumber::fromString('INV-2024-9999');

        expect($number->value)->toBe('INV-2024-9999');
    });

    it('throws exception for invalid format - missing prefix', function (): void {
        new InvoiceNumber('2025-0001');
    })->throws(InvalidInvoiceException::class, 'Invoice number must be in format INV-YYYY-XXXX');

    it('throws exception for invalid format - wrong prefix', function (): void {
        new InvoiceNumber('ABC-2025-0001');
    })->throws(InvalidInvoiceException::class);

    it('throws exception for invalid format - short year', function (): void {
        new InvoiceNumber('INV-25-0001');
    })->throws(InvalidInvoiceException::class);

    it('throws exception for invalid format - short number', function (): void {
        new InvoiceNumber('INV-2025-001');
    })->throws(InvalidInvoiceException::class);

    it('throws exception for invalid format - letters in number', function (): void {
        new InvoiceNumber('INV-2025-ABCD');
    })->throws(InvalidInvoiceException::class);

    it('compares equal numbers correctly', function (): void {
        $number1 = new InvoiceNumber('INV-2025-0001');
        $number2 = new InvoiceNumber('INV-2025-0001');
        $number3 = new InvoiceNumber('INV-2025-0002');

        expect($number1->equals($number2))->toBeTrue();
        expect($number1->equals($number3))->toBeFalse();
    });
});
