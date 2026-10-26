<?php

declare(strict_types=1);

namespace Planner\Infrastructure\Console\Command\Schedule;

use Planner\Infrastructure\Console\Command\AbstractPlannerCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

class ScheduleAddCommand extends AbstractPlannerCommand
{
    protected function configure(): void
    {
        $this
            ->setName('schedule:add')
            ->setDescription('Add a task to schedule')
            ->addOption('date', 'd', InputOption::VALUE_REQUIRED, 'Date', null)
            ->addOption('start', 's', InputOption::VALUE_REQUIRED, 'Start time (HH:MM)')
            ->addOption('end', 'e', InputOption::VALUE_REQUIRED, 'End time (HH:MM)')
            ->addOption('title', 't', InputOption::VALUE_REQUIRED, 'Task title')
            ->addOption('tags', null, InputOption::VALUE_REQUIRED, 'Comma-separated tags', '')
            ->addOption('status', null, InputOption::VALUE_REQUIRED, 'Status (pending|done)', 'pending');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $date = $input->getOption('date') ?? $this->getTodayDate();
        $startTime = $input->getOption('start');
        $endTime = $input->getOption('end');
        $title = $input->getOption('title');
        $tagsStr = $input->getOption('tags');
        $status = $input->getOption('status');

        if (!$startTime || !$endTime || !$title) {
            $output->writeln('<error>Options --start, --end, and --title are required</error>');
            return Command::FAILURE;
        }

        $tags = array_filter(array_map('trim', explode(',', $tagsStr)));
        
        $item = $this->plannerService->addScheduleItem($date, $startTime, $endTime, $title, $tags, $status);
        
        $output->writeln("<info>✓ Added schedule item: {$item->id}</info>");

        return Command::SUCCESS;
    }
}
