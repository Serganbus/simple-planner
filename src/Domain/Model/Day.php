<?php

declare(strict_types=1);

namespace Planner\Domain\Model;

class Day
{
    /** @var ScheduleItem[] */
    private array $schedule = [];
    
    /** @var FlexibleTask[] */
    private array $flexibleTasks = [];

    public function __construct(
        public readonly string $date,
        public string $mainGoal = ''
    ) {}

    public function addScheduleItem(ScheduleItem $item): void
    {
        $this->schedule[] = $item;
    }

    public function addFlexibleTask(FlexibleTask $task): void
    {
        $this->flexibleTasks[] = $task;
    }

    public function getScheduleItem(string $id): ?ScheduleItem
    {
        foreach ($this->schedule as $item) {
            if ($item->id === $id) {
                return $item;
            }
        }
        return null;
    }

    public function getFlexibleTask(string $id): ?FlexibleTask
    {
        foreach ($this->flexibleTasks as $task) {
            if ($task->id === $id) {
                return $task;
            }
        }
        return null;
    }

    public function removeScheduleItem(string $id): bool
    {
        foreach ($this->schedule as $key => $item) {
            if ($item->id === $id) {
                unset($this->schedule[$key]);
                $this->schedule = array_values($this->schedule);
                return true;
            }
        }
        return false;
    }

    public function removeFlexibleTask(string $id): bool
    {
        foreach ($this->flexibleTasks as $key => $task) {
            if ($task->id === $id) {
                unset($this->flexibleTasks[$key]);
                $this->flexibleTasks = array_values($this->flexibleTasks);
                return true;
            }
        }
        return false;
    }

    /** @return ScheduleItem[] */
    public function getSchedule(): array
    {
        return $this->schedule;
    }

    /** @return FlexibleTask[] */
    public function getFlexibleTasks(): array
    {
        return $this->flexibleTasks;
    }

    public function toArray(): array
    {
        return [
            'date' => $this->date,
            'main_goal' => $this->mainGoal,
            'schedule' => array_map(fn($item) => $item->toArray(), $this->schedule),
            'flexible_tasks' => array_map(fn($task) => $task->toArray(), $this->flexibleTasks),
        ];
    }

    public static function fromArray(array $data): self
    {
        $day = new self($data['date'], $data['main_goal'] ?? '');
        
        foreach ($data['schedule'] ?? [] as $itemData) {
            $day->addScheduleItem(ScheduleItem::fromArray($itemData));
        }
        
        foreach ($data['flexible_tasks'] ?? [] as $taskData) {
            $day->addFlexibleTask(FlexibleTask::fromArray($taskData));
        }
        
        return $day;
    }
}
