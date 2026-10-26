<?php

declare(strict_types=1);

namespace Planner\Infrastructure\Console\Command\Day;

use Planner\Infrastructure\Console\Command\AbstractPlannerCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

class DayShowCommand extends AbstractPlannerCommand
{
    protected function configure(): void
    {
        $this
            ->setName('day:show')
            ->setDescription('Show plan for a day')
            ->addArgument('date', InputArgument::OPTIONAL, 'Date in Y-m-d format', null)
            ->addOption('schedule', null, InputOption::VALUE_NONE, 'Show only schedule')
            ->addOption('tasks', null, InputOption::VALUE_NONE, 'Show only flexible tasks')
            ->addOption('tag', null, InputOption::VALUE_REQUIRED, 'Filter by tag')
            ->addOption('pending', null, InputOption::VALUE_NONE, 'Show only pending items')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'Output format (text|json)', 'text');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $date = $input->getArgument('date') ?? $this->getTodayDate();
        $day = $this->plannerService->getDay($date);

        if ($day === null) {
            $output->writeln("<comment>No plan found for $date</comment>");
            return Command::SUCCESS;
        }

        $format = $input->getOption('format');
        
        if ($format === 'json') {
            $output->writeln(json_encode($day->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            return Command::SUCCESS;
        }

        $showSchedule = $input->getOption('schedule') || !$input->getOption('tasks');
        $showTasks = $input->getOption('tasks') || !$input->getOption('schedule');
        $tagFilter = $input->getOption('tag');
        $pendingOnly = $input->getOption('pending');

        $output->writeln("<info>📅 Plan for $date</info>");
        if ($day->mainGoal) {
            $output->writeln("<comment>🎯 Goal: {$day->mainGoal}</comment>");
        }
        $output->writeln('');

        if ($showSchedule) {
            $output->writeln('<info>⏰ Schedule:</info>');
            $schedule = $day->getSchedule();
            
            if ($tagFilter) {
                $schedule = array_filter($schedule, fn($item) => in_array($tagFilter, $item->tags));
            }
            if ($pendingOnly) {
                $schedule = array_filter($schedule, fn($item) => $item->status === 'pending');
            }

            if (empty($schedule)) {
                $output->writeln('  <comment>No items</comment>');
            } else {
                foreach ($schedule as $item) {
                    $statusIcon = $item->status === 'done' ? '✅' : '⏳';
                    $tags = empty($item->tags) ? '' : ' [' . implode(', ', $item->tags) . ']';
                    $output->writeln("  $statusIcon {$item->startTime}-{$item->endTime}: {$item->title}$tags <dim>({$item->id})</dim>");
                }
            }
            $output->writeln('');
        }

        if ($showTasks) {
            $output->writeln('<info>✅ Flexible Tasks:</info>');
            $tasks = $day->getFlexibleTasks();
            
            if ($tagFilter) {
                $tasks = array_filter($tasks, fn($task) => in_array($tagFilter, $task->tags));
            }
            if ($pendingOnly) {
                $tasks = array_filter($tasks, fn($task) => $task->status === 'pending');
            }

            if (empty($tasks)) {
                $output->writeln('  <comment>No tasks</comment>');
            } else {
                foreach ($tasks as $task) {
                    $statusIcon = $task->status === 'done' ? '✅' : '⏳';
                    $tags = empty($task->tags) ? '' : ' [' . implode(', ', $task->tags) . ']';
                    $output->writeln("  $statusIcon {$task->title} ({$task->estimatedMinutes}min, {$task->priority})$tags <dim>({$task->id})</dim>");
                }
            }
        }

        return Command::SUCCESS;
    }
}
