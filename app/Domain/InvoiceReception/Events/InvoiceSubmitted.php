<?php

declare(strict_types=1);

namespace App\Domain\InvoiceReception\Events;

use DateTimeImmutable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Domain Event: An invoice has been submitted for approval.
 *
 * This event is raised when a valid invoice is successfully submitted
 * and triggers the approval process via the Policy.
 */
final class InvoiceSubmitted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly string $invoiceId,
        public readonly string $invoiceNumber,
        public readonly float $amount,
        public readonly string $submitterId,
        public readonly string $supervisorId,
        public readonly DateTimeImmutable $occurredAt,
    ) {}
}
