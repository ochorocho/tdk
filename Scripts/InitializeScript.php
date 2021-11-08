<?php

declare(strict_types=1);
namespace Ochorocho\Tdk\Scripts;

use Composer\Util\Git;
use Composer\Script\Event;
use Composer\Util\Filesystem as ComposerFilesystem;
use Composer\Util\ProcessExecutor;
use Symfony\Component\Filesystem\Filesystem;

class InitializeScript
{
    public static function question(Event $event)
    {
        // Ask a few questions ...
        $questions = [
            'enableCommitMessageHook' => 'Setup Commit Message Hook? <fg=cyan;options=bold>[y/n]</> ',
            'enablePreCommitHook' => 'Setup Pre Commit Hook? <fg=cyan;options=bold>[y/n]</> '
        ];

        foreach ($questions as $method => $question) {
            $answer = $event->getIO()->askConfirmation($question, true);

            if($answer) {
                static::$method($event);
            }
        }
    }

    public static function enableCommitMessageHook(Event $event)
    {
        $filesystem = new Filesystem();

        try {
            $filesystem->copy('typo3-core/Build/git-hooks/commit-msg', 'typo3-core/.git/hooks/commit-msg');
            if (!is_executable('typo3-core/.git/hooks/commit-msg')) {
                $filesystem->chmod('typo3-core/.git/hooks/commit-msg', 0755);
            }
            $event->getIO()->write('<info>Created Commit Message Hook</info>');
        } catch (\Symfony\Component\Filesystem\Exception\IOException $e) {
            $event->getIO()->writeError('<warning>Exception:enableCommitMessageHook:' . $e->getMessage() . '</warning>');
        }
    }

    public static function enablePreCommitHook(Event $event)
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            return;
        }
        $filesystem = new Filesystem();
        try {
            $filesystem->copy('typo3-core/Build/git-hooks/unix+mac/pre-commit', 'typo3-core/.git/hooks/pre-commit');
            if (!is_executable('typo3-core/.git/hooks/pre-commit')) {
                $filesystem->chmod('typo3-core/.git/hooks/pre-commit', 0755);
            }
            $event->getIO()->write('<info>Created Pre Commit Hook</info>');
        } catch (\Symfony\Component\Filesystem\Exception\IOException $e) {
            $event->getIO()->writeError('<warning>Exception:enablePreCommitHook:' . $e->getMessage() . '</warning>');
        }
    }

    public static function disablePreCommitHook(Event $event)
    {
        $filesystem = new Filesystem();
        $filesystem->remove('typo3-core/.git/hooks/pre-commit');
    }

    public static function cloneRepo(Event $event)
    {
        $filesystem = new Filesystem();

        if(!$filesystem->exists('typo3-core')) {
            $process = new ProcessExecutor();
            $composerFilesystem = new ComposerFilesystem();

            $url = 'git@github.com:TYPO3/typo3.git';
            $dir = 'typo3-core';

            $git = new Git($event->getIO(), $event->getComposer()->getConfig(), $process, $composerFilesystem);
            $commandCallable = function ($url) use ($dir) {
                return sprintf('git clone %s %s', ProcessExecutor::escape($url), ProcessExecutor::escape($dir));
            };

            $git->runCommand($commandCallable, $url, $dir, true);
        }
    }

    public static function clearFilesAndFolders(Event $event)
    {
        $answer = $event->getIO()->askConfirmation('Really want to cleanup/delete files and Folders? <fg=cyan;options=bold>[y/n]</> ', false);

        if($answer) {
            $filesystem = new Filesystem();
            $filesystem->remove([
                'composer.lock',
                'public/index.php',
                'public/typo3',
                'typo3-core',
                'vendor',
                'var',
            ]);
        }
    }
}
