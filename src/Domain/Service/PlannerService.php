<?php

namespace Planner\Domain\Service;

use Planner\Domain\Model\Day;
use Planner\Domain\Model\ScheduleItem;
use Planner\Domain\Model\FlexibleTask;
use Planner\Domain\Repository\DayRepositoryInterface;
use Ramsey\Uuid\Uuid;

class PlannerService
{
    public function __construct(
        private DayRepositoryInterface $repository
    ) {}

    public function getOrCreateDay(string $date): Day
    {
        $day = $this->repository->findByDate($date);
        if ($day === null) {
            $day = new Day($date);
            $this->repository->save($day);
        }
        return $day;
    }

    public function getDay(string $date): ?Day
    {
        return $this->repository->findByDate($date);
    }

    public function createDay(string $date, string $mainGoal = ''): Day
    {
        $day = new Day($date, $mainGoal);
        $this->repository->save($day);
        return $day;
    }

    public function addScheduleItem(
        string $date,
        string $startTime,
        string $endTime,
        string $title,
        array $tags = [],
        string $status = 'pending'
    ): ScheduleItem {
        $day = $this->getOrCreateDay($date);
        $item = new ScheduleItem(
            Uuid::uuid4()->toString(),
            $startTime,
            $endTime,
            $title,
            $tags,
            $status
        );
        $day->addScheduleItem($item);
        $this->repository->save($day);
        return $item;
    }

    public function updateScheduleItem(
        string $date,
        string $id,
        ?string $startTime = null,
        ?string $endTime = null,
        ?string $title = null,
        ?array $tags = null
    ): bool {
        $day = $this->repository->findByDate($date);
        if ($day === null) {
            return false;
        }

        $item = $day->getScheduleItem($id);
        if ($item === null) {
            return false;
        }

        // Обновляем поля (используем reflection, т.к. свойства readonly)
        $reflection = new \ReflectionClass($item);
        
        if ($startTime !== null) {
            $prop = $reflection->getProperty('startTime');
            $prop->setAccessible(true);
            $prop->setValue($item, $startTime);
        }
        
        if ($endTime !== null) {
            $prop = $reflection->getProperty('endTime');
            $prop->setAccessible(true);
            $prop->setValue($item, $endTime);
        }
        
        if ($title !== null) {
            $prop = $reflection->getProperty('title');
            $prop->setAccessible(true);
            $prop->setValue($item, $title);
        }
        
        if ($tags !== null) {
            $item->tags = $tags;
        }

        $this->repository->save($day);
        return true;
    }

    public function removeScheduleItem(string $date, string $id): bool
    {
        $day = $this->repository->findByDate($date);
        if ($day === null) {
            return false;
        }

        $removed = $day->removeScheduleItem($id);
        if ($removed) {
            $this->repository->save($day);
        }
        return $removed;
    }

    public function markScheduleItemDone(string $date, string $id): bool
    {
        $day = $this->repository->findByDate($date);
        if ($day === null) {
            return false;
        }

        $item = $day->getScheduleItem($id);
        if ($item === null) {
            return false;
        }

        $item->markDone();
        $this->repository->save($day);
        return true;
    }

    public function markScheduleItemPending(string $date, string $id): bool
    {
        $day = $this->repository->findByDate($date);
        if ($day === null) {
            return false;
        }

        $item = $day->getScheduleItem($id);
        if ($item === null) {
            return false;
        }

        $item->markPending();
        $this->repository->save($day);
        return true;
    }

    public function addFlexibleTask(
        string $date,
        string $title,
        int $estimatedMinutes,
        string $priority = 'medium',
        array $tags = [],
        string $status = 'pending'
    ): FlexibleTask {
        $day = $this->getOrCreateDay($date);
        $task = new FlexibleTask(
            Uuid::uuid4()->toString(),
            $title,
            $estimatedMinutes,
            $priority,
            $tags,
            $status
        );
        $day->addFlexibleTask($task);
        $this->repository->save($day);
        return $task;
    }

    public function updateFlexibleTask(
        string $date,
        string $id,
        ?string $title = null,
        ?int $estimatedMinutes = null,
        ?string $priority = null,
        ?array $tags = null
    ): bool {
        $day = $this->repository->findByDate($date);
        if ($day === null) {
            return false;
        }

        $task = $day->getFlexibleTask($id);
        if ($task === null) {
            return false;
        }

        $reflection = new \ReflectionClass($task);
        
        if ($title !== null) {
            $prop = $reflection->getProperty('title');
            $prop->setAccessible(true);
            $prop->setValue($task, $title);
        }
        
        if ($estimatedMinutes !== null) {
            $prop = $reflection->getProperty('estimatedMinutes');
            $prop->setAccessible(true);
            $prop->setValue($task, $estimatedMinutes);
        }
        
        if ($priority !== null) {
            $prop = $reflection->getProperty('priority');
            $prop->setAccessible(true);
            $prop->setValue($task, $priority);
        }
        
        if ($tags !== null) {
            $task->tags = $tags;
        }

        $this->repository->save($day);
        return true;
    }

    public function removeFlexibleTask(string $date, string $id): bool
    {
        $day = $this->repository->findByDate($date);
        if ($day === null) {
            return false;
        }

        $removed = $day->removeFlexibleTask($id);
        if ($removed) {
            $this->repository->save($day);
        }
        return $removed;
    }

    public function markFlexibleTaskDone(string $date, string $id): bool
    {
        $day = $this->repository->findByDate($date);
        if ($day === null) {
            return false;
        }

        $task = $day->getFlexibleTask($id);
        if ($task === null) {
            return false;
        }

        $task->markDone();
        $this->repository->save($day);
        return true;
    }

    public function markFlexibleTaskPending(string $date, string $id): bool
    {
        $day = $this->repository->findByDate($date);
        if ($day === null) {
            return false;
        }

        $task = $day->getFlexibleTask($id);
        if ($task === null) {
            return false;
        }

        $task->markPending();
        $this->repository->save($day);
        return true;
    }

    public function getStats(string $date): array
    {
        $day = $this->repository->findByDate($date);
        if ($day === null) {
            return [
                'schedule_total' => 0,
                'schedule_done' => 0,
                'tasks_total' => 0,
                'tasks_done' => 0,
                'total_estimated_minutes' => 0,
            ];
        }

        $scheduleTotal = count($day->getSchedule());
        $scheduleDone = count(array_filter($day->getSchedule(), fn($item) => $item->status === 'done'));
        
        $tasksTotal = count($day->getFlexibleTasks());
        $tasksDone = count(array_filter($day->getFlexibleTasks(), fn($task) => $task->status === 'done'));
        
        $totalMinutes = array_sum(array_map(
            fn($task) => $task->estimatedMinutes,
            $day->getFlexibleTasks()
        ));

        return [
            'schedule_total' => $scheduleTotal,
            'schedule_done' => $scheduleDone,
            'tasks_total' => $tasksTotal,
            'tasks_done' => $tasksDone,
            'total_estimated_minutes' => $totalMinutes,
        ];
    }
}
