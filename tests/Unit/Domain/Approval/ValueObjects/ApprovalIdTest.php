<?php

declare(strict_types=1);

use App\Domain\Approval\ValueObjects\ApprovalId;

describe('ApprovalId Value Object', function () {
    it('creates valid approval id', function () {
        $id = new ApprovalId('550e8400-e29b-41d4-a716-446655440000');

        expect($id->value)->toBe('550e8400-e29b-41d4-a716-446655440000');
        expect((string) $id)->toBe('550e8400-e29b-41d4-a716-446655440000');
    });

    it('generates unique ids', function () {
        $id1 = ApprovalId::generate();
        $id2 = ApprovalId::generate();

        expect($id1->value)->not->toBe($id2->value);
        expect($id1->equals($id2))->toBeFalse();
    });

    it('creates from string', function () {
        $id = ApprovalId::fromString('my-approval-id');

        expect($id->value)->toBe('my-approval-id');
    });

    it('throws exception for empty id', function () {
        new ApprovalId('');
    })->throws(InvalidArgumentException::class, 'Approval ID cannot be empty');

    it('compares equal ids correctly', function () {
        $id1 = new ApprovalId('same-id');
        $id2 = new ApprovalId('same-id');
        $id3 = new ApprovalId('different-id');

        expect($id1->equals($id2))->toBeTrue();
        expect($id1->equals($id3))->toBeFalse();
    });
});
