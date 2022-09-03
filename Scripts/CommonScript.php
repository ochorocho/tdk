<?php

declare(strict_types=1);

namespace Ochorocho\Tdk\Scripts;

use Composer\Script\Event;
use Composer\Util\ProcessExecutor;
use Symfony\Component\Filesystem\Filesystem;

class CommonScript extends BaseScript
{
    public static function createDdevConfig(Event $event)
    {
        // Only ask for ddev config if ddev command is available
        $windows = strpos(PHP_OS, 'WIN') === 0;
        $test = $windows ? 'where' : 'command -v';

        if (is_executable(trim(shell_exec($test . ' ddev') ?? ''))) {
            $ddevProjectName = self::getArguments($event->getArguments())['project-name'] ?? getenv('TDK_CREATE_DDEV_PROJECT_NAME') ?? false;
            if (!$ddevProjectName) {
                $skip = isset(self::getArguments($event->getArguments())['no']) ?? false;
                if ($skip) {
                    $createConfig = false;
                } else {
                    $createConfig = $event->getIO()->askConfirmation('Create a basic ddev config [<fg=cyan;options=bold>y</>/n] ?');
                }

                if (!$createConfig) {
                    $event->getIO()->write('<warning>Aborted! No ddev config created.</warning>');
                    return 0;
                }
            }

            $validator = ValidatorScript::projectName();

            if (!$ddevProjectName) {
                $defaultProjectName = basename(getcwd());
                $ddevProjectName = $event->getIO()->askAndValidate('Choose a ddev project name [default: ' . $defaultProjectName . '] :', $validator, 2, $defaultProjectName);
            } else {
                try {
                    $ddevProjectName = $validator($ddevProjectName);
                } catch (\UnexpectedValueException $e) {
                    $event->getIO()->write('<error>' . $e->getMessage() . '</error>');
                    return 1;
                }
            }

            $phpVersion = self::getPhpVersion(self::$coreDevFolder . '/composer.json');
            $ddevCommand = 'ddev config --docroot public --project-name ' . $ddevProjectName . ' --web-environment-add TYPO3_CONTEXT=Development --project-type typo3 --php-version ' . $phpVersion . ' --create-docroot 1> /dev/null';
            exec($ddevCommand, $output, $statusCode);

            return $statusCode;
        }

        return 0;
    }

    public static function removeFilesAndFolders(Event $event): void
    {
        $filesToDelete = [
            'composer.lock',
            'public/index.php',
            'public/typo3',
            self::$coreDevFolder,
            'var',
        ];

        $force = self::getArguments($event->getArguments())['force'] ?? false;

        if ($force) {
            $answer = true;
        } else {
            $answer = $event->getIO()->askConfirmation('Really want to delete ' . implode(', ', $filesToDelete) . '? [y/<fg=cyan;options=bold>n</>] ', false);
        }

        if ($answer) {
            $filesystem = new Filesystem();
            $filesystem->remove($filesToDelete);
            $event->getIO()->write('<info>Done deleting files.</info>');
        }
    }

    public static function getPhpVersion(string $composerFile): string
    {
        if ($fileContent = file_get_contents($composerFile)) {
            $json = json_decode($fileContent, true, 512, JSON_THROW_ON_ERROR);
            preg_match_all('/[0-9].[0-9]/', $json['require']['php'], $versions);
            return $versions[0][0];
        }

        return '8.1';
    }

    public static function doctor(Event $event): void
    {
        $filesystem = new Filesystem();

        // Test for existing repository
        if ($filesystem->exists(self::$coreDevFolder . '/.git')) {
            $event->getIO()->write('<fg=green;options=bold>✔</> Repository exists.');
        } else {
            $event->getIO()->write('<fg=red;options=bold>✘</> TYPO3 Repository not in place, please run "composer tdk:clone"');
        }

        // Test if hooks are set up
        if ($filesystem->exists([
            self::$coreDevFolder . '/.git/hooks/pre-commit',
            self::$coreDevFolder . '/.git/hooks/commit-msg',
        ])) {
            $event->getIO()->write('<fg=green;options=bold>✔</> All hooks are in place.');
        } else {
            $event->getIO()->write('<fg=red;options=bold>✘</> Hooks are missing please run "composer tdk:enable-hooks".');
        }

        // Test git push url
        $process = new ProcessExecutor();
        $command = 'git config --get remote.origin.pushurl';
        $process->execute($command, $output, self::$coreDevFolder);

        preg_match('/^ssh:\/\/(.*)@review\.typo3\.org/', $output, $matches);
        if (!empty($matches)) {
            $event->getIO()->write('<fg=green;options=bold>✔</> Git "remote.origin.pushurl" seems correct.');
        } else {
            $event->getIO()->write('<fg=red;options=bold>✘</> Git "remote.origin.pushurl" not set correctly, please run "composer tdk:set-git-config".');
        }

        // Test commit template
        $commandTemplate = 'git config --get commit.template';
        $process->execute($commandTemplate, $outputTemplate, self::$coreDevFolder);

        if (!empty($outputTemplate) && $filesystem->exists(trim($outputTemplate))) {
            $event->getIO()->write('<fg=green;options=bold>✔</> Git "commit.template" is set to ' . trim($outputTemplate) . '.');
        } else {
            $event->getIO()->write('<fg=red;options=bold>✘</> Git "commit.template" not set or file does not exist, please run "composer tdk:set-commit-template"');
        }

        // Test vendor folder
        if ($filesystem->exists('vendor')) {
            $event->getIO()->write('<fg=green;options=bold>✔</> Vendor folder exists.');
        } else {
            $event->getIO()->write('<fg=red;options=bold>✘</> Vendor folder is missing, please run "composer install"');
        }
    }
}
