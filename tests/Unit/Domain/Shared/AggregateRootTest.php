<?php

declare(strict_types=1);

use App\Domain\Shared\AggregateRoot;

// Test double: concrete implementation of abstract AggregateRoot
class TestAggregate extends AggregateRoot
{
    public function doSomething(object $event): void
    {
        $this->recordEvent($event);
    }

    public function doMultipleThings(object ...$events): void
    {
        foreach ($events as $event) {
            $this->recordEvent($event);
        }
    }
}

// Simple event class for testing
class TestEvent
{
    public function __construct(public readonly string $name) {}
}

describe('AggregateRoot', function (): void {

    it('starts with no recorded events', function (): void {
        $aggregate = new TestAggregate;

        expect($aggregate->getRecordedEvents())->toBeEmpty();
    });

    it('records a single event', function (): void {
        $aggregate = new TestAggregate;
        $event = new TestEvent('test');

        $aggregate->doSomething($event);

        expect($aggregate->getRecordedEvents())->toHaveCount(1);
        expect($aggregate->getRecordedEvents()[0])->toBe($event);
    });

    it('records multiple events in order', function (): void {
        $aggregate = new TestAggregate;
        $event1 = new TestEvent('first');
        $event2 = new TestEvent('second');
        $event3 = new TestEvent('third');

        $aggregate->doMultipleThings($event1, $event2, $event3);

        $events = $aggregate->getRecordedEvents();

        expect($events)->toHaveCount(3);
        expect($events[0]->name)->toBe('first');
        expect($events[1]->name)->toBe('second');
        expect($events[2]->name)->toBe('third');
    });

    it('releases events and clears internal list', function (): void {
        $aggregate = new TestAggregate;
        $event = new TestEvent('test');

        $aggregate->doSomething($event);
        $released = $aggregate->releaseEvents();

        expect($released)->toHaveCount(1);
        expect($released[0])->toBe($event);
        expect($aggregate->getRecordedEvents())->toBeEmpty();
    });

    it('returns empty array when releasing with no events', function (): void {
        $aggregate = new TestAggregate;

        $released = $aggregate->releaseEvents();

        expect($released)->toBeEmpty();
    });

    it('can record events after releasing', function (): void {
        $aggregate = new TestAggregate;
        $event1 = new TestEvent('first');
        $event2 = new TestEvent('second');

        $aggregate->doSomething($event1);
        $aggregate->releaseEvents();

        $aggregate->doSomething($event2);

        expect($aggregate->getRecordedEvents())->toHaveCount(1);
        expect($aggregate->getRecordedEvents()[0]->name)->toBe('second');
    });

    it('getRecordedEvents does not clear the list', function (): void {
        $aggregate = new TestAggregate;
        $event = new TestEvent('test');

        $aggregate->doSomething($event);

        // Call multiple times
        $aggregate->getRecordedEvents();
        $aggregate->getRecordedEvents();

        expect($aggregate->getRecordedEvents())->toHaveCount(1);
    });

    it('accumulates events across multiple operations', function (): void {
        $aggregate = new TestAggregate;

        $aggregate->doSomething(new TestEvent('first'));
        $aggregate->doSomething(new TestEvent('second'));
        $aggregate->doSomething(new TestEvent('third'));

        expect($aggregate->getRecordedEvents())->toHaveCount(3);
    });
});
