<?php

declare(strict_types=1);

namespace Ochorocho\TdkComposer\Command;

use Composer\Command\BaseCommand;
use Composer\Util\ProcessExecutor;
use Ochorocho\TdkComposer\Service\BaseService;
use Ochorocho\TdkComposer\Service\ValidationService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class DdevConfigCommand extends BaseCommand
{
    protected OutputInterface $output;

    protected function configure()
    {
        $this
            ->setName('tdk:ddev')
            ->setDescription('Delete TYPO3 installation in this TDK (files and folders only)')
            ->addOption('project-name', null, InputOption::VALUE_OPTIONAL, 'Set project name')
            ->addOption('no', null, InputOption::VALUE_NONE, 'Set all to no')
            ->setHelp(<<<EOT
Deletes all files and folders created/downloaded by "composer tdk:*" commands. 
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Only ask for ddev config if ddev command is available
        $windows = strpos(PHP_OS, 'WIN') === 0;
        $test = $windows ? 'where' : 'command -v';

        if (is_executable(trim(shell_exec($test . ' ddev') ?? ''))) {
            $ddevProjectName = $input->getOption('project-name') ?? getenv('TDK_CREATE_DDEV_PROJECT_NAME') ?? false;
            if (!$ddevProjectName) {
                $skip = $input->getOption('no') ?? false;
                if ($skip) {
                    $createConfig = false;
                } else {
                    $createConfig = $this->getIO()->askConfirmation('Create a basic ddev config [<fg=cyan;options=bold>y</>/n]? ');
                }

                if (!$createConfig) {
                    $this->getIO()->write('<warning>Aborted! No ddev config created.</warning>');
                    return Command::SUCCESS;
                }
            }

            $validationService = new ValidationService($this->getIO(), $this->requireComposer());
            $validator = $validationService->projectName();

            if (!$ddevProjectName) {
                $defaultProjectName = basename(getcwd());
                $ddevProjectName = $this->getIO()->askAndValidate('Choose a ddev project name [default: ' . $defaultProjectName . '] :', $validator, 2, $defaultProjectName);
            } else {
                try {
                    $ddevProjectName = $validator($ddevProjectName);
                } catch (\UnexpectedValueException $e) {
                    $this->getIO()->write('<error>' . $e->getMessage() . '</error>');
                    return Command::FAILURE;
                }
            }

            $phpVersion = BaseService::getPhpVersion();
            $ddevCommand = 'ddev config --docroot public --project-name ' . $ddevProjectName . ' --web-environment-add TYPO3_CONTEXT=Development --project-type typo3 --php-version ' . $phpVersion . ' --create-docroot 1> /dev/null';

            return (new ProcessExecutor())->execute($ddevCommand, $output);
        }

        $this->getIO()->write('No ddev binary found.');

        return Command::SUCCESS;
    }
}
