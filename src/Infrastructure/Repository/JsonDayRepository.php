<?php

namespace Planner\Infrastructure\Repository;

use Planner\Domain\Model\Day;
use Planner\Domain\Repository\DayRepositoryInterface;

class JsonDayRepository implements DayRepositoryInterface
{
    public function __construct(
        private string $dataPath
    ) {
        if (!is_dir($this->dataPath)) {
            mkdir($this->dataPath, 0755, true);
        }
    }

    public function findByDate(string $date): ?Day
    {
        $filePath = $this->getFilePath($date);
        if (!file_exists($filePath)) {
            return null;
        }

        $content = file_get_contents($filePath);
        $data = json_decode($content, true);
        
        return Day::fromArray($data);
    }

    public function save(Day $day): void
    {
        $filePath = $this->getFilePath($day->date);
        $json = json_encode($day->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($filePath, $json);
    }

    public function delete(string $date): void
    {
        $filePath = $this->getFilePath($date);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    public function exists(string $date): bool
    {
        return file_exists($this->getFilePath($date));
    }

    private function getFilePath(string $date): string
    {
        return $this->dataPath . '/' . $date . '.json';
    }
}
