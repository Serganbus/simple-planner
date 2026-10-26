<?php

declare(strict_types=1);

namespace Planner\Infrastructure\Console\Command;

use Planner\Domain\Service\PlannerService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractPlannerCommand extends Command
{
    protected PlannerService $plannerService;
    protected string $dataPath;

    public function __construct(PlannerService $plannerService, string $dataPath)
    {
        parent::__construct();
        $this->plannerService = $plannerService;
        $this->dataPath = $dataPath;
    }

    protected function getTodayDate(): string
    {
        return date('Y-m-d');
    }
}
