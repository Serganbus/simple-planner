<?php

declare(strict_types=1);

namespace Planner\Tests\Domain\Model;

use Planner\Domain\Model\FlexibleTask;
use PHPUnit\Framework\TestCase;

class FlexibleTaskTest extends TestCase
{
    private function createTask(): FlexibleTask
    {
        return new FlexibleTask(
            'task-1',
            'Write report',
            45,
            'high',
            ['work', 'writing'],
            'pending'
        );
    }

    public function testConstructorSetsProperties(): void
    {
        $task = $this->createTask();

        $this->assertSame('task-1', $task->id);
        $this->assertSame('Write report', $task->title);
        $this->assertSame(45, $task->estimatedMinutes);
        $this->assertSame('high', $task->priority);
        $this->assertSame(['work', 'writing'], $task->tags);
        $this->assertSame('pending', $task->status);
    }

    public function testMarkDone(): void
    {
        $task = $this->createTask();

        $task->markDone();

        $this->assertSame('done', $task->status);
    }

    public function testMarkPending(): void
    {
        $task = $this->createTask();
        $task->markDone();

        $task->markPending();

        $this->assertSame('pending', $task->status);
    }

    public function testToArray(): void
    {
        $task = $this->createTask();

        $this->assertSame(
            [
                'id' => 'task-1',
                'title' => 'Write report',
                'estimated_minutes' => 45,
                'priority' => 'high',
                'tags' => ['work', 'writing'],
                'status' => 'pending',
            ],
            $task->toArray()
        );
    }

    public function testFromArray(): void
    {
        $task = FlexibleTask::fromArray([
            'id' => 'task-2',
            'title' => 'Read book',
            'estimated_minutes' => 30,
            'priority' => 'low',
            'tags' => ['hobby'],
            'status' => 'done',
        ]);

        $this->assertSame('task-2', $task->id);
        $this->assertSame('Read book', $task->title);
        $this->assertSame(30, $task->estimatedMinutes);
        $this->assertSame('low', $task->priority);
        $this->assertSame(['hobby'], $task->tags);
        $this->assertSame('done', $task->status);
    }

    public function testFromArrayUsesDefaults(): void
    {
        $task = FlexibleTask::fromArray([
            'id' => 'task-3',
            'title' => 'Minimal',
            'estimated_minutes' => 10,
        ]);

        $this->assertSame('medium', $task->priority);
        $this->assertSame([], $task->tags);
        $this->assertSame('pending', $task->status);
    }

    public function testArrayRoundTrip(): void
    {
        $task = $this->createTask();

        $restored = FlexibleTask::fromArray($task->toArray());

        $this->assertSame($task->toArray(), $restored->toArray());
    }
}
