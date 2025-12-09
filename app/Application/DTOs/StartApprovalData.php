<?php

declare(strict_types=1);

namespace App\Application\DTOs;

/**
 * Data Transfer Object for starting an approval process.
 */
final readonly class StartApprovalData
{
    public function __construct(
        public string $invoiceId,
        public string $approverId,
    ) {}
}
