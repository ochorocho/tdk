<?php

declare(strict_types=1);

namespace Ochorocho\Tdk\Scripts;

use Composer\Script\Event;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

class HookScript extends BaseScript
{
    public static function enable(Event $event)
    {
        $questions = [
            [
                'method' => 'enableCommitMessage',
                'message' => 'Setup Commit Message Hook? [<fg=cyan;options=bold>y</>/n] ',
                'default' => true
            ],
            [
                'method' => 'enablePreCommit',
                'message' => 'Setup Pre Commit Hook? [<fg=cyan;options=bold>y</>/n] ',
                'default' => true
            ],
        ];

        $force = (bool)(GitScript::getArguments($event->getArguments())['force'] ?? getenv('TDK_HOOK_FORCE_CREATE') ?? false);
        foreach ($questions as $question) {
            if ($force) {
                $answer = true;
            } else {
                $answer = $event->getIO()->askConfirmation($question['message'], $question['default']);
            }

            if ($answer) {
                $method = $question['method'];
                static::$method($event);
            }
        }
    }

    public static function remove(Event $event)
    {
        $filesystem = new Filesystem();
        $filesystem->remove([
            self::$coreDevFolder . '/.git/hooks/pre-commit',
            self::$coreDevFolder . '/.git/hooks/commit-msg',
        ]);
    }

    private static function enableCommitMessage(Event $event)
    {
        $filesystem = new Filesystem();

        try {
            $targetCommitMsg = self::$coreDevFolder . '/.git/hooks/commit-msg';
            $filesystem->copy(self::$coreDevFolder . '/Build/git-hooks/commit-msg', $targetCommitMsg);

            if (!is_executable($targetCommitMsg)) {
                $filesystem->chmod($targetCommitMsg, 0755);
            }

            $event->getIO()->write('<info>Created Commit Message Hook</info>');
        } catch (IOException $e) {
            $event->getIO()->writeError('<warning>Exception:enableCommitMessageHook:' . $e->getMessage() . '</warning>');
        }
    }

    private static function enablePreCommit(Event $event)
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            return;
        }
        $filesystem = new Filesystem();
        try {
            $targetPreCommit = self::$coreDevFolder . '/.git/hooks/pre-commit';
            $filesystem->copy(self::$coreDevFolder . '/Build/git-hooks/unix+mac/pre-commit', $targetPreCommit);

            if (!is_executable($targetPreCommit)) {
                $filesystem->chmod($targetPreCommit, 0755);
            }

            $event->getIO()->write('<info>Created Pre Commit Hook</info>');
        } catch (IOException $e) {
            $event->getIO()->writeError('<warning>Exception:enablePreCommitHook:' . $e->getMessage() . '</warning>');
        }
    }
}
