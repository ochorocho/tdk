<?php

declare(strict_types=1);

namespace Ochorocho\TdkComposer\Command;

use Composer\Command\BaseCommand;
use Composer\Util\ProcessExecutor;
use Ochorocho\TdkComposer\Service\BaseService;
use Ochorocho\TdkComposer\Service\GitService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

final class DoctorCommand extends BaseCommand
{
    protected OutputInterface $output;

    protected function configure()
    {
        $this
            ->setName('tdk:doctor')
            ->setDescription('Test TYPO3 Development Kit setup')
            ->setHelp(
                <<<EOT
Test for files and folders required by the TYPO3 Development Kit 
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $code = Command::SUCCESS;
        $filesystem = new Filesystem();
        $coreDevFolder = BaseService::CORE_DEV_FOLDER;
        $iconSuccess = BaseService::ICON_SUCCESS;
        $iconFailed = BaseService::ICON_FAILED;

        // Test for existing repository
        if ($filesystem->exists($coreDevFolder . '/.git')) {
            $gitService = new GitService();
            $commit = $gitService->latestCommit();
            $this->getIO()->write($iconSuccess . 'Repository exists on commit ' . $commit);
        } else {
            $this->getIO()->write($iconFailed . 'Repository not in place, please run "composer tdk:git clone"');
            $code = Command::FAILURE;
        }

        // Test if hooks are set up
        if ($filesystem->exists([
            $coreDevFolder . '/.git/hooks/pre-commit',
            $coreDevFolder . '/.git/hooks/commit-msg',
        ])) {
            $this->getIO()->write($iconSuccess . 'All hooks are in place.');
        } else {
            $this->getIO()->write($iconFailed . 'Hooks are missing please run "composer tdk:hooks create".');
            $code = Command::FAILURE;
        }

        // Test git push url
        $process = new ProcessExecutor();
        $command = 'git config --get remote.origin.pushurl';
        $process->execute($command, $commandOutput, $coreDevFolder);

        preg_match('/^ssh:\/\/(.*)@review\.typo3\.org/', (string)$commandOutput, $matches);
        if (!empty($matches)) {
            $this->getIO()->write($iconSuccess . 'Git "remote.origin.pushurl" seems correct.');
        } else {
            $this->getIO()->write($iconFailed . 'Git "remote.origin.pushurl" not set correctly, please run "composer tdk:git config".');
            $code = Command::FAILURE;
        }

        // Test commit template
        $commandTemplate = 'git config --get commit.template';
        $process->execute($commandTemplate, $outputTemplate, $coreDevFolder);

        if (!empty($outputTemplate) && $filesystem->exists(trim($outputTemplate))) {
            $this->getIO()->write($iconSuccess . 'Git "commit.template" is set to ' . trim($outputTemplate) . '.');
        } else {
            $this->getIO()->write($iconFailed . 'Git "commit.template" not set or file does not exist, please run "composer tdk:git template"');
            $code = Command::FAILURE;
        }

        // Test vendor folder
        if ($filesystem->exists('vendor')) {
            $this->getIO()->write($iconSuccess . 'Vendor folder exists.');
        } else {
            $this->getIO()->write($iconFailed . 'Vendor folder is missing, please run "composer install"');
            $code = Command::FAILURE;
        }

        return $code;
    }
}
