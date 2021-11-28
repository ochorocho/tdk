<?php

declare(strict_types=1);

namespace Ochorocho\Tdk\Scripts;

use Composer\Downloader\TransportException;
use Composer\Script\Event;
use Composer\Util\HttpDownloader;
use Composer\Util\ProcessExecutor;
use Symfony\Component\Filesystem\Filesystem;

class GitScript
{
    private static string $coreDevFolder = 'typo3-core';

    public static function setGitConfig(Event $event)
    {
        $arguments = self::getArguments($event->getArguments());

        // Validate the username
        $validator = function ($value) use ($event) {
            try {
                $userData = self::getGerritUserData($event, $value);
            } catch (TransportException $exception) {
                throw new \UnexpectedValueException('Username "' . $value . '" not found in TYPO3 Gerrit: ' . PHP_EOL . $exception->getMessage());
            }

            return $userData;
        };

        $username = $arguments['username'] ?? getenv('TDK_USERNAME') ?? false;
        if($username === 'none') {
            return 0;
        } elseif ($username) {
            $userData = $validator($username);
        } else {
            $userData = $event->getIO()->askAndValidate('What is your TYPO3/Gerrit Account Username? ', $validator, 2);
        }

        $pushUrl = 'ssh://' . $userData['username'] . '@review.typo3.org:29418/Packages/TYPO3.CMS.git';
        self::setGitConfigValue($event, 'remote.origin.pushurl', $pushUrl);
        self::setGitConfigValue($event, 'user.name', $userData['display_name'] ?? $userData['name'] ?? $userData['username']);
        self::setGitConfigValue($event, 'user.email', $userData['email']);
    }

    public static function setCommitTemplate(Event $event)
    {
        $arguments = self::getArguments($event->getArguments());

        // Validate file
        $validator = function ($value) {
            $path = realpath($value);
            if (!is_file($path)) {
                throw new \UnexpectedValueException('Invalid file path "' . $path . '"');
            }

            return $value;
        };

        if ($arguments['file'] ?? false) {
            $file = $validator($arguments['file']);
        } else {
            $file = $event->getIO()->askAndValidate('Set TYPO3 commit message template [.gitmessage.txt]? ', $validator, 2, './.gitmessage.txt');
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
    }

    public static function cloneRepository(Event $event)
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

    private static function getGerritUserData(Event $event, string $username): array
    {
        $request = new HttpDownloader($event->getIO(), $event->getComposer()->getConfig());
        $json = $request->get('https://review.typo3.org/accounts/' . urlencode($username) . '/?pp=0');

        // Gerrit does not return valid JSON using their JSON API
        // therefore we need to chop off the first line
        // Sounds weird? See why https://gerrit-review.googlesource.com/Documentation/rest-api.html#output
        $validJson = str_replace(')]}\'', '', $json->getBody());

        return json_decode($validJson, true);
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
