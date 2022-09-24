<?php

declare(strict_types=1);

namespace Ochorocho\TdkComposer\Command;

use Composer\Command\BaseCommand;
use Composer\Util\ProcessExecutor;
use Ochorocho\TdkComposer\Service\BaseService;
use Ochorocho\TdkComposer\Service\ComposerService;
use Ochorocho\TdkComposer\Service\GitService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

final class DoctorCommand extends BaseCommand
{
    protected OutputInterface $output;
    protected Filesystem $filesystem;
    protected int $code;
    protected ComposerService $composerService;
    protected ProcessExecutor $process;

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->filesystem = new Filesystem();
        $this->code = Command::SUCCESS;
        $this->composerService = new ComposerService();
        $this->process = new ProcessExecutor();

        parent::initialize($input, $output);
    }

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
        $this->testGitRepository();
        $this->testHooks();
        $this->testGitPushUrl();
        $this->testCommitTemplate();
        $this->testCoreExtensionSymlinked();
        $this->testVendor();

        return $this->code;
    }

    /**
     * @return void
     */
    protected function testVendor(): void
    {
        if ($this->filesystem->exists('vendor')) {
            $this->getIO()->write(BaseService::ICON_SUCCESS . 'Vendor folder exists.');
        } else {
            $this->getIO()->write(BaseService::ICON_FAILED . 'Vendor folder is missing, please run "composer install"');
            $this->code = Command::FAILURE;
        }
    }

    protected function testCoreExtensionSymlinked(): void
    {
        // @todo: Test only extensions located in public/typo3/sysext
        $coreExtensionFolders = $this->composerService->getCoreExtensionsFolder();
        $extensionTest = [];
        foreach ($coreExtensionFolders as $folder) {
            $path = 'public/typo3/sysext/' . $folder->getFileName();

            $symlink = $this->filesystem->readlink($path, true);

            if ($symlink === null) {
                $extensionTest['failed'][] = $folder->getFileName();
            } else {
                $extensionTest['success'][] = $folder->getFileName();
            }
        }

        if ($extensionTest['failed'] ?? false) {
            $this->getIO()->write(BaseService::ICON_FAILED . 'Following extensions are not symlinked: ' . implode(
                ', ',
                $extensionTest['failed']
            ));
        }

        if ($extensionTest['success'] ?? false) {
            $this->getIO()->write(BaseService::ICON_SUCCESS . 'Following extensions are symlinked: ' . implode(
                ', ',
                $extensionTest['success']
            ));
        }
    }

    protected function testCommitTemplate(): void
    {
        $commandTemplate = 'git config --get commit.template';
        $this->process->execute($commandTemplate, $outputTemplate, BaseService::CORE_DEV_FOLDER);

        if (!empty($outputTemplate) && $this->filesystem->exists(trim($outputTemplate))) {
            $this->getIO()->write(BaseService::ICON_SUCCESS . 'Git "commit.template" is set to ' . trim($outputTemplate) . '.');
        } else {
            $this->getIO()->write(BaseService::ICON_FAILED . 'Git "commit.template" not set or file does not exist, please run "composer tdk:git template"');
            $this->code = Command::FAILURE;
        }
    }

    protected function testGitPushUrl(): void
    {
        $command = 'git config --get remote.origin.pushurl';
        $this->process->execute($command, $commandOutput, BaseService::CORE_DEV_FOLDER);

        preg_match('/^ssh:\/\/(.*)@review\.typo3\.org/', (string)$commandOutput, $matches);
        if (!empty($matches)) {
            $this->getIO()->write(BaseService::ICON_SUCCESS . 'Git "remote.origin.pushurl" seems correct.');
        } else {
            $this->getIO()->write(BaseService::ICON_FAILED . 'Git "remote.origin.pushurl" not set correctly, please run "composer tdk:git config".');
            $this->code = Command::FAILURE;
        }
    }

    protected function testHooks(): void
    {
        if ($this->filesystem->exists([
            BaseService::CORE_DEV_FOLDER . '/.git/hooks/pre-commit',
            BaseService::CORE_DEV_FOLDER . '/.git/hooks/commit-msg',
        ])) {
            $this->getIO()->write(BaseService::ICON_SUCCESS . 'All hooks are in place.');
        } else {
            $this->getIO()->write(BaseService::ICON_FAILED . 'Hooks are missing please run "composer tdk:hooks create".');
            $this->code = Command::FAILURE;
        }
    }

    protected function testGitRepository(): void
    {
        if ($this->filesystem->exists(BaseService::CORE_DEV_FOLDER . '/.git')) {
            $gitService = new GitService();
            $commit = $gitService->latestCommit();
            $this->getIO()->write(BaseService::ICON_SUCCESS . 'Repository exists on commit ' . $commit);
        } else {
            $this->getIO()->write(BaseService::ICON_FAILED . 'Repository not in place, please run "composer tdk:git clone"');
            $this->code = Command::FAILURE;
        }
    }
}
