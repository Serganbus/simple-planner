<?php

namespace Planner\Infrastructure\Console\Command\Schedule;

use Planner\Infrastructure\Console\Command\AbstractPlannerCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

class ScheduleEditCommand extends AbstractPlannerCommand
{
    protected function configure(): void
    {
        $this
            ->setName('schedule:edit')
            ->setDescription('Edit a schedule item')
            ->addArgument('id', InputArgument::REQUIRED, 'Item ID')
            ->addOption('date', 'd', InputOption::VALUE_REQUIRED, 'Date', null)
            ->addOption('start', 's', InputOption::VALUE_REQUIRED, 'Start time')
            ->addOption('end', 'e', InputOption::VALUE_REQUIRED, 'End time')
            ->addOption('title', 't', InputOption::VALUE_REQUIRED, 'Title')
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

        $success = $this->plannerService->updateScheduleItem(
            $date,
            $id,
            $input->getOption('start'),
            $input->getOption('end'),
            $input->getOption('title'),
            $tags
        );

        if ($success) {
            $output->writeln("<info>✓ Updated schedule item $id</info>");
            return Command::SUCCESS;
        } else {
            $output->writeln("<error>Item $id not found</error>");
            return Command::FAILURE;
        }
    }
}
