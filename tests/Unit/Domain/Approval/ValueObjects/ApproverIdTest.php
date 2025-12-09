<?php

declare(strict_types=1);

use App\Domain\Approval\Exceptions\InvalidApprovalException;
use App\Domain\Approval\ValueObjects\ApproverId;

describe('ApproverId Value Object', function (): void {
    it('creates valid approver id', function (): void {
        $id = new ApproverId('supervisor-123');

        expect($id->value)->toBe('supervisor-123');
        expect((string) $id)->toBe('supervisor-123');
    });

    it('creates from string', function (): void {
        $id = ApproverId::fromString('manager-456');

        expect($id->value)->toBe('manager-456');
    });

    it('throws exception for empty id', function (): void {
        new ApproverId('');
    })->throws(InvalidApprovalException::class, 'Approver ID cannot be empty');

    it('compares equal ids correctly', function (): void {
        $id1 = new ApproverId('approver-1');
        $id2 = new ApproverId('approver-1');
        $id3 = new ApproverId('approver-2');

        expect($id1->equals($id2))->toBeTrue();
        expect($id1->equals($id3))->toBeFalse();
    });
});
