<?php

declare(strict_types=1);

namespace Ochorocho\TdkComposer\Command;

use Composer\Command\BaseCommand;
use Ochorocho\TdkComposer\Service\ComposerService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ComposerCommand extends BaseCommand
{
    protected OutputInterface $output;
    protected ComposerService $composerService;

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->composerService = new ComposerService();

        parent::initialize($input, $output);
    }

    protected function configure()
    {
        $this
            ->setName('tdk:composer')
            ->setDescription('Manage TYPO3 Core packages with composer.')
            ->addArgument('action', InputArgument::OPTIONAL, 'Require/remove all TYPO3 system extensions')
            ->setHelp(
                <<<EOT
Just a handy command to require and remove all TYPO3 Core extensions.
 * require
 * remove
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $action = $input->getArgument('action');

        switch ($action) {
            case 'require':
                $this->composerService->requireAllCoreExtensions();
                break;
            case 'remove':
                $this->composerService->removeAllCoreExtensions();
                break;
            default:
                $this->getIO()->write($this->getHelp());
        }

        return Command::SUCCESS;
    }
}
