<?php

declare(strict_types=1);

use App\Domain\Approval\ValueObjects\ApprovalStatus;

describe('ApprovalStatus Enum', function () {
    it('has pending status', function () {
        $status = ApprovalStatus::PENDING;

        expect($status->value)->toBe('pending');
        expect($status->isPending())->toBeTrue();
        expect($status->isComplete())->toBeFalse();
    });

    it('has approved status', function () {
        $status = ApprovalStatus::APPROVED;

        expect($status->value)->toBe('approved');
        expect($status->isPending())->toBeFalse();
        expect($status->isComplete())->toBeTrue();
    });

    it('has rejected status', function () {
        $status = ApprovalStatus::REJECTED;

        expect($status->value)->toBe('rejected');
        expect($status->isPending())->toBeFalse();
        expect($status->isComplete())->toBeTrue();
    });

    it('can be created from string value', function () {
        $pending = ApprovalStatus::from('pending');
        $approved = ApprovalStatus::from('approved');
        $rejected = ApprovalStatus::from('rejected');

        expect($pending)->toBe(ApprovalStatus::PENDING);
        expect($approved)->toBe(ApprovalStatus::APPROVED);
        expect($rejected)->toBe(ApprovalStatus::REJECTED);
    });
});
