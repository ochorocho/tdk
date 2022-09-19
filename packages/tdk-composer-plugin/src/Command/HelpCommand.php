<?php

declare(strict_types=1);

namespace Ochorocho\TdkComposer\Command;

use Composer\Command\BaseCommand;
use Ochorocho\TdkComposer\Service\GitService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class HelpCommand extends BaseCommand
{
    protected OutputInterface $output;

    protected function configure()
    {
        $this
            ->setName('tdk:help')
            ->setDescription('Show details to get more information about contributing')
            ->addArgument('type', InputArgument::OPTIONAL, 'Which help text show.')
            ->setHelp(
                <<<EOT
Shows details/links about how to contribute to the TYPO3 core. 
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $action = $input->getArgument('type');
        $baseService = new GitService();

        switch ($action) {
            case 'summary':
                $output->writeln($baseService->summary());
                break;
            case 'done':
                $output->writeln($baseService->done());
                break;
        }

        return Command::SUCCESS;
    }
}
