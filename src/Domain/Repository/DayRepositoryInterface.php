<?php

declare(strict_types=1);

namespace Planner\Domain\Repository;

use Planner\Domain\Model\Day;

interface DayRepositoryInterface
{
    public function findByDate(string $date): ?Day;
    public function save(Day $day): void;
    public function delete(string $date): void;
    public function exists(string $date): bool;
}
