<?php

namespace Planner\Infrastructure\Console\Command\Util;

use Planner\Infrastructure\Console\Command\AbstractPlannerCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

class UtilTagsCommand extends AbstractPlannerCommand
{
    protected function configure(): void
    {
        $this
            ->setName('util:tags')
            ->setDescription('List all unique tags');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $tags = [];
        
        // Сканируем все файлы в dataPath
        foreach (glob($this->dataPath . '/*.json') as $file) {
            $content = file_get_contents($file);
            $data = json_decode($content, true);
            
            foreach ($data['schedule'] ?? [] as $item) {
                foreach ($item['tags'] ?? [] as $tag) {
                    $tags[$tag] = true;
                }
            }
            
            foreach ($data['flexible_tasks'] ?? [] as $task) {
                foreach ($task['tags'] ?? [] as $tag) {
                    $tags[$tag] = true;
                }
            }
        }

        $output->writeln('<info>All tags:</info>');
        foreach (array_keys($tags) as $tag) {
            $output->writeln("  • $tag");
        }

        return Command::SUCCESS;
    }
}
