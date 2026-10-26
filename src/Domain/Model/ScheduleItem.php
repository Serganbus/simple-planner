<?php

declare(strict_types=1);

namespace Planner\Domain\Model;

class ScheduleItem
{
    public function __construct(
        public readonly string $id,
        public string $startTime,
        public string $endTime,
        public string $title,
        public array $tags,
        public string $status
    ) {}

    public function markDone(): void
    {
        $this->status = 'done';
    }

    public function markPending(): void
    {
        $this->status = 'pending';
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'start_time' => $this->startTime,
            'end_time' => $this->endTime,
            'title' => $this->title,
            'tags' => $this->tags,
            'status' => $this->status,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['start_time'],
            $data['end_time'],
            $data['title'],
            $data['tags'] ?? [],
            $data['status'] ?? 'pending'
        );
    }
}
