<?php

declare(strict_types=1);

namespace Planner\Infrastructure\Console\Command\Schedule;

use Planner\Infrastructure\Console\Command\AbstractPlannerCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

class ScheduleDoneCommand extends AbstractPlannerCommand
{
    protected function configure(): void
    {
        $this
            ->setName('schedule:done')
            ->setDescription('Mark schedule item as done')
            ->addArgument('id', InputArgument::REQUIRED, 'Item ID')
            ->addOption('date', 'd', InputOption::VALUE_REQUIRED, 'Date', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $date = $input->getOption('date') ?? $this->getTodayDate();
        $id = $input->getArgument('id');

        $success = $this->plannerService->markScheduleItemDone($date, $id);

        if ($success) {
            $output->writeln("<info>✓ Marked schedule item $id as done</info>");
            return Command::SUCCESS;
        } else {
            $output->writeln("<error>Item $id not found</error>");
            return Command::FAILURE;
        }
    }
}
