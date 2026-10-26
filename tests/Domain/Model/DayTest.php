<?php

declare(strict_types=1);

namespace Planner\Tests\Domain\Model;

use Planner\Domain\Model\Day;
use Planner\Domain\Model\FlexibleTask;
use Planner\Domain\Model\ScheduleItem;
use PHPUnit\Framework\TestCase;

class DayTest extends TestCase
{
    private function createScheduleItem(string $id): ScheduleItem
    {
        return new ScheduleItem($id, '09:00', '10:00', "Item $id", [], 'pending');
    }

    private function createFlexibleTask(string $id): FlexibleTask
    {
        return new FlexibleTask($id, "Task $id", 30, 'medium', [], 'pending');
    }

    public function testConstructorSetsProperties(): void
    {
        $day = new Day('2026-10-26', 'Main goal');

        $this->assertSame('2026-10-26', $day->date);
        $this->assertSame('Main goal', $day->mainGoal);
        $this->assertSame([], $day->getSchedule());
        $this->assertSame([], $day->getFlexibleTasks());
    }

    public function testMainGoalHasDefaultValue(): void
    {
        $day = new Day('2026-10-26');

        $this->assertSame('', $day->mainGoal);
    }

    public function testAddAndGetScheduleItem(): void
    {
        $day = new Day('2026-10-26');
        $item = $this->createScheduleItem('a');

        $day->addScheduleItem($item);

        $this->assertSame([$item], $day->getSchedule());
        $this->assertSame($item, $day->getScheduleItem('a'));
    }

    public function testGetScheduleItemReturnsNullForUnknownId(): void
    {
        $day = new Day('2026-10-26');
        $day->addScheduleItem($this->createScheduleItem('a'));

        $this->assertNull($day->getScheduleItem('unknown'));
    }

    public function testAddAndGetFlexibleTask(): void
    {
        $day = new Day('2026-10-26');
        $task = $this->createFlexibleTask('t1');

        $day->addFlexibleTask($task);

        $this->assertSame([$task], $day->getFlexibleTasks());
        $this->assertSame($task, $day->getFlexibleTask('t1'));
    }

    public function testGetFlexibleTaskReturnsNullForUnknownId(): void
    {
        $day = new Day('2026-10-26');
        $day->addFlexibleTask($this->createFlexibleTask('t1'));

        $this->assertNull($day->getFlexibleTask('unknown'));
    }

    public function testRemoveScheduleItem(): void
    {
        $day = new Day('2026-10-26');
        $day->addScheduleItem($this->createScheduleItem('a'));
        $day->addScheduleItem($this->createScheduleItem('b'));
        $day->addScheduleItem($this->createScheduleItem('c'));

        $this->assertTrue($day->removeScheduleItem('b'));

        $this->assertCount(2, $day->getSchedule());
        $this->assertNull($day->getScheduleItem('b'));
        // Индексы переиндексируются
        $this->assertSame(['a', 'c'], array_map(fn($i) => $i->id, $day->getSchedule()));
    }

    public function testRemoveScheduleItemReturnsFalseForUnknownId(): void
    {
        $day = new Day('2026-10-26');
        $day->addScheduleItem($this->createScheduleItem('a'));

        $this->assertFalse($day->removeScheduleItem('unknown'));
        $this->assertCount(1, $day->getSchedule());
    }

    public function testRemoveFlexibleTask(): void
    {
        $day = new Day('2026-10-26');
        $day->addFlexibleTask($this->createFlexibleTask('t1'));
        $day->addFlexibleTask($this->createFlexibleTask('t2'));

        $this->assertTrue($day->removeFlexibleTask('t1'));

        $this->assertCount(1, $day->getFlexibleTasks());
        $this->assertNull($day->getFlexibleTask('t1'));
    }

    public function testRemoveFlexibleTaskReturnsFalseForUnknownId(): void
    {
        $day = new Day('2026-10-26');

        $this->assertFalse($day->removeFlexibleTask('unknown'));
    }

    public function testToArray(): void
    {
        $day = new Day('2026-10-26', 'Goal');
        $day->addScheduleItem($this->createScheduleItem('a'));
        $day->addFlexibleTask($this->createFlexibleTask('t1'));

        $data = $day->toArray();

        $this->assertSame('2026-10-26', $data['date']);
        $this->assertSame('Goal', $data['main_goal']);
        $this->assertCount(1, $data['schedule']);
        $this->assertSame('a', $data['schedule'][0]['id']);
        $this->assertCount(1, $data['flexible_tasks']);
        $this->assertSame('t1', $data['flexible_tasks'][0]['id']);
    }

    public function testFromArray(): void
    {
        $day = Day::fromArray([
            'date' => '2026-10-26',
            'main_goal' => 'Goal',
            'schedule' => [
                ['id' => 'a', 'start_time' => '09:00', 'end_time' => '10:00', 'title' => 'Item a', 'tags' => [], 'status' => 'pending'],
            ],
            'flexible_tasks' => [
                ['id' => 't1', 'title' => 'Task t1', 'estimated_minutes' => 30, 'priority' => 'medium', 'tags' => [], 'status' => 'pending'],
            ],
        ]);

        $this->assertSame('2026-10-26', $day->date);
        $this->assertSame('Goal', $day->mainGoal);
        $this->assertNotNull($day->getScheduleItem('a'));
        $this->assertNotNull($day->getFlexibleTask('t1'));
    }

    public function testFromArrayWithMissingOptionalKeys(): void
    {
        $day = Day::fromArray(['date' => '2026-10-26']);

        $this->assertSame('', $day->mainGoal);
        $this->assertSame([], $day->getSchedule());
        $this->assertSame([], $day->getFlexibleTasks());
    }

    public function testArrayRoundTrip(): void
    {
        $day = new Day('2026-10-26', 'Goal');
        $day->addScheduleItem($this->createScheduleItem('a'));
        $day->addFlexibleTask($this->createFlexibleTask('t1'));

        $restored = Day::fromArray($day->toArray());

        $this->assertSame($day->toArray(), $restored->toArray());
    }
}
