<?php

declare(strict_types=1);

namespace Planner\Tests\Domain\Model;

use Planner\Domain\Model\ScheduleItem;
use PHPUnit\Framework\TestCase;

class ScheduleItemTest extends TestCase
{
    private function createItem(): ScheduleItem
    {
        return new ScheduleItem(
            'item-1',
            '09:00',
            '10:30',
            'Meeting',
            ['work'],
            'pending'
        );
    }

    public function testConstructorSetsProperties(): void
    {
        $item = $this->createItem();

        $this->assertSame('item-1', $item->id);
        $this->assertSame('09:00', $item->startTime);
        $this->assertSame('10:30', $item->endTime);
        $this->assertSame('Meeting', $item->title);
        $this->assertSame(['work'], $item->tags);
        $this->assertSame('pending', $item->status);
    }

    public function testMarkDone(): void
    {
        $item = $this->createItem();

        $item->markDone();

        $this->assertSame('done', $item->status);
    }

    public function testMarkPending(): void
    {
        $item = $this->createItem();
        $item->markDone();

        $item->markPending();

        $this->assertSame('pending', $item->status);
    }

    public function testToArray(): void
    {
        $item = $this->createItem();

        $this->assertSame(
            [
                'id' => 'item-1',
                'start_time' => '09:00',
                'end_time' => '10:30',
                'title' => 'Meeting',
                'tags' => ['work'],
                'status' => 'pending',
            ],
            $item->toArray()
        );
    }

    public function testFromArray(): void
    {
        $item = ScheduleItem::fromArray([
            'id' => 'item-2',
            'start_time' => '12:00',
            'end_time' => '13:00',
            'title' => 'Lunch',
            'tags' => ['personal'],
            'status' => 'done',
        ]);

        $this->assertSame('item-2', $item->id);
        $this->assertSame('12:00', $item->startTime);
        $this->assertSame('13:00', $item->endTime);
        $this->assertSame('Lunch', $item->title);
        $this->assertSame(['personal'], $item->tags);
        $this->assertSame('done', $item->status);
    }

    public function testFromArrayUsesDefaults(): void
    {
        $item = ScheduleItem::fromArray([
            'id' => 'item-3',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'title' => 'Minimal',
        ]);

        $this->assertSame([], $item->tags);
        $this->assertSame('pending', $item->status);
    }

    public function testArrayRoundTrip(): void
    {
        $item = $this->createItem();

        $restored = ScheduleItem::fromArray($item->toArray());

        $this->assertSame($item->toArray(), $restored->toArray());
    }
}
