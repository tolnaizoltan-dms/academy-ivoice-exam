<?php

declare(strict_types=1);

namespace App\Domain\Approval\Events;

use DateTimeImmutable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Domain Event: Approval process has been started for an invoice.
 */
final class ApprovalProcessStarted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly string $approvalId,
        public readonly string $invoiceId,
        public readonly string $approverId,
        public readonly DateTimeImmutable $occurredAt,
    ) {}
}
