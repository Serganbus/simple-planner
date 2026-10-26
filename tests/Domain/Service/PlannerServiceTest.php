<?php

declare(strict_types=1);

namespace Planner\Tests\Domain\Service;

use Planner\Domain\Model\Day;
use Planner\Domain\Repository\DayRepositoryInterface;
use Planner\Domain\Service\PlannerService;
use PHPUnit\Framework\TestCase;

class InMemoryDayRepository implements DayRepositoryInterface
{
    /** @var array<string, Day> */
    private array $days = [];

    public function findByDate(string $date): ?Day
    {
        return $this->days[$date] ?? null;
    }

    public function save(Day $day): void
    {
        $this->days[$day->date] = $day;
    }

    public function delete(string $date): void
    {
        unset($this->days[$date]);
    }

    public function exists(string $date): bool
    {
        return isset($this->days[$date]);
    }
}

class PlannerServiceTest extends TestCase
{
    private const DATE = '2026-10-26';

    private PlannerService $service;

    protected function setUp(): void
    {
        $this->service = new PlannerService(new InMemoryDayRepository());
    }

    public function testGetDayReturnsNullWhenNotExists(): void
    {
        $this->assertNull($this->service->getDay(self::DATE));
    }

    public function testGetOrCreateDayCreatesAndSavesNewDay(): void
    {
        $day = $this->service->getOrCreateDay(self::DATE);

        $this->assertSame(self::DATE, $day->date);
        $this->assertSame($day, $this->service->getDay(self::DATE));
    }

    public function testGetOrCreateDayReturnsExistingDay(): void
    {
        $first = $this->service->getOrCreateDay(self::DATE);

        $second = $this->service->getOrCreateDay(self::DATE);

        $this->assertSame($first, $second);
    }

    public function testCreateDay(): void
    {
        $day = $this->service->createDay(self::DATE, 'My goal');

        $this->assertSame(self::DATE, $day->date);
        $this->assertSame('My goal', $day->mainGoal);
        $this->assertSame($day, $this->service->getDay(self::DATE));
    }

    public function testAddScheduleItemCreatesDayIfNeeded(): void
    {
        $item = $this->service->addScheduleItem(self::DATE, '09:00', '10:00', 'Meeting');

        $this->assertNotEmpty($item->id);
        $this->assertSame('09:00', $item->startTime);
        $this->assertSame('10:00', $item->endTime);
        $this->assertSame('Meeting', $item->title);
        $this->assertSame('pending', $item->status);
        $this->assertSame($item, $this->service->getDay(self::DATE)->getScheduleItem($item->id));
    }

    public function testAddScheduleItemWithTagsAndStatus(): void
    {
        $item = $this->service->addScheduleItem(self::DATE, '09:00', '10:00', 'Meeting', ['work'], 'done');

        $this->assertSame(['work'], $item->tags);
        $this->assertSame('done', $item->status);
    }

    public function testUpdateScheduleItem(): void
    {
        $item = $this->service->addScheduleItem(self::DATE, '09:00', '10:00', 'Meeting', ['work']);

        $result = $this->service->updateScheduleItem(
            self::DATE,
            $item->id,
            startTime: '11:00',
            endTime: '12:00',
            title: 'Updated meeting',
            tags: ['updated']
        );

        $this->assertTrue($result);
        $updated = $this->service->getDay(self::DATE)->getScheduleItem($item->id);
        $this->assertSame('11:00', $updated->startTime);
        $this->assertSame('12:00', $updated->endTime);
        $this->assertSame('Updated meeting', $updated->title);
        $this->assertSame(['updated'], $updated->tags);
    }

    public function testUpdateScheduleItemUpdatesOnlyProvidedFields(): void
    {
        $item = $this->service->addScheduleItem(self::DATE, '09:00', '10:00', 'Meeting', ['work']);

        $result = $this->service->updateScheduleItem(self::DATE, $item->id, title: 'New title');

        $this->assertTrue($result);
        $updated = $this->service->getDay(self::DATE)->getScheduleItem($item->id);
        $this->assertSame('New title', $updated->title);
        $this->assertSame('09:00', $updated->startTime);
        $this->assertSame('10:00', $updated->endTime);
        $this->assertSame(['work'], $updated->tags);
    }

    public function testUpdateScheduleItemReturnsFalseWhenDayNotFound(): void
    {
        $this->assertFalse($this->service->updateScheduleItem('2099-01-01', 'some-id', title: 'X'));
    }

    public function testUpdateScheduleItemReturnsFalseWhenItemNotFound(): void
    {
        $this->service->createDay(self::DATE);

        $this->assertFalse($this->service->updateScheduleItem(self::DATE, 'unknown-id', title: 'X'));
    }

    public function testRemoveScheduleItem(): void
    {
        $item = $this->service->addScheduleItem(self::DATE, '09:00', '10:00', 'Meeting');

        $this->assertTrue($this->service->removeScheduleItem(self::DATE, $item->id));
        $this->assertNull($this->service->getDay(self::DATE)->getScheduleItem($item->id));
    }

    public function testRemoveScheduleItemReturnsFalseWhenDayNotFound(): void
    {
        $this->assertFalse($this->service->removeScheduleItem('2099-01-01', 'some-id'));
    }

    public function testRemoveScheduleItemReturnsFalseWhenItemNotFound(): void
    {
        $this->service->createDay(self::DATE);

        $this->assertFalse($this->service->removeScheduleItem(self::DATE, 'unknown-id'));
    }

    public function testMarkScheduleItemDone(): void
    {
        $item = $this->service->addScheduleItem(self::DATE, '09:00', '10:00', 'Meeting');

        $this->assertTrue($this->service->markScheduleItemDone(self::DATE, $item->id));
        $this->assertSame('done', $this->service->getDay(self::DATE)->getScheduleItem($item->id)->status);
    }

    public function testMarkScheduleItemDoneReturnsFalseWhenDayNotFound(): void
    {
        $this->assertFalse($this->service->markScheduleItemDone('2099-01-01', 'some-id'));
    }

    public function testMarkScheduleItemDoneReturnsFalseWhenItemNotFound(): void
    {
        $this->service->createDay(self::DATE);

        $this->assertFalse($this->service->markScheduleItemDone(self::DATE, 'unknown-id'));
    }

    public function testMarkScheduleItemPending(): void
    {
        $item = $this->service->addScheduleItem(self::DATE, '09:00', '10:00', 'Meeting');
        $this->service->markScheduleItemDone(self::DATE, $item->id);

        $this->assertTrue($this->service->markScheduleItemPending(self::DATE, $item->id));
        $this->assertSame('pending', $this->service->getDay(self::DATE)->getScheduleItem($item->id)->status);
    }

    public function testAddFlexibleTaskCreatesDayIfNeeded(): void
    {
        $task = $this->service->addFlexibleTask(self::DATE, 'Write report', 45);

        $this->assertNotEmpty($task->id);
        $this->assertSame('Write report', $task->title);
        $this->assertSame(45, $task->estimatedMinutes);
        $this->assertSame('medium', $task->priority);
        $this->assertSame('pending', $task->status);
        $this->assertSame($task, $this->service->getDay(self::DATE)->getFlexibleTask($task->id));
    }

    public function testAddFlexibleTaskWithAllParameters(): void
    {
        $task = $this->service->addFlexibleTask(self::DATE, 'Write report', 45, 'high', ['work'], 'done');

        $this->assertSame('high', $task->priority);
        $this->assertSame(['work'], $task->tags);
        $this->assertSame('done', $task->status);
    }

    public function testUpdateFlexibleTask(): void
    {
        $task = $this->service->addFlexibleTask(self::DATE, 'Write report', 45, 'low', ['work']);

        $result = $this->service->updateFlexibleTask(
            self::DATE,
            $task->id,
            title: 'Updated report',
            estimatedMinutes: 60,
            priority: 'high',
            tags: ['updated']
        );

        $this->assertTrue($result);
        $updated = $this->service->getDay(self::DATE)->getFlexibleTask($task->id);
        $this->assertSame('Updated report', $updated->title);
        $this->assertSame(60, $updated->estimatedMinutes);
        $this->assertSame('high', $updated->priority);
        $this->assertSame(['updated'], $updated->tags);
    }

    public function testUpdateFlexibleTaskUpdatesOnlyProvidedFields(): void
    {
        $task = $this->service->addFlexibleTask(self::DATE, 'Write report', 45, 'low', ['work']);

        $result = $this->service->updateFlexibleTask(self::DATE, $task->id, priority: 'high');

        $this->assertTrue($result);
        $updated = $this->service->getDay(self::DATE)->getFlexibleTask($task->id);
        $this->assertSame('Write report', $updated->title);
        $this->assertSame(45, $updated->estimatedMinutes);
        $this->assertSame('high', $updated->priority);
        $this->assertSame(['work'], $updated->tags);
    }

    public function testUpdateFlexibleTaskReturnsFalseWhenDayNotFound(): void
    {
        $this->assertFalse($this->service->updateFlexibleTask('2099-01-01', 'some-id', title: 'X'));
    }

    public function testUpdateFlexibleTaskReturnsFalseWhenTaskNotFound(): void
    {
        $this->service->createDay(self::DATE);

        $this->assertFalse($this->service->updateFlexibleTask(self::DATE, 'unknown-id', title: 'X'));
    }

    public function testRemoveFlexibleTask(): void
    {
        $task = $this->service->addFlexibleTask(self::DATE, 'Write report', 45);

        $this->assertTrue($this->service->removeFlexibleTask(self::DATE, $task->id));
        $this->assertNull($this->service->getDay(self::DATE)->getFlexibleTask($task->id));
    }

    public function testRemoveFlexibleTaskReturnsFalseWhenDayNotFound(): void
    {
        $this->assertFalse($this->service->removeFlexibleTask('2099-01-01', 'some-id'));
    }

    public function testRemoveFlexibleTaskReturnsFalseWhenTaskNotFound(): void
    {
        $this->service->createDay(self::DATE);

        $this->assertFalse($this->service->removeFlexibleTask(self::DATE, 'unknown-id'));
    }

    public function testMarkFlexibleTaskDone(): void
    {
        $task = $this->service->addFlexibleTask(self::DATE, 'Write report', 45);

        $this->assertTrue($this->service->markFlexibleTaskDone(self::DATE, $task->id));
        $this->assertSame('done', $this->service->getDay(self::DATE)->getFlexibleTask($task->id)->status);
    }

    public function testMarkFlexibleTaskDoneReturnsFalseWhenDayNotFound(): void
    {
        $this->assertFalse($this->service->markFlexibleTaskDone('2099-01-01', 'some-id'));
    }

    public function testMarkFlexibleTaskDoneReturnsFalseWhenTaskNotFound(): void
    {
        $this->service->createDay(self::DATE);

        $this->assertFalse($this->service->markFlexibleTaskDone(self::DATE, 'unknown-id'));
    }

    public function testMarkFlexibleTaskPending(): void
    {
        $task = $this->service->addFlexibleTask(self::DATE, 'Write report', 45);
        $this->service->markFlexibleTaskDone(self::DATE, $task->id);

        $this->assertTrue($this->service->markFlexibleTaskPending(self::DATE, $task->id));
        $this->assertSame('pending', $this->service->getDay(self::DATE)->getFlexibleTask($task->id)->status);
    }

    public function testGetStatsWhenDayNotFound(): void
    {
        $stats = $this->service->getStats('2099-01-01');

        $this->assertSame(
            [
                'schedule_total' => 0,
                'schedule_done' => 0,
                'tasks_total' => 0,
                'tasks_done' => 0,
                'total_estimated_minutes' => 0,
            ],
            $stats
        );
    }

    public function testGetStats(): void
    {
        $item1 = $this->service->addScheduleItem(self::DATE, '09:00', '10:00', 'Meeting');
        $this->service->addScheduleItem(self::DATE, '10:00', '11:00', 'Review');
        $this->service->markScheduleItemDone(self::DATE, $item1->id);

        $task1 = $this->service->addFlexibleTask(self::DATE, 'Task 1', 30);
        $task2 = $this->service->addFlexibleTask(self::DATE, 'Task 2', 45);
        $this->service->addFlexibleTask(self::DATE, 'Task 3', 15);
        $this->service->markFlexibleTaskDone(self::DATE, $task1->id);
        $this->service->markFlexibleTaskDone(self::DATE, $task2->id);

        $stats = $this->service->getStats(self::DATE);

        $this->assertSame(
            [
                'schedule_total' => 2,
                'schedule_done' => 1,
                'tasks_total' => 3,
                'tasks_done' => 2,
                'total_estimated_minutes' => 90,
            ],
            $stats
        );
    }
}
