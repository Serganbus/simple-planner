<?php

declare(strict_types=1);

namespace Planner\Infrastructure\Console\Command\Schedule;

use Planner\Infrastructure\Console\Command\AbstractPlannerCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

class ScheduleRemoveCommand extends AbstractPlannerCommand
{
    protected function configure(): void
    {
        $this
            ->setName('schedule:remove')
            ->setDescription('Remove a schedule item')
            ->addArgument('id', InputArgument::REQUIRED, 'Item ID')
            ->addOption('date', 'd', InputOption::VALUE_REQUIRED, 'Date', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $date = $input->getOption('date') ?? $this->getTodayDate();
        $id = $input->getArgument('id');

        $success = $this->plannerService->removeScheduleItem($date, $id);

        if ($success) {
            $output->writeln("<info>✓ Removed schedule item $id</info>");
            return Command::SUCCESS;
        } else {
            $output->writeln("<error>Item $id not found</error>");
            return Command::FAILURE;
        }
    }
}
