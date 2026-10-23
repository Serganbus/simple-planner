<?php

namespace Planner\Infrastructure\Console\Command\Day;

use Planner\Infrastructure\Console\Command\AbstractPlannerCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

class DayStatsCommand extends AbstractPlannerCommand
{
    protected function configure(): void
    {
        $this
            ->setName('day:stats')
            ->setDescription('Show statistics for a day')
            ->addArgument('date', InputArgument::OPTIONAL, 'Date in Y-m-d format', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $date = $input->getArgument('date') ?? $this->getTodayDate();
        $stats = $this->plannerService->getStats($date);

        $output->writeln("<info>📊 Statistics for $date</info>");
        $output->writeln("  Schedule: {$stats['schedule_done']}/{$stats['schedule_total']} done");
        $output->writeln("  Tasks: {$stats['tasks_done']}/{$stats['tasks_total']} done");
        $output->writeln("  Total estimated time: {$stats['total_estimated_minutes']} minutes");

        return Command::SUCCESS;
    }
}
