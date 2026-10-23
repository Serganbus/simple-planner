<?php

namespace Planner\Infrastructure\Console\Command\Day;

use Planner\Infrastructure\Console\Command\AbstractPlannerCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

class DayCreateCommand extends AbstractPlannerCommand
{
    protected function configure(): void
    {
        $this
            ->setName('day:create')
            ->setDescription('Create a new day plan')
            ->addArgument('date', InputArgument::OPTIONAL, 'Date in Y-m-d format', null)
            ->addOption('goal', 'g', InputOption::VALUE_REQUIRED, 'Main goal for the day', '');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $date = $input->getArgument('date') ?? $this->getTodayDate();
        $goal = $input->getOption('goal');

        if ($this->plannerService->getDay($date) !== null) {
            $output->writeln("<comment>Plan for $date already exists</comment>");
            return Command::SUCCESS;
        }

        $this->plannerService->createDay($date, $goal);
        $output->writeln("<info>✓ Created plan for $date</info>");

        return Command::SUCCESS;
    }
}
