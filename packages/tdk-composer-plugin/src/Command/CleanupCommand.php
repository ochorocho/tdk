<?php

declare(strict_types=1);

namespace Ochorocho\TdkComposer\Command;

use Composer\Command\BaseCommand;
use Composer\Script\Event;
use Composer\Util\ProcessExecutor;
use Ochorocho\TdkComposer\Service\BaseService;
use Ochorocho\TdkComposer\Service\GitService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

final class CleanupCommand extends BaseCommand
{
    protected OutputInterface $output;

    protected function configure()
    {
        $this
            ->setName('tdk-plugin:cleanup')
            ->setDescription('Delete TYPO3 installation in this TDK (files and folders only)')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force to run delete without confirmation')
            ->setHelp(<<<EOT
Deletes all files and folders created/downloaded by "composer tdk:*" commands. 
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filesToDelete = [
            'composer.lock',
            'public/index.php',
            'public/typo3',
            BaseService::CORE_DEV_FOLDER,
            'var',
        ];

        $force = $input->getOption('force');

        if ($force) {
            $answer = true;
        } else {
            $answer = $this->getIO()->askConfirmation('Really want to delete ' . implode(', ', $filesToDelete) . '? [y/<fg=cyan;options=bold>n</>] ', false);
        }

        if ($answer) {
            $filesystem = new Filesystem();
            $filesystem->remove($filesToDelete);
            $this->getIO()->write(BaseService::ICON_SUCCESS . 'Done deleting files.');
        }

        return Command::SUCCESS;
    }
}
