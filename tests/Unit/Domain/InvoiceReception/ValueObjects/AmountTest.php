<?php

declare(strict_types=1);

use App\Domain\InvoiceReception\Exceptions\InvalidInvoiceException;
use App\Domain\InvoiceReception\ValueObjects\Amount;

describe('Amount Value Object', function () {
    it('creates valid amount with positive value', function () {
        $amount = new Amount(1000.50);

        expect($amount->getValue())->toBe(1000.50);
        expect((string) $amount)->toBe('1000.50');
    });

    it('creates amount from float', function () {
        $amount = Amount::fromFloat(500.00);

        expect($amount->getValue())->toBe(500.00);
    });

    it('throws exception for zero amount', function () {
        new Amount(0);
    })->throws(InvalidInvoiceException::class, 'Invoice amount must be greater than zero');

    it('throws exception for negative amount', function () {
        new Amount(-100.50);
    })->throws(InvalidInvoiceException::class, 'Invoice amount must be greater than zero');

    it('compares equal amounts correctly', function () {
        $amount1 = new Amount(100.00);
        $amount2 = new Amount(100.00);
        $amount3 = new Amount(200.00);

        expect($amount1->equals($amount2))->toBeTrue();
        expect($amount1->equals($amount3))->toBeFalse();
    });
});
