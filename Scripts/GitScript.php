<?php

declare(strict_types=1);

namespace Ochorocho\Tdk\Scripts;

use Composer\Script\Event;
use Composer\Util\ProcessExecutor;
use Symfony\Component\Filesystem\Filesystem;

class GitScript
{
    private static string $coreDevFolder = 'typo3-core';

    public static function setGerritPushUrl(Event $event)
    {
        $arguments = self::getArguments($event->getArguments());

        if($arguments['username'] ?? false) {
            $typo3AccountUsername = $arguments['username'];
        } else {
            $typo3AccountUsername = $event->getIO()->askAndValidate('What is your TYPO3/Gerrit Account Username? ', '', 2);
        }

        if(!empty($typo3AccountUsername)) {
            $pushUrl = '"ssh://' . $typo3AccountUsername . '@review.typo3.org:29418/Packages/TYPO3.CMS.git"';
            $process = new ProcessExecutor();
            $command = 'git config remote.origin.pushurl ' . $pushUrl;
            $status = $process->execute($command, $output, self::$coreDevFolder);

            if($status) {
                $event->getIO()->writeError('<error>Could not enable Git Commit Template!</error>');
            } else {
                $event->getIO()->write('<info>Set "remote.origin.pushurl" to ' . $pushUrl . ' </info>');
            }
        }
    }

    public static function setCommitTemplate(Event $event)
    {
        $process = new ProcessExecutor();
        $template = realpath('./.gitmessage.txt');
        $status = $process->execute('git config commit.template ' . $template, $output, self::$coreDevFolder);

        if($status) {
            $event->getIO()->writeError('<error>Could not enable Git Commit Template!</error>');
        } else {
            $event->getIO()->write('<info>Set "commit.template" to ' . $template . ' </info>');
        }
    }

    public static function cloneRepository(Event $event)
    {
        $filesystem = new Filesystem();
        if(!$filesystem->exists(static::$coreDevFolder)) {
            $process = new ProcessExecutor();
            $gitRemoteUrl = 'git@github.com:TYPO3/typo3.git';
            $command = sprintf('git clone %s %s', ProcessExecutor::escape($gitRemoteUrl), ProcessExecutor::escape(static::$coreDevFolder));
            $event->getIO()->write('<info>Cloning TYPO3 repository. This may take a while depending on your internet connection!</info>');
            $status = $process->executeTty($command);

            if($status) {
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

            $key = explode('=', $parsed[1]);
            $items[$key[0]] = $key[1] ?? true;
        }

        return $items;
    }
}
