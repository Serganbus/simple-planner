<?php

namespace Planner\Infrastructure\Console\Command\Task;

use Planner\Infrastructure\Console\Command\AbstractPlannerCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

class TaskRemoveCommand extends AbstractPlannerCommand
{
    protected function configure(): void
    {
        $this
            ->setName('task:remove')
            ->setDescription('Remove a flexible task')
            ->addArgument('id', InputArgument::REQUIRED, 'Task ID')
            ->addOption('date', 'd', InputOption::VALUE_REQUIRED, 'Date', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $date = $input->getOption('date') ?? $this->getTodayDate();
        $id = $input->getArgument('id');

        $success = $this->plannerService->removeFlexibleTask($date, $id);

        if ($success) {
            $output->writeln("<info>✓ Removed task $id</info>");
            return Command::SUCCESS;
        } else {
            $output->writeln("<error>Task $id not found</error>");
            return Command::FAILURE;
        }
    }
}
