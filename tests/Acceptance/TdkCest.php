<?php

declare(strict_types=1);

namespace Acceptance;

use AcceptanceTester as AcceptanceTester;

class TdkCest
{
    private static string $coreDevFolder = 'typo3-core/';
    private static string $testFolder = __DIR__ . '/../../test-acceptance-tdk/';

    public function _before(AcceptanceTester $I)
    {
        chdir(self::$testFolder);
    }

    public function clone(AcceptanceTester $I): void
    {
        // Use "composer install" because it triggers tdk:clone
        $I->runShellCommand('composer install');
        $I->seeResultCodeIs(0);
        $I->seeInShellOutput('Cloning TYPO3 repository. This may take a while depending on your internet connection!');
        $I->seeInShellOutput('Cloning into');

        $I->runShellCommand('composer tdk:clone');
        $I->seeResultCodeIs(0);
        $I->seeInShellOutput('Repository exists! Therefore no download required.');
    }

    public function help(AcceptanceTester $I): void
    {
        $I->runShellCommand('composer tdk:help');

        $I->seeResultCodeIs(0);
        $I->seeInShellOutput('For more Details read the docs:', 'To be able to push to Gerrit, you need to add your public key');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function gitConfig(AcceptanceTester $I): void
    {
        $I->runShellCommand('composer tdk:set-git-config -- --username=ochorocho');
        $I->amGoingTo('See expected response text of command');
        $I->seeInShellOutput('Set "remote.origin.pushurl" to "ssh://ochorocho@review.typo3.org:29418/Packages/TYPO3.CMS.git"');
        $I->seeInShellOutput('Set "user.email" to "rothjochen@gmail.com"');
        $I->seeInShellOutput('Set "user.name" to "Jochen Roth"');

        $I->amGoingTo('See newly set "remote.origin.pushurl"');
        $I->runShellCommand('git -C ' . self::$coreDevFolder . ' config --get remote.origin.pushurl');
        $I->seeInShellOutput('ssh://ochorocho@review.typo3.org');

        $I->amGoingTo('See newly set "user.name"');
        $I->runShellCommand('git -C ' . self::$coreDevFolder . ' config --get user.name');
        $I->seeInShellOutput('Jochen Roth');

        $I->amGoingTo('See newly set "user.email"');
        $I->runShellCommand('git -C ' . self::$coreDevFolder . ' config --get user.email');
        $I->seeInShellOutput('rothjochen@gmail.com');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function commitTemplate(AcceptanceTester $I): void
    {
        $I->runShellCommand('composer tdk:set-commit-template -- --file=./.gitmessage.txt');
        $I->seeInShellOutput('Set "commit.template" to ');

        $I->runShellCommand('git -C ' . self::$coreDevFolder . ' config --get commit.template');
        $I->seeInShellOutput('.gitmessage.txt');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function enableHooks(AcceptanceTester $I): void
    {
        $hooksFolder = self::$testFolder . self::$coreDevFolder . '.git/hooks/';

        $I->amGoingTo('Enable the hooks');
        $I->runShellCommand('composer tdk:enable-hooks -- --force');

        $I->seeResultCodeIs(0);
        $I->seeFileFound('commit-msg', $hooksFolder);
        $I->seeFileFound('pre-commit', $hooksFolder);
    }

    /**
     * @todo: Find a more generic way to test the tdk:apply-patch command
     *
     * @param AcceptanceTester $I
     */
    public function applyPatch(AcceptanceTester $I): void
    {
        // @todo: Create a dedicated patch to test against, currently this breaks as soon as the patch gets merged
        $I->runShellCommand('composer tdk:apply-patch -- --ref=refs/changes/60/69360/6');
        $I->seeInShellOutput('Apply patch refs/changes/60/69360/6');

        $I->runShellCommand('git -C ' . self::$coreDevFolder . ' log -1 --oneline');
        $I->seeInShellOutput('Add returnUrl for Open Documents/Recently Used Documents');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function ddevConfig(AcceptanceTester $I): void
    {
        $I->amGoingTo('use a invalid project name');
        $I->runShellCommand('composer tdk:ddev-config -- --project-name="typo3 invalid"');
        $I->seeInShellOutput('Invalid ddev project name');
        $I->dontSeeFileFound('.ddev', 'test-acceptance-tdk/');

        $I->amGoingTo('abort configuration');
        $I->runShellCommand('composer tdk:ddev-config -- --no');
        $I->seeInShellOutput('Aborted! No ddev config created');
        $I->dontSeeFileFound('.ddev', 'test-acceptance-tdk/');

        $I->amGoingTo('create a ddev config');
        $I->runShellCommand('composer tdk:ddev-config -- --project-name="typo3-dev-tdk"');
        $I->seeFileFound('config.yaml', 'test-acceptance-tdk/.ddev/');
        $I->seeResultCodeIs(0);
    }

    /**
     * @param AcceptanceTester $I
     */
    public function checkoutBranch(AcceptanceTester $I): void
    {
        $I->runShellCommand('composer tdk:checkout -- --branch=main');
        $I->seeInShellOutput('Checking out branch "main"!');

        $I->runShellCommand('git -C ' . self::$coreDevFolder . ' branch --show-current');
        $I->seeInShellOutput('main');
    }

    /**
     * @param AcceptanceTester $I
     */
    public function doctor(AcceptanceTester $I): void
    {
        $I->runShellCommand('composer tdk:doctor');
        $expectedLines = [
            'Repository exists',
            'All hooks are in place',
            'Git "remote.origin.pushurl" seems correct',
            'Git "commit.template" is set to',
            'Vendor folder exists.',
        ];
        $output = $I->grabShellOutput();

        foreach ($expectedLines as $line) {
            $I->assertStringContainsString($line, $output);
        }

        $I->seeResultCodeIs(0);
    }

    /**
     * @param AcceptanceTester $I
     */
    public function removeHooks(AcceptanceTester $I)
    {
        $hooksFolder = self::$testFolder . self::$coreDevFolder . '.git/hooks/';

        $I->amGoingTo('Delete the hooks');
        $I->runShellCommand('composer tdk:remove-hooks');

        $I->seeResultCodeIs(0);
        $I->dontSeeFileFound('commit-msg', $hooksFolder);
        $I->dontSeeFileFound('pre-commit', $hooksFolder);
    }

    /**
     * @param AcceptanceTester $I
     */
    public function clear(AcceptanceTester $I): void
    {
        $I->runShellCommand('composer tdk:clear -- --force');
        $I->seeResultCodeIs(0);

        // Foreach is needed here, as we don't want to
        // re-run the command multiple times
        foreach ($this->clearDataProvider() as $file) {
            $I->dontSeeFileFound(self::$testFolder . $file);
        }
    }

    protected function clearDataProvider(): array
    {
        return [
            'composer.lock',
            'public/index.php',
            'public/typo3',
            'typo3-core',
            'var',
        ];
    }
}
