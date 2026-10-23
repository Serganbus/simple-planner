<?php

namespace Planner\Domain\Model;

class FlexibleTask
{
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly int $estimatedMinutes,
        public readonly string $priority,
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
            'title' => $this->title,
            'estimated_minutes' => $this->estimatedMinutes,
            'priority' => $this->priority,
            'tags' => $this->tags,
            'status' => $this->status,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['title'],
            $data['estimated_minutes'],
            $data['priority'] ?? 'medium',
            $data['tags'] ?? [],
            $data['status'] ?? 'pending'
        );
    }
}
