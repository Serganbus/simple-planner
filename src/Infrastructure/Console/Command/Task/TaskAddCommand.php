<?php

namespace Planner\Infrastructure\Console\Command\Task;

use Planner\Infrastructure\Console\Command\AbstractPlannerCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

class TaskAddCommand extends AbstractPlannerCommand
{
    protected function configure(): void
    {
        $this
            ->setName('task:add')
            ->setDescription('Add a flexible task')
            ->addOption('date', 'd', InputOption::VALUE_REQUIRED, 'Date', null)
            ->addOption('title', 't', InputOption::VALUE_REQUIRED, 'Task title')
            ->addOption('minutes', 'm', InputOption::VALUE_REQUIRED, 'Estimated minutes')
            ->addOption('priority', 'p', InputOption::VALUE_REQUIRED, 'Priority (high|medium|low)', 'medium')
            ->addOption('tags', null, InputOption::VALUE_REQUIRED, 'Comma-separated tags', '')
            ->addOption('status', null, InputOption::VALUE_REQUIRED, 'Status (pending|done)', 'pending');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $date = $input->getOption('date') ?? $this->getTodayDate();
        $title = $input->getOption('title');
        $minutes = $input->getOption('minutes');
        $priority = $input->getOption('priority');
        $tagsStr = $input->getOption('tags');
        $status = $input->getOption('status');

        if (!$title || !$minutes) {
            $output->writeln('<error>Options --title and --minutes are required</error>');
            return Command::FAILURE;
        }

        $tags = array_filter(array_map('trim', explode(',', $tagsStr)));
        
        $task = $this->plannerService->addFlexibleTask(
            $date, 
            $title, 
            (int)$minutes, 
            $priority, 
            $tags, 
            $status
        );
        
        $output->writeln("<info>✓ Added flexible task: {$task->id}</info>");

        return Command::SUCCESS;
    }
}
