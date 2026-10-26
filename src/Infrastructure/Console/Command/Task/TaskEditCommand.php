<?php

declare(strict_types=1);

namespace Planner\Infrastructure\Console\Command\Task;

use Planner\Infrastructure\Console\Command\AbstractPlannerCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

class TaskEditCommand extends AbstractPlannerCommand
{
    protected function configure(): void
    {
        $this
            ->setName('task:edit')
            ->setDescription('Edit a flexible task')
            ->addArgument('id', InputArgument::REQUIRED, 'Task ID')
            ->addOption('date', 'd', InputOption::VALUE_REQUIRED, 'Date', null)
            ->addOption('title', 't', InputOption::VALUE_REQUIRED, 'Title')
            ->addOption('minutes', 'm', InputOption::VALUE_REQUIRED, 'Estimated minutes')
            ->addOption('priority', 'p', InputOption::VALUE_REQUIRED, 'Priority')
            ->addOption('tags', null, InputOption::VALUE_REQUIRED, 'Comma-separated tags');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $date = $input->getOption('date') ?? $this->getTodayDate();
        $id = $input->getArgument('id');
        
        $tags = null;
        if ($input->getOption('tags') !== null) {
            $tags = array_filter(array_map('trim', explode(',', $input->getOption('tags'))));
        }

        $success = $this->plannerService->updateFlexibleTask(
            $date,
            $id,
            $input->getOption('title'),
            $input->getOption('minutes') !== null ? (int)$input->getOption('minutes') : null,
            $input->getOption('priority'),
            $tags
        );

        if ($success) {
            $output->writeln("<info>✓ Updated task $id</info>");
            return Command::SUCCESS;
        } else {
            $output->writeln("<error>Task $id not found</error>");
            return Command::FAILURE;
        }
    }
}
