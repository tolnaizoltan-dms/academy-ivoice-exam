<?php

declare(strict_types=1);

namespace App\Domain\Shared;

/**
 * Base class for all Aggregate Roots in the domain.
 *
 * Aggregate Roots are the entry point to an aggregate and are responsible for:
 * - Maintaining invariants within the aggregate boundary
 * - Recording domain events that occur within the aggregate
 * - Providing a consistent interface for aggregate manipulation
 */
abstract class AggregateRoot
{
    /** @var array<object> */
    private array $recordedEvents = [];

    /**
     * Record a domain event that occurred within this aggregate.
     */
    protected function recordEvent(object $event): void
    {
        $this->recordedEvents[] = $event;
    }

    /**
     * Release all recorded domain events and clear the internal list.
     *
     * @return array<object>
     */
    public function releaseEvents(): array
    {
        $events = $this->recordedEvents;
        $this->recordedEvents = [];

        return $events;
    }

    /**
     * Get recorded events without releasing them.
     *
     * @return array<object>
     */
    public function getRecordedEvents(): array
    {
        return $this->recordedEvents;
    }
}
