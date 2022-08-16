<?php

declare(strict_types=1);

namespace Ochorocho\Tdk\Scripts;

use Composer\Script\Event;
use Composer\Util\ProcessExecutor;
use Symfony\Component\Filesystem\Filesystem;

class GitScript extends BaseScript
{
    public static function setGitConfig(Event $event)
    {
        $arguments = self::getArguments($event->getArguments());
        $validator = self::validateUsername($event);

        $username = $arguments['username'] ?? getenv('TDK_USERNAME') ?? false;
        if ($username === 'none') {
            return 0;
        }

        if($username) {
            $userData = $validator($username);
        } else {
            $userData = $event->getIO()->askAndValidate('What is your TYPO3/Gerrit Account Username? ', $validator, 2);
        }

        $pushUrl = 'ssh://' . $userData['username'] . '@review.typo3.org:29418/Packages/TYPO3.CMS.git';
        self::setGitConfigValue($event, 'remote.origin.pushurl', $pushUrl);
        self::setGitConfigValue($event, 'user.name', $userData['display_name'] ?? $userData['name'] ?? $userData['username']);
        self::setGitConfigValue($event, 'user.email', $userData['email']);

        return 0;
    }

    public static function setCommitTemplate(Event $event)
    {
        $arguments = self::getArguments($event->getArguments());
        $validator = self::validateFilePath();

        if ($arguments['file'] ?? false) {
            $file = $validator($arguments['file']);
        } else {
            $file = $event->getIO()->askAndValidate('Set TYPO3 commit message template [default: .gitmessage.txt]? ', $validator, 3, '.gitmessage.txt');
        }

        $process = new ProcessExecutor();
        $template = realpath($file);
        $status = $process->execute('git config commit.template ' . $template, $output, self::$coreDevFolder);

        if ($status) {
            $event->getIO()->writeError('<error>Could not enable Git Commit Template!</error>');
        } else {
            $event->getIO()->write('<info>Set "commit.template" to ' . $template . ' </info>');
        }
    }

    public static function applyPatch(Event $event)
    {
        $ref = self::getArguments($event->getArguments())['ref'] ?? getenv('TDK_PATCH_REF') ?? false;
        if(empty($ref)) {
            $event->getIO()->write('<warning>No patch ref given</warning>');
            return 1;
        }

        $filesystem = new Filesystem();
        if ($filesystem->exists(self::$coreDevFolder)) {
            $process = new ProcessExecutor();
            $command = 'git fetch https://review.typo3.org/Packages/TYPO3.CMS ' . $ref . ' && git cherry-pick FETCH_HEAD';
            $event->getIO()->write('<info>Apply patch ' . $ref . '</info>');
            $status = $process->executeTty($command, self::$coreDevFolder);

            if ($status) {
                $event->getIO()->write('<warning>Could not apply patch ' . $ref . ' </warning>');
            }
        } else {
            $event->getIO()->write('Could not apply patch, repository does not exist. Please run "composer tdk:clone"');
        }

        return 0;
    }

    public static function cloneRepository(Event $event): void
    {
        $filesystem = new Filesystem();
        if (!$filesystem->exists(self::$coreDevFolder)) {
            $process = new ProcessExecutor();
            $gitRemoteUrl = 'https://github.com/TYPO3/typo3.git';
            $command = sprintf('git clone %s %s', ProcessExecutor::escape($gitRemoteUrl), ProcessExecutor::escape(self::$coreDevFolder));
            $event->getIO()->write('<info>Cloning TYPO3 repository. This may take a while depending on your internet connection!</info>');
            $status = $process->executeTty($command);

            if ($status) {
                $event->getIO()->write('<warning>Could not download git repository ' . $gitRemoteUrl . ' </warning>');
            }
        } else {
            $event->getIO()->write('Repository exists! Therefore no download required.');
        }
    }

    public static function checkoutBranch(Event $event)
    {
        $branch = self::getArguments($event->getArguments())['branch'] ?? getenv('TDK_BRANCH') ?? false;
        if(empty($branch)) {
            $event->getIO()->write('<warning>No branch name given</warning>');
            return 1;
        }

        $process = new ProcessExecutor();
        $command = sprintf('git checkout %s', ProcessExecutor::escape($branch));
        $event->getIO()->write('<info>Checking out branch "' . $branch . '"!</info>');
        $status = $process->executeTty($command, self::$coreDevFolder);
        if ($status) {
            $event->getIO()->write('<warning>Could not checkout branch ' . $branch . ' </warning>');
        }

        return 0;
    }

    public static function getArguments($array): array
    {
        $items = [];
        foreach ($array as $argument) {
            preg_match('/^--(.*)/', $argument, $parsed);

            $key = explode('=', $parsed[1] ?? '');
            $items[$key[0]] = $key[1] ?? true;
        }

        return $items;
    }

    private static function setGitConfigValue(Event $event, string $config, string $value): void
    {
        $process = new ProcessExecutor();
        $command = 'git config ' . $config . ' "' . $value . '"';
        $status = $process->execute($command, $output, self::$coreDevFolder);
        if ($status > 0) {
            $event->getIO()->writeError('<error>Could not set "' . $config . '" to "' . $value . '"</error>');
        } else {
            $event->getIO()->write('<info>Set "' . $config . '" to "' . $value . '"</info>');
        }
    }
}
