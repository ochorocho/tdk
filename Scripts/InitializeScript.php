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
    private static string $coreDevFolder = 'typo3-core';

    public static function question(Event $event)
    {
        // Ask a few questions ...
        $questions = [
            [
                'method' => 'enableCommitMessageHook',
                'message' => 'Setup Commit Message Hook? <fg=cyan;options=bold>[y/n]</> ',
                'default' => true
            ],
            [
                'method' => 'enablePreCommitHook',
                'message' => 'Setup Pre Commit Hook? <fg=cyan;options=bold>[y/n]</> ',
                'default' => true
            ],
        ];

        // Only ask for ddev config if ddev command is available
        $windows = strpos(PHP_OS, 'WIN') === 0;
        $test = $windows ? 'where' : 'command -v';

        if(is_executable(trim(shell_exec($test . ' ddev') ?? ''))) {
            $questions[] = [
                'method' => 'createDdevConfig',
                'message' => 'Create a basic ddev config? <fg=cyan;options=bold>[y/n]</> ',
                'default' => false
            ];
        }

        foreach ($questions as $question) {
            $answer = $event->getIO()->askConfirmation($question['message'], $question['default']);

            if($answer) {
                $method = $question['method'];
                static::$method($event);
            }
        }
    }

    public static function enableCommitMessageHook(Event $event)
    {
        $filesystem = new Filesystem();

        try {
            $filesystem->copy(static::$coreDevFolder . '/Build/git-hooks/commit-msg', static::$coreDevFolder . '/.git/hooks/commit-msg');
            if (!is_executable(static::$coreDevFolder . '/.git/hooks/commit-msg')) {
                $filesystem->chmod(static::$coreDevFolder . '/.git/hooks/commit-msg', 0755);
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
            $filesystem->copy(static::$coreDevFolder . '/Build/git-hooks/unix+mac/pre-commit', static::$coreDevFolder . '/.git/hooks/pre-commit');
            if (!is_executable(static::$coreDevFolder . '/.git/hooks/pre-commit')) {
                $filesystem->chmod(static::$coreDevFolder . '/.git/hooks/pre-commit', 0755);
            }
            $event->getIO()->write('<info>Created Pre Commit Hook</info>');
        } catch (\Symfony\Component\Filesystem\Exception\IOException $e) {
            $event->getIO()->writeError('<warning>Exception:enablePreCommitHook:' . $e->getMessage() . '</warning>');
        }
    }

    public static function setGerritPushUrl(Event $event)
    {
        $typo3AccountUsername = $event->getIO()->askAndValidate('What is your TYPO3 Account Username? ', '', 2);
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

    /**
     * @todo: disable all hooks?
     *
     * @param Event $event
     */
    public static function disablePreCommitHook(Event $event)
    {
        $filesystem = new Filesystem();
        $filesystem->remove(static::$coreDevFolder . '/.git/hooks/pre-commit');
    }

    public static function createDdevConfig(Event $event)
    {
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

    public static function cloneRepo(Event $event)
    {
        $filesystem = new Filesystem();

        if(!$filesystem->exists(static::$coreDevFolder)) {
            $process = new ProcessExecutor();
            $composerFilesystem = new ComposerFilesystem();
            $gitRemoteUrl = 'git@github.com:TYPO3/typo3.git';

            $git = new Git($event->getIO(), $event->getComposer()->getConfig(), $process, $composerFilesystem);
            $commandCallable = function ($gitRemoteUrl) {
                return sprintf('git clone %s %s', ProcessExecutor::escape($gitRemoteUrl), ProcessExecutor::escape(static::$coreDevFolder));
            };

            $event->getIO()->write('Cloning TYPO3 repository. This may take a while depending on your internet connection!');
            $git->runCommand($commandCallable, $gitRemoteUrl, static::$coreDevFolder, true);
        } else {
            $event->getIO()->write('Repository exists! Therefore no download required.');
        }
    }

    public static function clearFilesAndFolders(Event $event)
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

        $answer = $event->getIO()->askConfirmation('Really want to delete ' . implode(', ', $filesToDelete) . '? <fg=cyan;options=bold>[y/n]</> ', false);

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
