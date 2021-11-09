<?php

declare(strict_types=1);

namespace Ochorocho\Tdk\Scripts;

use Composer\Script\Event;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

class InitializeScript
{
    private static string $coreDevFolder = 'typo3-core';

    public static function enableHooks(Event $event)
    {
        $questions = [
            [
                'method' => 'enableCommitMessageHook',
                'message' => 'Setup Commit Message Hook? [<fg=cyan;options=bold>y</>/n] ',
                'default' => true
            ],
            [
                'method' => 'enablePreCommitHook',
                'message' => 'Setup Pre Commit Hook? [<fg=cyan;options=bold>y</>/n] ',
                'default' => true
            ],
        ];

        foreach ($questions as $question) {
            $answer = $event->getIO()->askConfirmation($question['message'], $question['default']);

            if($answer) {
                $method = $question['method'];
                static::$method($event);
            }
        }
    }

    public static function removeHooks(Event $event)
    {
        $filesystem = new Filesystem();
        $filesystem->remove([
            static::$coreDevFolder . '/.git/hooks/pre-commit',
            static::$coreDevFolder . '/.git/hooks/commit-msg',
        ]);
    }

    private static function enableCommitMessageHook(Event $event)
    {
        $filesystem = new Filesystem();

        try {
            $targetCommitMsg = static::$coreDevFolder . '/.git/hooks/commit-msg';
            $filesystem->copy(static::$coreDevFolder . '/Build/git-hooks/commit-msg', $targetCommitMsg);

            if (!is_executable($targetCommitMsg)) {
                $filesystem->chmod($targetCommitMsg, 0755);
            }

            $event->getIO()->write('<info>Created Commit Message Hook</info>');
        } catch (IOException $e) {
            $event->getIO()->writeError('<warning>Exception:enableCommitMessageHook:' . $e->getMessage() . '</warning>');
        }
    }

    private static function enablePreCommitHook(Event $event)
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            return;
        }
        $filesystem = new Filesystem();
        try {
            $targetPreCommit = static::$coreDevFolder . '/.git/hooks/pre-commit';
            $filesystem->copy(static::$coreDevFolder . '/Build/git-hooks/unix+mac/pre-commit', $targetPreCommit);

            if (!is_executable($targetPreCommit)) {
                $filesystem->chmod($targetPreCommit, 0755);
            }

            $event->getIO()->write('<info>Created Pre Commit Hook</info>');
        } catch (IOException $e) {
            $event->getIO()->writeError('<warning>Exception:enablePreCommitHook:' . $e->getMessage() . '</warning>');
        }
    }

    public static function createDdevConfig(Event $event)
    {
        // Only ask for ddev config if ddev command is available
        $windows = strpos(PHP_OS, 'WIN') === 0;
        $test = $windows ? 'where' : 'command -v';

        if(is_executable(trim(shell_exec($test . ' ddev') ?? ''))) {
            $answer = $event->getIO()->askConfirmation('Create a basic ddev config? [y/<fg=cyan;options=bold>n</>] ', false);

            if($answer) {
                $ddevProjectName = $event->getIO()->askAndValidate('What should be the ddev projects name? ', '', 2);

                if(!empty($ddevProjectName)) {
                    $configYaml = <<<EOF
name: $ddevProjectName
type: typo3
docroot: public
php_version: "8.0"
webserver_type: nginx-fpm
router_http_port: "80"
router_https_port: "443"
xdebug_enabled: false
additional_hostnames: []
additional_fqdns: []
mariadb_version: "10.3"
mysql_version: ""
nfs_mount_enabled: false
mutagen_enabled: false
use_dns_when_possible: true
composer_version: ""
web_environment: []
EOF;

                    $filesystem = new Filesystem();
                    $filesystem->dumpFile('.ddev/config.yaml', $configYaml);
                }
            }

        }
    }

    public static function removeFilesAndFolders(Event $event)
    {
        $filesToDelete = [
            'composer.lock',
            'public/index.php',
            'public/typo3',
            'typo3-core',
            'vendor',
            'var',
            '.ddev',
        ];

        $answer = $event->getIO()->askConfirmation('Really want to delete ' . implode(', ', $filesToDelete) . '? [y/<fg=cyan;options=bold>n</>] ', false);

        if($answer) {
            $filesystem = new Filesystem();
            $filesystem->remove($filesToDelete);
        }
    }

    public static function showSummary(Event $event)
    {
        $summary = <<<EOF

💡For more Details read the docs:
* Setting up Gerrit (ssh):
  https://docs.typo3.org/m/typo3/guide-contributionworkflow/master/en-us/Account/GerritAccount.html
* Git Setup:
  https://docs.typo3.org/m/typo3/guide-contributionworkflow/master/en-us/Setup/Git/Index.html
* Setup your IDE:
  https://docs.typo3.org/m/typo3/guide-contributionworkflow/master/en-us/Setup/SetupIde.html
* runTests.sh docs still apply, but don't forget to cd into 'typo3-core':
  https://docs.typo3.org/m/typo3/guide-contributionworkflow/master/en-us/Testing/Index.html

<fg=yellow;options=bold>To be able to push to Gerrit, you need to add your public key, see https://review.typo3.org/settings/#SSHKeys</>
<info>🎉 Happy days ... TYPO3 Composer CoreDev Setup done! </info>
EOF;

        $event->getIO()->write($summary);
    }
}
