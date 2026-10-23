<?php

namespace Planner\Infrastructure\Console\Command\Task;

use Planner\Infrastructure\Console\Command\AbstractPlannerCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

class TaskUndoneCommand extends AbstractPlannerCommand
{
    protected function configure(): void
    {
        $this
            ->setName('task:undone')
            ->setDescription('Mark task as pending')
            ->addArgument('id', InputArgument::REQUIRED, 'Task ID')
            ->addOption('date', 'd', InputOption::VALUE_REQUIRED, 'Date', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $date = $input->getOption('date') ?? $this->getTodayDate();
        $id = $input->getArgument('id');

        $success = $this->plannerService->markFlexibleTaskPending($date, $id);

        if ($success) {
            $output->writeln("<info>✓ Marked task $id as pending</info>");
            return Command::SUCCESS;
        } else {
            $output->writeln("<error>Task $id not found</error>");
            return Command::FAILURE;
        }
    }
}
